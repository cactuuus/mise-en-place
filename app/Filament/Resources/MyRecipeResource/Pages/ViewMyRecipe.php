<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMyRecipe extends ViewRecord
{
    protected static string $resource = MyRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
