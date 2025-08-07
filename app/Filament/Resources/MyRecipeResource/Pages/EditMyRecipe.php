<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyRecipe extends EditRecord
{
    protected static string $resource = MyRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        // Return empty array to hide default form actions since wizard has its own submit button
        return [];
    }
}
