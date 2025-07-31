<?php

namespace App\Filament\Resources;

use App\Models\Recipe;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Entries\RatingEntry;
use Mokhosh\FilamentRating\RatingTheme;

abstract class BaseRecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;
    protected static ?string $modelLabel = 'Recipe';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([

                        Infolists\Components\SpatieMediaLibraryImageEntry::make('image')
                            ->label(false)
                            ->size('80%')
                            ->square()
                            ->visibility('private')
                            ->defaultImageUrl(asset('images/recipe-placeholder.svg'))
                            ->collection('recipe-images')
                            ->conversion('lg')
                            ->columnSpan(1),

                        Infolists\Components\Group::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(false)
                                    ->size('64')
                                    ->weight('bold')
                                    ->extraAttributes(['class' => 'text-3xl'])
                                    ->columnSpanFull(),

                                RatingEntry::make('average_rating')
                                    ->label(false)
                                    ->state(fn($record) => $record->averageRating())
                                    ->stars(5)
                                    ->color('warning')
                                    ->helperText(fn($record) => "{$record->totalRatings()} ratings")
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Created by')
                                    ->icon('tabler-user'),

                                Infolists\Components\TextEntry::make('difficulty_level')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('total_time')
                                    ->label('Time')
                                    ->suffix(' min')
                                    ->icon('tabler-clock')
                                    ->hint(fn(Recipe $record): string => static::getTimeBreakdown($record)),

                                Infolists\Components\TextEntry::make('serves')
                                    ->icon('tabler-users'),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Ingredients')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('ingredients')
                            ->label(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('amount')
                                    ->label(false)
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('item')
                                    ->label(false),
                            ])
                            ->contained(false)
                            ->columns(2)
                            ->grid(1),
                    ]),

                Infolists\Components\Section::make('Instructions')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('instructions')
                            ->label(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('instruction')
                                    ->label(fn() => 'Step '.static::getStepNumber())
                                    ->prose(),
                            ])
                            ->grid(1),
                    ]),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->prose()
                            ->columnSpanFull()
                            ->visible(fn($record) => ! empty($record->notes)),

                        Infolists\Components\TextEntry::make('source_url')
                            ->label('Source')
                            ->url(fn($record) => $record->source_url)
                            ->openUrlInNewTab()
                            ->icon('tabler-external-link')
                            ->visible(fn($record) => ! empty($record->source_url)),

                        Infolists\Components\TextEntry::make('parentRecipe.title')
                            ->label('Forked from')
                            ->formatStateUsing(fn($record)
                                => $record->parentRecipe ?
                                'Forked from "'.$record->parentRecipe->title.'" by '.$record->parentRecipe->user->name : '')
                            ->icon('tabler-git-fork')
                            ->visible(fn($record) => $record->parentRecipe),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    protected static function getTimeBreakdown(Recipe $record): string
    {
        $hints = [];
        if ($record->prep_time) {
            $hints[] = "{$record->prep_time} min prep";
        }
        if ($record->cook_time) {
            $hints[] = "{$record->cook_time} min cook";
        }

        return empty($hints) ? '' : implode(' + ', $hints);
    }

    protected static function getStepNumber(): int
    {
        static $stepCounter = 0;

        return ++$stepCounter;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions(static::getTableActions())
            ->bulkActions(static::getBulkActions());
    }

    protected static function getTableColumns(): array
    {
        // width(0) is used to reduce the width of a column to the minimum

        return [
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

            Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                ->width(0)
                ->label(false)
                ->square()
                ->size(80)
                ->visibility('private')
                ->extraAttributes(['class' => 'rounded-lg'])
                ->defaultImageUrl(asset('images/recipe-placeholder.svg'))
                ->tooltip('Recipe photo')
                ->collection('recipe-images')
                ->conversion('sm'),

            Tables\Columns\TextColumn::make('title')
                ->label(false)
                ->searchable()
                ->description(fn(Recipe $record): string => static::getTitleDescription($record)),

            Tables\Columns\TextColumn::make('difficulty_level')
                ->label('Difficulty')
                ->width(0)
                ->badge(),

            Tables\Columns\TextColumn::make('total_time')
                ->label('Time')
                ->width(0)
                ->suffix(' min')
                ->description(fn(Recipe $record): string => static::getTimeBreakdown($record))
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getTitleDescription(Recipe $record): string
    {
        return count($record->ingredients ?? []).' ingredients, '.
               count($record->instructions ?? []).' steps';
    }

    protected static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [];
    }

    public static function getRelations(): array
    {
        return [];
    }
}
