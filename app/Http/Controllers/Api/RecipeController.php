<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $recipes = Recipe::with(['user:id,name', 'tags:id,name'])
            ->leftJoin('recipe_ratings', 'recipes.id', '=', 'recipe_ratings.recipe_id')
            ->where('recipes.is_public', true)
            ->select([
                'recipes.id', 'recipes.title', 'recipes.user_id',
                'recipes.prep_time', 'recipes.cook_time', 'recipes.total_time',
                'recipes.serves', 'recipes.difficulty_level', 'recipes.created_at',
                DB::raw('AVG(recipe_ratings.rating) as average_rating'),
                DB::raw('COUNT(recipe_ratings.rating) as total_ratings'),
            ])
            ->groupBy([
                'recipes.id', 'recipes.title', 'recipes.user_id',
                'recipes.prep_time', 'recipes.cook_time', 'recipes.total_time',
                'recipes.serves', 'recipes.difficulty_level', 'recipes.created_at',
            ])
            ->latest('recipes.created_at')
            ->paginate(20);

        return response()->json($recipes);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        if ( ! $recipe->is_public && $recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        $recipe->load([
            'user:id,name',
            'tags:id,name',
            'ratings.user',
            'parentRecipe:id,title,user_id',
            'parentRecipe.user:id,name',
        ]);

        return response()->json([
            ...$recipe->toArray(),
            'average_rating' => $recipe->averageRating(),
            'total_ratings'  => $recipe->totalRatings(),
            'user_rating'    => auth()->check() ? $recipe->getUserRating(auth()->id()) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'ingredients'      => 'required|array',
            'instructions'     => 'required|array',
            'notes'            => 'nullable|string',
            'source_url'       => 'nullable|url',
            'is_public'        => 'boolean',
            'prep_time'        => 'nullable|integer|min:0',
            'cook_time'        => 'nullable|integer|min:0',
            'serves'           => 'nullable|integer|min:1',
            'difficulty_level' => 'nullable|string|in:easy,medium,hard',
            'tags'             => 'nullable|array',
        ]);

        $recipe = Recipe::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        if (isset($validated['tags'])) {
            $recipe->attachTags($validated['tags']);
        }

        return response()->json($recipe->load(['user', 'tags']), 201);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'            => 'string|max:255',
            'ingredients'      => 'array',
            'instructions'     => 'array',
            'notes'            => 'nullable|string',
            'source_url'       => 'nullable|url',
            'is_public'        => 'boolean',
            'prep_time'        => 'nullable|integer|min:0',
            'cook_time'        => 'nullable|integer|min:0',
            'serves'           => 'nullable|integer|min:1',
            'difficulty_level' => 'nullable|string|in:easy,medium,hard',
            'tags'             => 'nullable|array',
        ]);

        $recipe->update($validated);

        if (isset($validated['tags'])) {
            $recipe->syncTags($validated['tags']);
        }

        return response()->json($recipe->load(['user', 'tags']));
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully']);
    }

    public function fork(Request $request, Recipe $recipe): JsonResponse
    {
        if ( ! $recipe->is_public) {
            return response()->json(['message' => 'Cannot fork private recipe'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $forkedRecipe = $recipe->fork(auth()->id(), $validated['title'] ?? null);

        return response()->json($forkedRecipe->load(['user', 'tags']), 201);
    }

    public function rate(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ( ! $recipe->is_public && $recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Cannot rate this recipe'], 403);
        }

        $rating = $recipe->rate(auth()->id(), $validated['rating']);

        return response()->json([
            'rating'         => $rating,
            'average_rating' => $recipe->averageRating(),
            'total_ratings'  => $recipe->totalRatings(),
        ]);
    }

    public function mine(Request $request): JsonResponse
    {
        $recipes = Recipe::with(['tags:id,name'])
            ->leftJoin('recipe_ratings', 'recipes.id', '=', 'recipe_ratings.recipe_id')
            ->where('recipes.user_id', auth()->id())
            ->select([
                'recipes.id', 'recipes.title', 'recipes.user_id',
                'recipes.prep_time', 'recipes.cook_time', 'recipes.total_time',
                'recipes.serves', 'recipes.difficulty_level', 'recipes.is_public',
                'recipes.created_at', 'recipes.updated_at',
                DB::raw('AVG(recipe_ratings.rating) as average_rating'),
                DB::raw('COUNT(recipe_ratings.rating) as total_ratings'),
            ])
            ->groupBy([
                'recipes.id', 'recipes.title', 'recipes.user_id',
                'recipes.prep_time', 'recipes.cook_time', 'recipes.total_time',
                'recipes.serves', 'recipes.difficulty_level', 'recipes.is_public',
                'recipes.created_at', 'recipes.updated_at',
            ])
            ->latest('recipes.updated_at')
            ->paginate(50);

        return response()->json($recipes);
    }
}
