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

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

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
}
