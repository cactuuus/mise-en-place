<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $currentUser = Auth::user();
        $viewedUser  = $this->record;

        // Don't show follow button for own profile
        if ( ! $currentUser || $currentUser->id === $viewedUser->id) {
            return [];
        }

        // Create a closure that will be evaluated fresh each time
        $isFollowing = fn() => $currentUser->isFollowing($viewedUser);
        
        return [
            Action::make('toggleFollow')
                ->label(fn() => $isFollowing() ? 'Unfollow' : 'Follow')
                ->icon(fn() => $isFollowing() ? 'tabler-user-minus' : 'tabler-user-plus')
                ->color(fn() => $isFollowing() ? 'danger' : 'success')
                ->action(function (Action $action) use ($currentUser, $viewedUser) {
                    try {
                        $isFollowing = $currentUser->isFollowing($viewedUser);

                        if ($isFollowing) {
                            $currentUser->unfollow($viewedUser);
                            $action->successNotification(
                                Notification::make()
                                    ->success()
                                    ->icon('tabler-user-minus')
                                    ->title("Unfollowed $viewedUser->name."),
                            );
                        } else {
                            $currentUser->follow($viewedUser);
                            $action->successNotification(
                                Notification::make()
                                    ->success()
                                    ->icon('tabler-user-plus')
                                    ->title("Now following $viewedUser->name."),
                            );
                        }

                        $this->record->refresh();
                        $action->success();
                    } catch (\Exception $e) {
                        Log::error('Follow/Unfollow error: '.$e->getMessage());
                        $action->failure();
                    }
                })
                ->failureNotification(
                    Notification::make()
                        ->danger()
                        ->title('Ouchie boo boo!')
                        ->body('Something went wrong, please try again later or submit a bug report.'),
                ),
        ];
    }
}
