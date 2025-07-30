<?php

namespace App\Models;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'ingredients'      => 'array',
        'instructions'     => 'array',
        'is_public'        => 'boolean',
        'prep_time'        => 'integer',
        'cook_time'        => 'integer',
        'serves'           => 'integer',
        'difficulty_level' => Difficulty::class,
    ];

    protected static function booted(): void
    {
        static::updating(function ($recipe) {
            if ($recipe->isDirty('image_path')) {
                $originalImagePath = $recipe->getOriginal('image_path');
                if ($originalImagePath) {
                    Storage::delete($originalImagePath);
                }
            }
        });

        static::deleting(function ($recipe) {
            if ($recipe->image_path) {
                Storage::delete($recipe->image_path);
            }
        });
    }

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

    public function averageRating(): float
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RecipeRating::class);
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
        return ! is_null($this->source_url);
    }

    public function isFork(): bool
    {
        return ! is_null($this->forked_from_recipe_id);
    }

    public function fork(int $userId, ?string $customTitle = null): self
    {
        $forkedRecipe                        = $this->replicate();
        $forkedRecipe->user_id               = $userId;
        $forkedRecipe->forked_from_recipe_id = $this->id;
        $forkedRecipe->title                 = $customTitle ?? "Fork of $this->title";
        $forkedRecipe->is_public             = false; // Forked recipes start as private
        $forkedRecipe->save();

        return $forkedRecipe;
    }

    public function rate(int $userId, int $rating): RecipeRating
    {
        return $this->ratings()->updateOrCreate(
            ['user_id' => $userId],
            ['rating' => $rating],
        );
    }

    public function getUserRating(int $userId): ?int
    {
        return $this->ratings()->where('user_id', $userId)->first()?->rating;
    }
}
