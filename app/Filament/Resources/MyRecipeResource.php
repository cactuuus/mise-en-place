<?php

namespace App\Filament\Resources;

use App\Enums\Difficulty;
use App\Enums\TagType;
use App\Filament\Resources\MyRecipeResource\Pages;
use App\Services\RecipeImportService;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
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
                Forms\Components\Wizard::make([

                    Forms\Components\Wizard\Step::make('General info')
                        ->icon('tabler-question-mark')
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('import_from_url')
                                    ->label('Import from URL')
                                    ->icon('tabler-world-www')
                                    ->color('info')
                                    ->form([
                                        Forms\Components\TextInput::make('recipe_url')
                                            ->label('Recipe URL')
                                            ->url()
                                            ->required()
                                            ->placeholder('https://example.com/recipe')
                                            ->helperText('Enter the URL of the recipe you want to import'),
                                    ])
                                    ->action(function (array $data, Forms\Set $set) {
                                        $url = $data['recipe_url'];

                                        try {
                                            // Use the service instead of making HTTP request
                                            $importService = new RecipeImportService();
                                            $recipeData    = $importService->importFromUrl($url);

                                            // Populate form fields
                                            $set('title', $recipeData['title']);
                                            $set('source_url', $recipeData['source_url']);
                                            $set('prep_time', $recipeData['prep_time']);
                                            $set('cook_time', $recipeData['cook_time']);
                                            $set('serves', $recipeData['serves']);
                                            $set('difficulty_level', $recipeData['difficulty_level']);
                                            $set('ingredients', $recipeData['ingredients']);
                                            $set('instructions', $recipeData['instructions']);

                                            \Filament\Notifications\Notification::make()
                                                ->title('Recipe imported successfully!')
                                                ->success()
                                                ->send();
                                        } catch (\Exception $e) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Failed to import recipe')
                                                ->body('Please check the URL and try again. Error: '.$e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ])
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g., Grandma\'s Chocolate Chip Cookies')
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('is_public')
                                ->label('Share this recipe publicly')
                                ->helperText('Other users will be able to see and fork your recipe')
                                ->default(true)
                                ->columnSpanFull(),

                            SpatieTagsInput::make('tags')
                                ->label('Tags')
                                ->placeholder('Add tags like "vegetarian", "italian", "dessert"...')
                                ->type(TagType::Recipe->value)
                                ->hint('(optional)')
                                ->columnSpanFull(),

                            Forms\Components\Grid::make([
                                'default' => 1,
                                'sm'      => 2,
                                'md'      => 4,
                            ])
                                ->schema([
                                    Forms\Components\TextInput::make('prep_time')
                                        ->label('Prep Time')
                                        ->integer()
                                        ->suffixIcon('tabler-clock')
                                        ->suffix('minutes')
                                        ->minValue(0)
                                        ->required(),

                                    Forms\Components\TextInput::make('cook_time')
                                        ->label('Cook Time')
                                        ->integer()
                                        ->suffixIcon('tabler-clock')
                                        ->suffix('minutes')
                                        ->minValue(0)
                                        ->required()
                                        ->extraAttributes(['class' => 'text-center']),

                                    Forms\Components\TextInput::make('serves')
                                        ->label('Serves')
                                        ->integer()
                                        ->suffixIcon('tabler-users')
                                        ->suffix('people')
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->extraAttributes(['class' => 'text-center']),

                                    Forms\Components\Select::make('difficulty_level')
                                        ->label('Difficulty Level')
                                        ->options(Difficulty::class)
                                        ->required()
                                        ->placeholder('Select difficulty')
                                        ->native(false),
                                ]),

                            Forms\Components\TextInput::make('source_url')
                                ->label('Recipe Source')
                                ->url()
                                ->placeholder('https://example.com/original-recipe')
                                ->hint('(optional)')
                                ->helperText('Link to the original recipe if this is adapted from somewhere')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Wizard\Step::make('Ingredients')
                        ->icon('tabler-shopping-cart')
                        ->schema([
                            TableRepeater::make('ingredients')
                                ->label(false)
                                ->headers([
                                    Header::make('amount')
                                        ->label('Amount')
                                        ->width('100px'),
                                    Header::make('item')
                                        ->label('Ingredient'),
                                ])
                                ->schema([
                                    Forms\Components\TextInput::make('amount')
                                        ->required()
                                        ->placeholder('2 cups')
                                        ->extraAttributes(['class' => 'text-center font-mono']),

                                    Forms\Components\TextInput::make('item')
                                        ->required()
                                        ->placeholder('all-purpose flour'),
                                ])
                                ->required()
                                ->minItems(1)
                                ->defaultItems(3)
                                ->addActionLabel('Add ingredient')
                                ->stackAt('sm')
                                ->reorderableWithButtons()
                                ->reorderableWithDragAndDrop()
                                ->streamlined(),
                        ]),

                    Forms\Components\Wizard\Step::make('Instructions')
                        ->icon('tabler-list-numbers')
                        ->schema([
                            Forms\Components\Repeater::make('instructions')
                                ->label(false)
                                ->schema([
                                    Forms\Components\RichEditor::make('instruction')
                                        ->label(false)
                                        ->required()
                                        ->placeholder('Describe this step in detail')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'bulletList',
                                            'orderedList',
                                        ]),
                                ])
                                ->required()
                                ->minItems(1)
                                ->addActionLabel('Add step')
                                ->orderColumn('step')
                                ->reorderableWithButtons()
                                ->reorderableWithDragAndDrop()
                                ->defaultItems(1)
                                ->itemLabel(fn() => 'Step '.BaseRecipeResource::getStepNumber())
                                ->extraAttributes(['class' => 'seamless-repeater']),
                        ]),

                    Forms\Components\Wizard\Step::make('Photo & Notes')
                        ->icon('tabler-camera')
                        ->schema([
                            Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                                ->label('Recipe Photo')
                                ->helperText('Upload a photo of your finished dish to make it more appealing!')
                                ->image()
                                ->imageEditor()
                                ->imageCropAspectRatio('1:1')
                                ->imageEditorAspectRatios(['1:1'])
                                ->imageEditorViewportWidth(800)
                                ->imageEditorViewportHeight(800)
                                ->visibility('private')
                                ->maxSize(5120)
                                ->collection('recipe-images')
                                ->imagePreviewHeight('200')
                                ->panelLayout('integrated')
                                ->extraAttributes([
                                    'class' => 'custom-recipe-upload',
                                ]),

                            Forms\Components\RichEditor::make('notes')
                                ->label('Additional Notes (Optional)')
                                ->placeholder('Any tips, variations, or additional information about this recipe...')
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'bulletList',
                                    'orderedList',
                                ])
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString()
//                    ->skippable(fn($operation) => $operation === 'edit')
                    ->skippable()
                    ->submitAction(
                        Forms\Components\Actions\Action::make('create')
                            ->label('Done')
                            ->icon('tabler-check')
                            ->color('success')
                            ->submit('create'),
                    ),
            ])
            ->extraAttributes([
                'class' => 'recipe-wizard',
            ]);
    }

    // ... rest of your methods remain the same
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
