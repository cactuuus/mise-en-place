<?php

namespace App\Filament\Resources\MyRecipeResource\Pages;

use App\Filament\Resources\MyRecipesResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyRecipe extends CreateRecord
{
    protected static string $resource = MyRecipesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }
}
