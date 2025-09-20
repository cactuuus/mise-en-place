<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components;
use Filament\Schemas;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Components\Section::make('Profile')
                    ->schema([
                        Schemas\Components\Grid::make(4)
                            ->schema([
                                Components\SpatieMediaLibraryImageEntry::make('avatar')
                                    ->hiddenLabel()
                                    ->circular()
                                    ->visibility('private')
                                    ->defaultImageUrl(asset('images/avatar-placeholder.svg'))
                                    ->collection('avatar')
                                    ->conversion('large'),
                                Schemas\Components\Grid::make(3)
                                    ->schema([
                                        Components\TextEntry::make('name')
                                            ->icon('tabler-user')
                                            ->weight('bold'),
                                        Components\TextEntry::make('email')
                                            ->icon('tabler-mail'),
                                        Components\TextEntry::make('created_at')
                                            ->label('Joined')
                                            ->date()
                                            ->icon('tabler-calendar'),

                                        Components\TextEntry::make('recipes_count')
                                            ->label('Total Recipes')
                                            ->state(fn($record) => $record->recipes()->count())
                                            ->icon('tabler-book'),

                                        Components\TextEntry::make('followers_count')
                                            ->label('Followers')
                                            ->state(fn($record) => $record->followersCount())
                                            ->icon('tabler-users'),

                                        Components\TextEntry::make('following_count')
                                            ->label('Following')
                                            ->state(fn($record) => $record->followingCount())
                                            ->icon('tabler-user-plus'),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
