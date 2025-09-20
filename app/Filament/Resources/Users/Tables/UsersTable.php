<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->width(0)
                    ->label(false)
                    ->circular()
                    ->imageSize(50)
                    ->visibility('private')
                    ->defaultImageUrl(asset('images/avatar-placeholder.svg'))
                    ->collection('avatar')
                    ->conversion('small'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('recipes_count')
                    ->label('Recipes')
                    ->counts('recipes')
                    ->sortable(),

                TextColumn::make('followers_count')
                    ->label('Followers')
                    ->counts('followers')
                    ->sortable(),

                TextColumn::make('following_count')
                    ->label('Following')
                    ->counts('following')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
