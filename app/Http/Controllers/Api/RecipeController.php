<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeDetailsResource;
use App\Http\Resources\RecipePreviewCollection;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;

class RecipeController extends Controller
{
    /**
     * Return a paginated list of public recipes excluding the user's (if present) own recipes.
     */
    public function index(): RecipePreviewCollection
    {
        $recipes = Recipe::with('media')
            ->withRatings()
            ->excludeUser(auth('sanctum')->id())
            ->publicOnly()
            ->with(['user:id,name', 'tags:id,name'])
            ->latest('recipes.created_at')
            ->simplePaginate(20);

        return new RecipePreviewCollection($recipes);
    }

    /**
     * Return a paginated list of the authenticated user's own recipes.
     */
    public function mine(): RecipePreviewCollection
    {
        $recipes = Recipe::with('media')
            ->withRatings()
            ->forUser(auth()->id())
            ->with(['tags:id,name'])
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

        $recipe->load([
            'media',
            'user:id,name',
            'tags:id,name',
            'ratings.user:id,name',
            'parentRecipe:id,title,user_id',
            'parentRecipe.user:id,name',
        ]);

        return new RecipeDetailsResource($recipe);
    }

//    public function store(Request $request): RecipeDetailsResource
//    {
//        $validated = $this->validateRecipeData($request);
//
//        $recipe = Recipe::create([
//            ...$validated,
//            'user_id' => auth()->id(),
//        ]);
//
//        if (isset($validated['tags'])) {
//            $recipe->attachTags($validated['tags']);
//        }
//
//        return new RecipeDetailsResource($recipe->load(['user', 'tags']));
//    }
//
//    private function validateRecipeData(Request $request, bool $isRequired = true): array
//    {
//        $rules = [
//            'title'            => ($isRequired ? 'required|' : '').'string|max:255',
//            'ingredients'      => ($isRequired ? 'required|' : '').'array',
//            'instructions'     => ($isRequired ? 'required|' : '').'array',
//            'notes'            => 'nullable|string',
//            'source_url'       => 'nullable|url',
//            'is_public'        => 'boolean',
//            'prep_time'        => 'nullable|integer|min:0',
//            'cook_time'        => 'nullable|integer|min:0',
//            'serves'           => 'nullable|integer|min:1',
//            'difficulty_level' => 'nullable|integer',
//            'tags'             => 'nullable|array',
//        ];
//
//        return $request->validate($rules);
//    }
//
//    public function update(Request $request, Recipe $recipe): RecipeDetailsResource|JsonResponse
//    {
//        if ($recipe->user_id !== auth()->id()) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }
//
//        $validated = $this->validateRecipeData($request, false);
//        $recipe->update($validated);
//
//        if (isset($validated['tags'])) {
//            $recipe->syncTags($validated['tags']);
//        }
//
//        return new RecipeDetailsResource($recipe->load(['user', 'tags']));
//    }
//
//    public function destroy(Recipe $recipe): JsonResponse
//    {
//        if ($recipe->user_id !== auth()->id()) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }
//
//        $recipe->delete();
//
//        return response()->json(null, 'Recipe deleted successfully');
//    }
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
