<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Recipes\RecipeResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'recipes';

    public function table(Table $table): Table
    {
        return RecipeResource::table($table);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return RecipeResource::infolist($schema);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
