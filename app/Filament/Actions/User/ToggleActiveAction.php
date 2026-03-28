<?php

namespace App\Filament\Actions\User;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ToggleActiveAction
{
    public static function make($livewire): Action
    {
        return Action::make('toggle_active')
            ->label(fn() => $livewire->record->is_active ? 'Deactivate' : 'Activate')
            ->icon(fn() => $livewire->record->is_active ? Heroicon::XMark : Heroicon::Check)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(fn() => ($livewire->record->is_active ? 'Deactivate' : 'Activate') . ' User?')
            ->modalDescription('Are you sure you want to change this user\'s access status?')
            ->visible(fn($livewire) => Auth::user()->can('toggleActive', $livewire->record))
            ->authorize(fn($livewire) => Auth::user()->can('toggleActive', $livewire->record))
            ->action(function () use ($livewire) {
                $livewire->record->update([
                    'is_active' => ! $livewire->record->is_active,
                ]);

                Notification::make()
                    ->title($livewire->record->is_active ? 'User activated' : 'User deactivated')
                    ->success()
                    ->send();
            });
    }
}
