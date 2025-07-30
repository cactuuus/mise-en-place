<?php

namespace App\Filament\Resources;

use App\Enums\Difficulty;
use App\Filament\Resources\MyRecipeResource\Pages;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyRecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;
    protected static ?string $navigationIcon = 'tabler-soup';
    protected static ?string $navigationLabel = 'My Recipes';
    protected static ?string $modelLabel = 'Recipe';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Repeater::make('ingredients')
                    ->schema([
                        Forms\Components\TextInput::make('item')
                            ->required()
                            ->placeholder('e.g., flour, eggs, milk'),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->placeholder('e.g., 2 cups, 3 large, 1 tsp'),
                    ])
                    ->columns(2)
                    ->required()
                    ->minItems(1)
                    ->addActionLabel('Add ingredient')
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('instructions')
                    ->schema([
                        Forms\Components\Textarea::make('instruction')
                            ->required()
                            ->placeholder('Describe this step in detail')
                            ->rows(2),
                    ])
                    ->required()
                    ->minItems(1)
                    ->addActionLabel('Add step')
                    ->orderColumn('step')
                    ->defaultItems(1)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('notes')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->directory('recipe-images')
                    ->visibility('private')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('source_url')
                    ->url()
                    ->placeholder('https://example.com/recipe')
                    ->helperText('If this recipe is from an external website')
                    ->columnSpanFull(),

                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('prep_time')
                            ->numeric()
                            ->suffix('minutes')
                            ->minValue(0),

                        Forms\Components\TextInput::make('cook_time')
                            ->numeric()
                            ->suffix('minutes')
                            ->minValue(0),

                        Forms\Components\TextInput::make('serves')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\Select::make('difficulty_level')
                            ->options(Difficulty::options())
                            ->placeholder('Select difficulty'),
                    ]),

                Forms\Components\Toggle::make('is_public')
                    ->default(true)
                    ->helperText('Allow other users to see and fork this recipe'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_path')
                            ->size(200)
                            ->visibility('private')
                            ->columnSpanFull(),


                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('average_rating')
                                    ->label('Rating')
                                    ->state(fn($record,
                                    )
                                        => $record->averageRating() > 0 ? number_format($record->averageRating(),
                                            1).'/5 stars ('.$record->totalRatings().' ratings)' : 'No ratings yet')
                                    ->icon('tabler-star'),
                            ]),

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
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->square()
                    ->size(60)
                    ->visibility('private'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Recipe $record): string
                        => count($record->ingredients ?? []).' ingredients, '.
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

                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('ratings_avg_rating')
                    ->label('Rating')
                    ->avg('ratings', 'rating')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 1).'/5' : 'No ratings')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListMyRecipes::route('/'),
            'create' => Pages\CreateMyRecipe::route('/create'),
            'view'   => Pages\ViewMyRecipe::route('/{record}'),
            'edit'   => Pages\EditMyRecipe::route('/{record}/edit'),
        ];
    }
}
