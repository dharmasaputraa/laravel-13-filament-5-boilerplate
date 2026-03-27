<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\RoleType;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit hanya muncul jika belum di-softdelete
            EditAction::make()
                ->visible(fn() => ! $this->record->trashed()),

            // Restore hanya muncul jika SUDAH di-softdelete
            RestoreAction::make()
                ->icon(Heroicon::ArrowUturnLeft),

            ActionGroup::make([
                // 1. Change Role
                Action::make('change_role')
                    ->label('Change Role')
                    ->icon(Heroicon::ShieldCheck)
                    // ->color('warning')
                    ->color('gray')
                    ->visible(fn(): bool => Auth::id() !== $this->record->id && Auth::user()->can('change_role_user'))
                    ->schema([
                        Select::make('role')
                            ->options(RoleType::class)
                            ->default(fn() => $this->record->roles->first()?->name)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data) {
                        $this->record->syncRoles([$data['role']]);
                        Notification::make()->title('Role updated')->success()->send();
                    }),

                // 2. Toggle Active
                Action::make('toggle_active')
                    ->label(fn() => $this->record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn() => $this->record->is_active ? Heroicon::XMark : Heroicon::Check)
                    // ->color(fn() => $this->record->is_active ? 'danger' : 'success')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn() => ($this->record->is_active ? 'Deactivate' : 'Activate') . ' User?')
                    ->visible(fn(): bool => Auth::id() !== $this->record->id && Auth::user()->can('toggle_active_user'))
                    ->modalDescription('Are you sure you want to change this user\'s access status?')
                    ->action(function () {
                        $this->record->update(['is_active' => !$this->record->is_active]);
                        Notification::make()
                            ->title($this->record->is_active ? 'User activated' : 'User deactivated')
                            ->success()
                            ->send();
                    }),

                // 3. Disable 2FA
                ActionGroup::make([
                    Action::make('disable_2fa')
                        ->label('Disable 2FA')
                        ->icon(Heroicon::LockOpen)
                        // ->color('danger')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn(): bool => $this->record->hasConfirmedTwoFactor() && Auth::id() !== $this->record->id)
                        ->action(function () {
                            $this->record->disableTwoFactorAuthentication();
                            Notification::make()->title('2FA has been disabled')->success()->send();
                        }),
                ])->dropdown(false),


                // 4. Delete Action
                ActionGroup::make([
                    DeleteAction::make()
                        ->icon(Heroicon::Trash),
                ])->dropdown(false),

            ])
                ->hiddenLabel()
                ->icon(Heroicon::EllipsisVertical)
                ->color('gray')
                ->button()
                ->visible(fn() => ! $this->record->trashed()),
        ];
    }
}
