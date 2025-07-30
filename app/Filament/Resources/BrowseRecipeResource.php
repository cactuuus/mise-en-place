<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrowseRecipeResource\Pages;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BrowseRecipeResource extends BaseRecipeResource
{
    protected static ?string $navigationIcon = 'tabler-world';
    protected static ?string $navigationLabel = 'Explore';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_public', true)
            ->where('user_id', '!=', Auth::id())
            ->with(['user', 'ratings']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrowseRecipes::route('/'),
            'view'  => Pages\ViewBrowseRecipe::route('/{record}'),
        ];
    }

    protected static function getTitleDescription(Recipe $record): string
    {
        return 'by '.$record->user->name.' • '.
               count($record->ingredients ?? []).' ingredients, '.
               count($record->instructions ?? []).' steps';
    }
}
