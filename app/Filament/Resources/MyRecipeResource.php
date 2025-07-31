<?php

namespace App\Filament\Resources;

use App\Enums\Difficulty;
use App\Enums\TagType;
use App\Filament\Resources\MyRecipeResource\Pages;
use Filament\Forms;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyRecipeResource extends BaseRecipeResource
{
    protected static ?string $navigationIcon = 'tabler-soup';
    protected static ?string $navigationLabel = 'My Recipes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Toggle::make('is_public')
                                    ->label('Make public')
                                    ->default(true),

                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),

                                SpatieTagsInput::make('tags')
                                    ->type(TagType::Recipe->value),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('prep_time')
                                            ->numeric()
                                            ->suffix('minutes')
                                            ->minValue(0)
                                            ->required(),

                                        Forms\Components\TextInput::make('cook_time')
                                            ->numeric()
                                            ->suffix('minutes')
                                            ->minValue(0)
                                            ->required(),

                                        Forms\Components\TextInput::make('serves')
                                            ->numeric()
                                            ->suffix('people')
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),

                                        Forms\Components\Select::make('difficulty_level')
                                            ->options(Difficulty::class)
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                            ->label('Image')
                            ->hint('Upload a photo of your finished dish (optional)')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageEditorAspectRatios(['1:1'])
                            ->imageEditorViewportWidth(800)
                            ->imageEditorViewportHeight(800)
                            ->visibility('private')
                            ->maxSize(5120) // 5MB max file size
                            ->collection('recipe-images'),

                        Forms\Components\TextInput::make('source_url')
                            ->url()
                            ->placeholder('https://example.com/recipe')
                            ->helperText('If this recipe is from an external website')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('ingredients')
                            ->label('List of ingredients')
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->required()
                                    ->placeholder('e.g., 2 cups, 3 large, 1 tsp'),

                                Forms\Components\TextInput::make('item')
                                    ->required()
                                    ->placeholder('e.g., flour, eggs, milk'),
                            ])
                            ->columns(2)
                            ->required()
                            ->minItems(1)
                            ->addActionLabel('Add ingredient'),

                        Forms\Components\Repeater::make('instructions')
                            ->label('Step by step instructions')
                            ->schema([
                                Forms\Components\Textarea::make('instruction')
                                    ->label(fn() => 'Step '.BaseRecipeResource::getStepNumber())
                                    ->required()
                                    ->placeholder('Describe this step in detail')
                                    ->rows(2),
                            ])
                            ->required()
                            ->minItems(1)
                            ->addActionLabel('Add step')
                            ->orderColumn('step')
                            ->reorderableWithButtons()
                            ->defaultItems(1),

                        Forms\Components\Textarea::make('Additional notes')
                            ->hint('(optional)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
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

    protected static function getTableColumns(): array
    {
        $columns = parent::getTableColumns();

        $publicColumn = Tables\Columns\IconColumn::make('is_public')
            ->label('Public')
            ->boolean();
        // Insert before the created_at column
        array_splice($columns, -1, 0, [$publicColumn]);

        return $columns;
    }

    protected static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
