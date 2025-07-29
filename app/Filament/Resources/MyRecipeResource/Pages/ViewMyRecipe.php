<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMyRecipe extends ViewRecord
{
    protected static string $resource = MyRecipesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
