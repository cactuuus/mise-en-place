<?php

namespace App\Filament\Resources\BrowseRecipeResource\Pages;

use App\Filament\Resources\BrowseRecipeResource;
use App\Filament\Resources\MyRecipeResource;
use App\Models\Recipe;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewBrowseRecipe extends ViewRecord
{
    protected static string $resource = BrowseRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fork')
                ->label('Fork Recipe')
                ->icon('tabler-grill-fork')
                ->color('warning')
                ->action(function (Recipe $record) {
                    $forkedRecipe = $record->fork(Auth::id());

                    return redirect(MyRecipeResource::getUrl('edit', ['record' => $forkedRecipe]));
                })
                ->requiresConfirmation()
                ->modalHeading('Fork this recipe?')
                ->modalDescription('This will create a copy of this recipe that you can modify and make your own.')
                ->modalSubmitActionLabel('Fork Recipe')
                ->visible(fn() => Auth::check()),
        ];
    }
}
