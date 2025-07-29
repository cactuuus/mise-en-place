<?php

namespace App\Filament\Resources\BrowseRecipeResource\Pages;

use App\Filament\Resources\BrowseRecipeResource;
use App\Filament\Resources\MyRecipeResource;
use App\Helpers\ErrorMessages;
use App\Models\Recipe;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mokhosh\FilamentRating\Components\Rating;

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

            Action::make('rate')
                ->label('Rate Recipe')
                ->icon('tabler-star')
                ->color('success')
                ->form([
                    Rating::make('rating')
                        ->label('Your Rating')
                        ->required()
                        ->default(fn() => $this->record->getUserRating(Auth::id()) ?? 5)
                        ->stars(5)
                        ->color('warning'),
                ])
                ->action(function (array $data, Recipe $record, Action $action) {
                    try {
                        $record->rate(Auth::id(), $data['rating']);
                        $action->success();
                    } catch (\Exception $e) {
                        Log::error('Rating error: '.$e->getMessage());
                        $action->failure();
                    }
                })
                ->modalHeading(fn() => 'Rate "'.$this->record->title.'"')
                ->modalDescription('Share your experience with this recipe')
                ->modalSubmitActionLabel('Submit Rating')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Recipe rated successfully!'),
                )
                ->failureNotification(
                    Notification::make()
                        ->danger()
                        ->title(ErrorMessages::random())
                        ->body('Something went wrong, your rating was not saved. Please try again later or submit a bug report.'),
                )
                ->visible(fn() => Auth::check()),
        ];
    }
}
