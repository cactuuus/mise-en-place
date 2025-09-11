<?php

namespace App\Enums;

enum TagType: string
{
    case RecipeCuisine = 'recipe_cuisine';
    case RecipeCategory = 'recipe_category';
    case RecipeKeyword = 'recipe_keyword';
    case RecipeDiet = 'recipe_diet';
}
