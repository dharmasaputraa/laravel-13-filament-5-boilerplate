<?php

namespace App\Filament\Actions\User;

use App\Enums\RoleType;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ChangeRoleAction
{
    public static function make($livewire): Action
    {
        return Action::make('change_role')
            ->label('Change Role')
            ->icon(Heroicon::ShieldCheck)
            ->color('gray')
            ->visible(fn($livewire) => Auth::user()->can('changeRole', $livewire->record))
            ->authorize(fn($livewire) => Auth::user()->can('changeRole', $livewire->record))
            ->schema([
                Select::make('role')
                    ->options(RoleType::class)
                    ->default(fn() => $livewire->record->roles->first()?->name)
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) use ($livewire) {
                $livewire->record->assignSingleRole($data['role']->value);

                Notification::make()
                    ->title('Role updated')
                    ->success()
                    ->send();
            });
    }
}
