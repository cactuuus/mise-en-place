<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Difficulty;
use App\Filament\Resources\BrowseRecipeResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class PublicRecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'publicRecipes';

    public function form(Form $form): Form
    {
        // This is read-only - users can't edit recipes from here
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => 
                        count($record->ingredients ?? []) . ' ingredients, ' .
                        count($record->instructions ?? []) . ' steps'
                    ),

                Tables\Columns\TextColumn::make('prep_time')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cook_time')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->formatStateUsing(fn(?Difficulty $state): string
                        => $state?->label() ?? 'Not set',
                    )
                    ->badge()
                    ->color(fn(?Difficulty $state): string
                        => $state?->color() ?? 'gray',
                    ),

                RatingColumn::make('ratings_avg_rating')
                    ->label('Rating')
                    ->avg('ratings', 'rating')
                    ->stars(5)
                    ->color('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->options(Difficulty::class),
            ])
            ->headerActions([
                // No header actions - this is read-only
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Recipe')
                    ->icon('tabler-eye')
                    ->url(fn($record) => BrowseRecipeResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->defaultSort('created_at', 'desc');
    }
}
