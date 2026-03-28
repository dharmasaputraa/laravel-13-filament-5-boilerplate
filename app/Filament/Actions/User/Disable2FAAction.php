<?php

namespace App\Filament\Actions\User;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class Disable2FAAction
{
    public static function make($livewire): Action
    {
        return Action::make('disable_2fa')
            ->label('Disable 2FA')
            ->icon(Heroicon::LockOpen)
            ->color('gray')
            ->requiresConfirmation()
            ->visible(fn($livewire) => Auth::user()->can('disable2FA', $livewire->record))
            ->authorize(fn($livewire) => Auth::user()->can('disable2FA', $livewire->record))
            ->action(function () use ($livewire) {
                $livewire->record->disableTwoFactorAuthentication();

                Notification::make()
                    ->title('2FA has been disabled')
                    ->success()
                    ->send();
            });
    }
}
