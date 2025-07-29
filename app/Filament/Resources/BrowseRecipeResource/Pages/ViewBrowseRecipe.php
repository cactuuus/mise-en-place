<?php

namespace App\Filament\Resources\BrowseRecipeResource\Pages;

use App\Filament\Resources\BrowseRecipeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBrowseRecipe extends ViewRecord
{
    protected static string $resource = BrowseRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Fork and Rate actions will be added later
        ];
    }
}
