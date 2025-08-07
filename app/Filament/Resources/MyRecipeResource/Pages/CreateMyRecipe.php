<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyRecipe extends CreateRecord
{
    protected static string $resource = MyRecipeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function getFormActions(): array
    {
        // Return empty array to hide default form actions since wizard has its own submit button
        return [];
    }
}
