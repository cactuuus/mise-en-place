<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyRecipe extends EditRecord
{
    protected static string $resource = MyRecipesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
