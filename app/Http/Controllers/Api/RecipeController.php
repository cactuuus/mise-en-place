<?php

namespace App\Http\Controllers\Api;

use App\Enums\TagType;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeDetailsResource;
use App\Http\Resources\RecipePreviewCollection;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * Return a paginated list of public recipes excluding the user's (if present) own recipes.
     */
    public function index(): RecipePreviewCollection
    {
        $recipes = Recipe::withRatings()
            ->excludeUser(auth('sanctum')->id())
            ->publicOnly()
            ->with(['user:id,name', 'tags:name,type', 'media'])
            ->latest('recipes.created_at')
            ->simplePaginate(20);

        return new RecipePreviewCollection($recipes);
    }

    /**
     * Return a paginated list of the authenticated user's own recipes.
     */
    public function mine(): RecipePreviewCollection
    {
        $recipes = Recipe::withRatings()
            ->forUser(auth()->id())
            ->with(['tags:name,type', 'media'])
            ->latest('recipes.updated_at')
            ->paginate(20);

        return new RecipePreviewCollection($recipes);
    }

    /**
     * Display the specified recipe with details.
     */
    public function show(Recipe $recipe): RecipeDetailsResource|JsonResponse
    {
        if ( ! $recipe->is_public && $recipe->user_id !== auth('sanctum')->id()) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        $recipe = $recipe
            ->newQuery()
            ->withRatings()
            ->find($recipe->id);

        $recipe->load([
            'media',
            'user:id,name',
            'tags:name,type',
            'ratings.user:id,name',
            'parentRecipe:id,title,user_id',
            'parentRecipe.user:id,name',
        ]);

        return new RecipeDetailsResource($recipe);
    }

    public function store(Request $request): RecipeDetailsResource
    {
        $validated = $this->validateRecipeData($request);

        $recipe = Recipe::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('image')) {
            $recipe
                ->addMediaFromRequest('image')
                ->usingName($recipe->title)
                ->toMediaCollection('recipe-images');
        }

        if (isset($validated['tags'])) {
            $recipe->syncRecipeTags($validated['tags']);
        }

        $recipe->load([
            'media',
            'user:id,name',
            'tags:name,type',
            'ratings.user:id,name',
            'parentRecipe:id,title,user_id',
            'parentRecipe.user:id,name',
        ]);

        return new RecipeDetailsResource($recipe);
    }

    private function validateRecipeData(Request $request): array
    {
        if ($request->has('tags') && is_string($request->input('tags'))) {
            $tags = json_decode($request->input('tags'), true);
            $request->merge(['tags' => $tags]);
        }

        Log::debug('Parsed tags:', $request->input('tags', []));
        $rules = [
            'title'            => 'required|string|max:255',
            'ingredients'      => 'required|array',
            'instructions'     => 'required|array',
            'notes'            => 'nullable|string',
            'source_url'       => 'nullable|url',
            'is_public'        => 'required|boolean',
            'prep_time'        => 'nullable|integer|min:0',
            'cook_time'        => 'nullable|integer|min:0',
            'recipe_yield'     => 'nullable|string|max:50',
            'difficulty_level' => 'nullable|integer',
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['nullable', 'array'],
            'tags.*.*'         => ['nullable', 'string'],
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];

        return $request->validate($rules);
    }

    public function update(Request $request, Recipe $recipe): RecipeDetailsResource|JsonResponse
    {
        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $this->validateRecipeData($request);
        $recipe->update($validated);

        if ($request->hasFile('image')) {
            $recipe->clearMediaCollection('recipe-images');
            $recipe
                ->addMediaFromRequest('image')
                ->usingName($recipe->title)
                ->toMediaCollection('recipe-images');
        }

        if (isset($validated['tags'])) {
            $recipe->syncRecipeTags($validated['tags']);
        }

        $recipe = $recipe
            ->newQuery()
            ->withRatings()
            ->find($recipe->id);

        $recipe->load([
            'media',
            'user:id,name',
            'tags:name,type',
            'ratings.user:id,name',
            'parentRecipe:id,title,user_id',
            'parentRecipe.user:id,name',
        ]);

        return new RecipeDetailsResource($recipe->load(['user', 'tags']));
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        if ($recipe->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $recipe->delete();

        return response()->json(null, 204);
    }
//
//    public function fork(Request $request, Recipe $recipe): RecipeDetailsResource|JsonResponse
//    {
//        if ( ! $recipe->is_public) {
//            return response()->json(['message' => 'Cannot fork private recipe'], 403);
//        }
//
//        $validated = $request->validate([
//            'title' => 'nullable|string|max:255',
//        ]);
//
//        $forkedRecipe = $recipe->fork(auth()->id(), $validated['title'] ?? null);
//
//        return new RecipeDetailsResource($forkedRecipe->load(['user', 'tags']));
//    }
//
//    public function rate(Request $request, Recipe $recipe): JsonResponse
//    {
//        $validated = $request->validate([
//            'rating' => 'required|integer|min:1|max:5',
//        ]);
//
//        if ( ! $recipe->is_public && $recipe->user_id !== auth()->id()) {
//            return response()->json('Cannot rate this recipe', 403);
//        }
//
//        $rating = $recipe->rate(auth()->id(), $validated['rating']);
//
//        return response()->json([
//            'rating'         => $rating,
//            'average_rating' => $recipe->averageRating(),
//            'total_ratings'  => $recipe->totalRatings(),
//        ], 'Recipe rated successfully');
//    }
}
