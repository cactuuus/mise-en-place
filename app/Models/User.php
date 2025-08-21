<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia;

    private static array $AVATAR_SIZES = [
        'small' => 100,
        'large' => 500,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    protected $appends = [
        'avatar_urls',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        foreach (self::$AVATAR_SIZES as $name => $size) {
            $this
                ->addMediaConversion($name)
                ->fit(Fit::Max, $size, $size)
                ->format('webp')
                ->optimize()
                ->quality(90)
                ->performOnCollections('avatar')
                ->queued();
        }
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RecipeRating::class);
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function followers(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function follow(User $user): void
    {
        if ( ! $this->isFollowing($user) && $this->id !== $user->id) {
            $this->following()->attach($user);
        }
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function following(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user);
    }

    public function publicRecipes(): HasMany
    {
        return $this->recipes()->where('is_public', true);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function followingCount(): int
    {
        return $this->following()->count();
    }

    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // for now we only have a single panel, accessible for all users
        return true;
    }

    public function getAvatarUrlsAttribute(): array
    {
        if ( ! $this->hasMedia('avatar')) {
            return array_fill_keys(array_keys(self::$AVATAR_SIZES), null);
        }

        $media = $this->getFirstMedia('avatar');
        $urls  = [];

        foreach (self::$AVATAR_SIZES as $sizeName => $sizeValue) {
            $urls[$sizeName] = Cache::remember(
                "user_avatar_{$sizeName}_{$media->id}",
                now()->addMinutes(30),
                fn() => $media->getTemporaryUrl(now()->addHour(), $sizeName),
            );
        }

        return $urls;
    }
}
