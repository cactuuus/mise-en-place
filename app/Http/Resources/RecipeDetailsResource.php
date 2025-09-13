<?php

namespace App\Http\Resources;

class RecipeDetailsResource extends RecipePreviewResource
{
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'ingredients'           => $this->ingredients,
            'instructions'          => $this->instructions,
            'notes'                 => $this->notes,
            'forked_from_recipe_id' => $this->forked_from_recipe_id,
            'parent_recipe'         => $this->whenLoaded('parentRecipe'),
        ]);
    }
}
