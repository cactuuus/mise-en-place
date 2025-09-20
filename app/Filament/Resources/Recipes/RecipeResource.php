<?php

namespace App\Filament\Resources\Recipes;

use App\Filament\Resources\Recipes\Pages\CreateRecipe;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Recipes\Pages\ViewRecipe;
use App\Filament\Resources\Recipes\Schemas\RecipeForm;
use App\Filament\Resources\Recipes\Schemas\RecipeInfolist;
use App\Filament\Resources\Recipes\Tables\RecipesTable;
use App\Models\Recipe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-soup';

    public static function form(Schema $schema): Schema
    {
        return RecipeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RecipeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecipesTable::configure($table);
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
            'index'  => ListRecipes::route('/'),
            'create' => CreateRecipe::route('/create'),
            'view'   => ViewRecipe::route('/{record}'),
        ];
    }

    public static function getTitleDescription(Recipe $record): string
    {
        return count($record->ingredients ?? []).' ingredients, '.
               count($record->instructions ?? []).' steps';
    }

    public static function getTimeBreakdown(Recipe $record): string
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

    public static function getStepNumber(): int
    {
        static $stepCounter = 0;

        return ++$stepCounter;
    }
}
