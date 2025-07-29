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
}
