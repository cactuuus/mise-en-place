<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Traits\HasCachedMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Recipe extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTags, HasCachedMediaUrls;

    private static array $IMAGE_SIZES = [
        'small'  => 150,
        'medium' => 300,
        'large'  => 800,
    ];

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
        'total_time',
        'recipe_yield',
        'difficulty_level',
    ];

    protected $casts = [
        'ingredients'      => 'array',
        'instructions'     => 'array',
        'is_public'        => 'boolean',
        'prep_time'        => 'integer',
        'cook_time'        => 'integer',
        'total_time'       => 'integer',
        'difficulty_level' => Difficulty::class,
    ];

    protected $appends = [
        'image_urls',
    ];

    protected static function booted(): void
    {
        static::saving(function (Recipe $recipe) {
            $recipe->total_time = ($recipe->prep_time ?? 0) + ($recipe->cook_time ?? 0);
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('recipe-images')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                foreach (self::$IMAGE_SIZES as $name => $size) {
                    $this
                        ->addMediaConversion($name)
                        ->fit(Fit::Max, $size, $size)
                        ->format('webp')
                        ->optimize()
                        ->quality(90)
                        ->queued();
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

    public function scopeWithRatings($query)
    {
        return $query
            ->leftJoin('recipe_ratings', 'recipes.id', '=', 'recipe_ratings.recipe_id')
            ->select([
                'recipes.*',
                DB::raw('AVG(recipe_ratings.rating) as average_rating'),
                DB::raw('COUNT(recipe_ratings.rating) as total_ratings'),
            ])
            ->groupBy('recipes.id');
    }

    public function scopePublicOnly($query)
    {
        return $query->where('recipes.is_public', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('recipes.user_id', $userId);
    }

    public function scopeExcludeUser($query, ?int $userId)
    {
        if ($userId) {
            $query->where('recipes.user_id', '!=', $userId);
        }

        return $query;
    }

    public function getImageUrlsAttribute(): array
    {
        return $this->getCachedMediaUrls('recipe-images', self::$IMAGE_SIZES);
    }

    /**
     * Syncs the recipe's tags based on a structured array.
     *
     * @param  array  $tags  An associative array where keys are tag types and values are arrays of tag names.
     */
    public function syncRecipeTags(array $tags): void
    {
        foreach ($tags as $tagType => $tagNames) {
            $this->syncTagsWithType($tagNames, $tagType);
        }
    }
}
