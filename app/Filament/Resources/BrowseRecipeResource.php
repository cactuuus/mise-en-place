<?php

namespace App\Filament\Resources;

use App\Enums\Difficulty;
use App\Filament\Resources\BrowseRecipeResource\Pages;
use App\Models\Recipe;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\Entries\RatingEntry;

class BrowseRecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;
    protected static ?string $navigationIcon = 'tabler-world';
    protected static ?string $navigationLabel = 'Browse Recipes';
    protected static ?string $modelLabel = 'Recipe';
    protected static ?string $pluralModelLabel = 'Browse Recipes';

    public static function form(Form $form): Form
    {
        // This resource is read-only, no form needed
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->size(200)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('title')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created by')
                            ->icon('tabler-user')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('prep_time')
                                    ->suffix(' min')
                                    ->icon('tabler-clock'),

                                Infolists\Components\TextEntry::make('cook_time')
                                    ->suffix(' min')
                                    ->icon('tabler-flame'),

                                Infolists\Components\TextEntry::make('serves')
                                    ->icon('tabler-users'),

                                Infolists\Components\TextEntry::make('difficulty_level')
                                    ->formatStateUsing(fn(?Difficulty $state): string
                                        => $state?->label() ?? 'Not set',
                                    )
                                    ->badge()
                                    ->color(fn(?Difficulty $state): string
                                        => $state?->color() ?? 'gray',
                                    )
                                    ->icon('tabler-star'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Ingredients')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('ingredients')
                            ->schema([
                                Infolists\Components\TextEntry::make('amount')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('item'),
                            ])
                            ->columns(2)
                            ->grid(1),
                    ]),

                Infolists\Components\Section::make('Instructions')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('instructions')
                            ->label(false)
                            ->schema([
                                Infolists\Components\TextEntry::make('instruction')
                                    ->label(fn() => 'Step '.self::getStepNumber())
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

                        RatingEntry::make('average_rating')
                            ->label('Average Rating')
                            ->state(fn($record) => $record->averageRating())
                            ->stars(5)
                            ->color('warning')
                            ->helperText(fn($record) => $record->totalRatings() . ' ratings'),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    private static function getStepNumber(): int
    {
        static $stepCounter = 0;

        return ++$stepCounter;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_public', true)
            ->where('user_id', '!=', Auth::id())
            ->with(['user', 'ratings']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Recipe $record): string
                        => 'by '.$record->user->name.' • '.
                           count($record->ingredients ?? []).' ingredients, '.
                           count($record->instructions ?? []).' steps',
                    ),

                Tables\Columns\TextColumn::make('prep_time')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cook_time')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('serves')
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
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Fork and Rate actions will be added later
            ])
            ->bulkActions([
                // No bulk actions for browsing recipes
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrowseRecipes::route('/'),
            'view'  => Pages\ViewBrowseRecipe::route('/{record}'),
        ];
    }
}
