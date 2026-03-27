<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\RoleType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Collection;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->state(fn(User $record) => $record->getFilamentAvatarUrl())
                    ->disk('s3'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => RoleType::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn(string $state): string => RoleType::tryFrom($state)?->getColor() ?? 'gray')
                    ->separator(',')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label("Email Verification")
                    ->sortable()
                    ->state(fn($record) => $record->email_verified_at ? 'Verified' : 'Not Verified')
                    ->badge()
                    ->color(fn($state) => $state === 'Verified' ? 'success' : 'gray')
                    ->tooltip(fn($record) => $record->email_verified_at?->format('d M Y H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('breezySession.is_confirmed') // Mengakses kolom di tabel breezy_sessions
                    ->label('2FA Status')
                    ->state(fn(User $record) => $record->hasConfirmedTwoFactor() ? 'Enabled' : 'Disabled')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Enabled' ? 'success' : 'gray')
                    ->tooltip(fn(User $record) => $record->breezySession?->created_at?->format('d M Y H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => RoleType::tryFrom($record->name)?->getLabel() ?? $record->name)
                    ->multiple()
                    ->preload(),

                TernaryFilter::make('email_verified_at')
                    ->label('Email Verification')
                    ->nullable()
                    ->placeholder('All Users')
                    ->trueLabel('Verified Only')
                    ->falseLabel('Not Verified Only')
                    ->native(false),

                TernaryFilter::make('two_factor_status')
                    ->label('2FA Status')
                    ->placeholder('All Users')
                    ->trueLabel('2FA Enabled')
                    ->falseLabel('2FA Disabled')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('breezySession', fn($q) => $q->whereNotNull('confirmed_at')),
                        false: fn(Builder $query) => $query->whereDoesntHave('breezySession')->orWhereHas('breezySession', fn($q) => $q->whereNull('confirmed_at')),
                    )
                    ->native(false),

                // TernaryFilter::make('is_active'),
                TrashedFilter::make()
                    ->native(false),
            ])
            ->recordActions([
                Impersonate::make()
                    ->label("")
                    ->color('gray')
                    ->tooltip('Impersonate')
                    ->visible(
                        fn(User $record): bool =>
                        Auth::user()->canImpersonate() &&
                            $record->canBeImpersonated() && Auth::id() !== $record->id
                    )
                    ->redirectTo(function (User $record): string {
                        if ($record->isAdmin() || $record->isSuperAdmin()) {
                            return route('filament.admin.pages.dashboard');
                        }

                        return route('home');
                    }),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->color("gray"),

                    ActionGroup::make([
                        Action::make('toggle_active')
                            ->icon(fn(User $record): Heroicon => $record->is_active ? Heroicon::XMark : Heroicon::Check)
                            ->label(fn(User $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                            // ->color(fn(User $record): string => $record->is_active ? 'danger' : 'success')
                            ->color("gray")
                            ->visible(fn(User $record): bool => Auth::id() !== $record->id && Auth::user()->can('toggle_active_user'))
                            ->requiresConfirmation()
                            ->action(function (User $record) {
                                $record->update(['is_active' => ! $record->is_active]);

                                Notification::make()
                                    ->title($record->is_active ? 'User activated' : 'User deactivated')
                                    ->success()
                                    ->send();
                            }),
                        Action::make('change_role')
                            ->label('Change Role')
                            ->icon('heroicon-o-shield-check')
                            // ->color('info')
                            ->color("gray")
                            ->visible(
                                fn(User $record): bool =>
                                Auth::user()->can('change_role_user') && Auth::id() !== $record->id
                            )
                            ->fillForm(fn(User $record): array => [
                                'role' => $record->roles->first()?->name,
                            ])
                            ->schema([
                                Select::make('role')
                                    ->label('Select Role')
                                    ->options(RoleType::class)
                                    ->required()
                                    ->native(false),
                            ])
                            ->action(function (User $record, array $data): void {
                                $record->syncRoles([$data['role']]);

                                Notification::make()
                                    ->title('Role updated successfully')
                                    ->success()
                                    ->send();
                            }),
                    ])->dropdown(false),

                    ActionGroup::make([
                        RestoreAction::make(),
                        DeleteAction::make()
                            ->visible(
                                fn(User $record): bool =>
                                Auth::user()->can('delete_user') && Auth::id() !== $record->id
                            ),
                    ])->dropdown(false),
                ])->color('gray'),

            ])
            ->checkIfRecordIsSelectableUsing(
                fn(User $record): bool =>
                $record->id !== Auth::id()
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('toggle_active')
                        ->icon(Heroicon::Power)
                        ->color('warning')
                        ->schema([
                            ToggleButtons::make('is_active')
                                ->options([
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ])
                                ->inline()
                                ->required(),
                        ])
                        ->visible(
                            fn(): bool =>
                            Auth::user()->can('toggle_active_user')
                        )
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $records
                                ->filter(fn(User $record) => $record->id !== Auth::id())
                                ->each(fn(User $record) => $record->update(['is_active' => (bool) $data['is_active']]));

                            Notification::make()
                                ->title('Selected users updated')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
