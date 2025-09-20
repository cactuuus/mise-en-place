<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->required(fn(?User $record) => $record === null)
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required(fn(?User $record) => $record === null)
                        ->maxLength(255),
                    TextInput::make('password')
                        ->password()
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(?User $record) => $record === null)
                        ->minLength(8)
                        ->maxLength(255)
                        ->same('passwordConfirmation'),
                    TextInput::make('passwordConfirmation')
                        ->password()
                        ->label('Password Confirmation')
                        ->dehydrated(false)
                        ->required(fn(?User $record) => $record === null)
                        ->minLength(8)
                        ->maxLength(255),
                ])
                ->columnSpanFull(),
        ]);
    }
}
