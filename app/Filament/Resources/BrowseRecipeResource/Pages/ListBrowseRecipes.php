<?php

namespace App\Filament\Resources\BrowseRecipeResource\Pages;

use App\Filament\Resources\BrowseRecipeResource;
use Filament\Resources\Pages\ListRecords;

class ListBrowseRecipes extends ListRecords
{
    protected static string $resource = BrowseRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for browsing recipes
        ];
    }
}
