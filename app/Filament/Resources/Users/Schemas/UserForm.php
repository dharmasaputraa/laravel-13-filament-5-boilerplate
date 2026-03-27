<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    // public static function configure(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             Tabs::make('User Management')
    //                 ->schema([
    //                     // Tab 1: Basic Profile Information
    //                     Tab::make('Personal Information')
    //                         ->icon(Heroicon::User)
    //                         ->schema([
    //                             // Section::make('Basic Info')
    //                             //     ->schema([
    //                             Grid::make(6)
    //                                 ->schema([
    //                                     // Left Column: Avatar
    //                                     FileUpload::make('avatar_url')
    //                                         ->label('Avatar')
    //                                         ->disk('s3')
    //                                         ->directory('avatars')
    //                                         ->image()
    //                                         ->imageEditor()
    //                                         ->maxSize(2048)
    //                                         ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    //                                         ->automaticallyCropImagesToAspectRatio('1:1')
    //                                         ->automaticallyResizeImagesToWidth(300)
    //                                         ->automaticallyResizeImagesToHeight(300)
    //                                         ->columnSpan(2),

    //                                     // Right Column: Stacked Inputs
    //                                     Group::make()
    //                                         ->schema([
    //                                             TextInput::make('name')
    //                                                 ->required()
    //                                                 ->maxLength(255),

    //                                             TextInput::make('email')
    // ->required()
    // ->email()
    // ->maxLength(255)
    // ->unique(User::class, 'email', ignoreRecord: true),
    //                                         ])
    //                                         ->columnSpan(4),
    //                                 ]),
    //                             //         ])
    //                         ]),

    //                     // Tab 2: Security & Authentication
    //                     Tab::make('Security')
    //                         ->icon(Heroicon::LockClosed)
    //                         ->schema([
    //                             Grid::make(6)
    //                                 ->schema([
    //                                     // Kiri: Password Fields (Column Span 4)
    //                                     Group::make([
    //                                         TextInput::make('password')
    //                                             ->password()
    //                                             ->revealable()
    //                                             ->required(fn($operation) => $operation === 'create')
    //                                             ->dehydrated(fn($state) => filled($state)),

    //                                         TextInput::make('password_confirmation')
    //                                             ->password()
    //                                             ->revealable()
    //                                             ->required(fn($operation) => $operation === 'create')
    //                                             ->same('password')
    //                                             ->dehydrated(false),
    //                                     ])->columnSpan(4),

    //                                     // Kanan: 2FA Status & Action (Column Span 2)
    //                                     Group::make([
    //                                         TextInput::make('breezy_2fa_status')
    //                                             ->label('2FA Status')
    //                                             ->disabled()
    //                                             ->dehydrated(false)
    //                                             // Disembunyikan jika sudah aktif sesuai request
    //                                             ->hidden(fn(?User $record) => $record?->hasConfirmedTwoFactor())
    //                                             ->formatStateUsing(function (?User $record) {
    //                                                 if (!$record) return "Disabled";
    //                                                 return $record->hasConfirmedTwoFactor() ? "Active" : "Disabled";
    //                                             }),

    //                                         // Tombol Reset hanya muncul jika 2FA Aktif
    //                                         Action::make('reset_2fa')
    //                                             ->label('Disable & Reset 2FA')
    //                                             ->color('danger')
    //                                             ->icon(Heroicon::ShieldExclamation)
    //                                             ->requiresConfirmation()
    //                                             ->action(function (User $record) {
    //                                                 $record->disableTwoFactorAuthentication();
    //                                                 \Filament\Notifications\Notification::make()
    //                                                     ->title('2FA Reset Successfully')
    //                                                     ->success()
    //                                                     ->send();
    //                                             })
    //                                             ->visible(fn(?User $record) => $record?->hasConfirmedTwoFactor()),
    //                                     ])
    //                                         ->columnSpan(2)
    //                                         ->extraAttributes(['class' => 'flex flex-col gap-4']), // Memberi jarak antar elemen tanpa section
    //                                 ]),
    //                         ]),

    //                     // Tab 3: Metadata / Timestamps
    //                     Tab::make('Account Status')
    //                         ->icon(Heroicon::ShieldCheck)
    //                         ->schema([
    //                             // Langsung tampilkan tanpa Section
    //                             DateTimePicker::make('email_verified_at')
    //                                 ->label('Email Verified Date')
    //                                 ->native(false)
    //                                 ->columnSpan(2),
    //                         ]),
    //                 ])
    //                 ->columnSpanFull(),
    //         ]);
    // }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make("Personal Information")
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan('full'),

                                        TextInput::make('email')
                                            ->required()
                                            ->email()
                                            ->maxLength(255)
                                            ->unique(User::class, 'email', ignoreRecord: true)
                                            ->columnSpan('full'),
                                    ]),
                            ]),
                        Section::make("Password")
                            ->schema([
                                Group::make([
                                    TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->required(fn($operation) => $operation === 'create')
                                        ->dehydrated(fn($state) => filled($state)),

                                    TextInput::make('password_confirmation')
                                        ->password()
                                        ->revealable()
                                        ->required(fn($operation) => $operation === 'create')
                                        ->same('password')
                                        ->dehydrated(false),
                                ])
                            ]),
                        Section::make("Status")
                            ->schema([
                                Group::make([
                                    DateTimePicker::make('email_verified_at')
                                        ->label('Email Verified Date')
                                        ->native(false)
                                        ->columnSpan(2),
                                ])
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Avatar')
                            ->schema([
                                FileUpload::make('avatar_url')
                                    ->hiddenLabel()
                                    ->disk('s3')
                                    ->directory('avatars')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->automaticallyCropImagesToAspectRatio('1:1')
                                    ->automaticallyResizeImagesToWidth(300)
                                    ->automaticallyResizeImagesToHeight(300)
                                    ->columnSpan(2),
                            ]),
                        // Section::make("Two-Factor Authentication")
                        //     ->schema([
                        //         Grid::make(3) // Membagi section menjadi 3 kolom agar tombol bisa di samping input
                        //             ->schema([
                        //                 // 1. Status Saat Ini (Input Read-only)
                        //                 TextInput::make('breezy_2fa_status')
                        //                     ->label('Current Status')
                        //                     ->placeholder('Disabled')
                        //                     ->disabled()
                        //                     ->dehydrated(false)
                        //                     // Sembunyikan input ini jika 2FA sudah aktif (opsional, sesuai request sebelumnya)
                        //                     ->hidden(fn(?User $record) => $record?->hasConfirmedTwoFactor())
                        //                     ->formatStateUsing(function (?User $record) {
                        //                         if (! $record) return "Disabled";
                        //                         return $record->hasConfirmedTwoFactor() ? "Active" : "Disabled";
                        //                     })
                        //                     ->columnSpan(3),

                        //                 // 2. Tombol Disable / Reset
                        //                 Group::make([
                        //                     Action::make('reset_2fa')
                        //                         ->label('Disable 2FA')
                        //                         ->color('danger')
                        //                         ->requiresConfirmation()
                        //                         ->modalHeading('Disable Two-Factor Authentication?')
                        //                         ->modalDescription('This will immediately disable 2FA for this user and delete their security keys.')
                        //                         ->action(function (User $record) {
                        //                             // Memanggil method dari trait Breezy untuk menghapus session 2FA
                        //                             $record->disableTwoFactorAuthentication();

                        //                             \Filament\Notifications\Notification::make()
                        //                                 ->title('2FA has been disabled')
                        //                                 ->success()
                        //                                 ->send();
                        //                         }),
                        //                 ])->visible(fn(?User $record) => $record?->hasConfirmedTwoFactor())
                        //                     ->columnSpan(3),
                        //             ]),
                        //     ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
