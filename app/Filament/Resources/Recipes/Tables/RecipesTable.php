<?php

namespace App\Filament\Resources\Recipes\Tables;

use App\Filament\Resources\Recipes\RecipeResource;
use App\Models\Recipe;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                RatingColumn::make('ratings_avg_rating')
                    ->width(0)
                    ->label('Rating')
                    ->theme(RatingTheme::HalfStars)
                    ->size('xs')
                    ->avg('ratings', 'rating')
                    ->stars(5)
                    ->color('warning')
                    ->placeholder('Not rated yet')
                    ->sortable(),

                SpatieMediaLibraryImageColumn::make('image')
                    ->width(0)
                    ->label(false)
                    ->square()
                    ->imageSize(80)
                    ->visibility('private')
                    ->extraAttributes(['class' => 'rounded-lg'])
                    ->defaultImageUrl(asset('images/recipe-placeholder.svg'))
                    ->tooltip('Recipe photo')
                    ->collection('recipe-images')
                    ->conversion('small'),

                TextColumn::make('title')
                    ->label(false)
                    ->searchable()
                    ->description(fn(Recipe $record): string => RecipeResource::getTitleDescription($record)),

                TextColumn::make('difficulty_level')
                    ->label('Difficulty')
                    ->width(0)
                    ->badge(),

                TextColumn::make('total_time')
                    ->label('Time')
                    ->width(0)
                    ->suffix(' min')
                    ->description(fn(Recipe $record): string => RecipeResource::getTimeBreakdown($record))
                    ->sortable(),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_public')
                    ->options([
                        1 => 'Public',
                        0 => 'Private',
                    ])
                    ->label('Visibility'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
