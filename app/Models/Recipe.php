<?php

namespace App\Models;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'ingredients',
        'instructions',
        'notes',
        'source_url',
        'image_path',
        'is_public',
        'forked_from_recipe_id',
        'prep_time',
        'cook_time',
        'serves',
        'difficulty_level',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
        'is_public' => 'boolean',
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'serves' => 'integer',
        'difficulty_level' => Difficulty::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentRecipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'forked_from_recipe_id');
    }

    public function forks(): HasMany
    {
        return $this->hasMany(Recipe::class, 'forked_from_recipe_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RecipeRating::class);
    }

    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function totalRatings(): int
    {
        return $this->ratings()->count();
    }

    public function isOriginal(): bool
    {
        return is_null($this->source_url) && is_null($this->forked_from_recipe_id);
    }

    public function isExternalBookmark(): bool
    {
        return !is_null($this->source_url);
    }

    public function isFork(): bool
    {
        return !is_null($this->forked_from_recipe_id);
    }
}
