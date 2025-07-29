<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'tabler-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        // This resource is read-only for social features
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Profile')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(false)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Joined')
                                    ->date()
                                    ->icon('tabler-calendar'),

                                Infolists\Components\TextEntry::make('public_recipes_count')
                                    ->label('Public Recipes')
                                    ->state(fn($record) => $record->publicRecipes()->count())
                                    ->icon('tabler-book'),

                                Infolists\Components\TextEntry::make('followers_count')
                                    ->label('Followers')
                                    ->state(fn($record) => $record->followersCount())
                                    ->icon('tabler-users'),

                                Infolists\Components\TextEntry::make('following_count')
                                    ->label('Following')
                                    ->state(fn($record) => $record->followingCount())
                                    ->icon('tabler-user-plus'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('public_recipes_count')
                    ->label('Public Recipes')
                    ->counts('publicRecipes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('followers_count')
                    ->label('Followers')
                    ->counts('followers')
                    ->sortable(),

                Tables\Columns\TextColumn::make('following_count')
                    ->label('Following')
                    ->counts('following')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_public_recipes')
                    ->label('Has Public Recipes')
                    ->query(fn(Builder $query): Builder => $query->has('publicRecipes')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for users
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PublicRecipesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view'  => Pages\ViewUser::route('/{record}'),
        ];
    }
}
