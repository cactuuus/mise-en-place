<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Filament\Infolists\Components\RecipeInstructionsEntry;
use App\Filament\Resources\Recipes\RecipeResource;
use App\Models\Recipe;
use Filament\Infolists\Components;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Mokhosh\FilamentRating\Entries\RatingEntry;

class RecipeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Components\SpatieMediaLibraryImageEntry::make('image')
                            ->label(false)
                            ->imageSize('80%')
                            ->square()
                            ->visibility('private')
                            ->defaultImageUrl(asset('images/recipe-placeholder.svg'))
                            ->collection('recipe-images')
                            ->columnSpan(1),

                        Group::make()
                            ->schema([
                                TextEntry::make('title')
                                    ->label(false)
                                    ->size('64')
                                    ->weight('bold')
                                    ->extraAttributes(['class' => 'text-3xl'])
                                    ->columnSpanFull(),

                                Components\SpatieTagsEntry::make('tags')
                                    ->label(false)
                                    ->columnSpanFull(),

                                RatingEntry::make('average_rating')
                                    ->label(false)
                                    ->state(fn($record) => $record->averageRating())
                                    ->stars(5)
                                    ->color('warning')
                                    ->helperText(fn($record) => "{$record->totalRatings()} ratings")
                                    ->columnSpanFull(),

                                TextEntry::make('user.name')
                                    ->label('Created by')
                                    ->icon('tabler-user'),

                                TextEntry::make('difficulty_level')
                                    ->badge(),

                                TextEntry::make('total_time')
                                    ->label('Time')
                                    ->suffix(' min')
                                    ->icon('tabler-clock')
                                    ->hint(fn(Recipe $record): string => RecipeResource::getTimeBreakdown($record)),

                                TextEntry::make('serves')
                                    ->icon('tabler-users'),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Ingredients')
                    ->schema([
                        Components\KeyValueEntry::make('ingredients')
                            ->label(false)
                            ->keyLabel('#')
                            ->valueLabel('Ingredient'),
                    ])
                    ->columnSpanFull(),

                Section::make('Instructions')
                    ->schema([
                        RecipeInstructionsEntry::make('instructions')
                            ->label(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('notes')
                            ->prose()
                            ->columnSpanFull()
                            ->visible(fn($record) => ! empty($record->notes)),

                        TextEntry::make('source_url')
                            ->label('Source')
                            ->url(fn($record) => $record->source_url)
                            ->openUrlInNewTab()
                            ->icon('tabler-external-link')
                            ->visible(fn($record) => ! empty($record->source_url)),

                        TextEntry::make('parentRecipe.title')
                            ->label('Forked from')
                            ->formatStateUsing(fn($record)
                                => $record->parentRecipe ?
                                'Forked from "'.$record->parentRecipe->title.'" by '.$record->parentRecipe->user->name : '')
                            ->icon('tabler-git-fork')
                            ->visible(fn($record) => $record->parentRecipe),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
