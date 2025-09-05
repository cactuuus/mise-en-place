<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * @method hasMedia(string $collection)
 * @method getFirstMedia(string $collection)
 */
trait HasCachedMediaUrls
{
    private array $mediaUrlsCache = [];

    public function getCachedMediaUrls(string $collection, array $sizes): array
    {
        $cacheKey = "{$collection}_urls";

        if (isset($this->mediaUrlsCache[$cacheKey])) {
            return $this->mediaUrlsCache[$cacheKey];
        }

        if ( ! $this->hasMedia($collection)) {
            $urls = array_fill_keys(array_keys($sizes), null);
        } else {
            $media = $this->getFirstMedia($collection);

            $urls = Cache::remember(
                "{$collection}_urls_{$media->id}",
                now()->addMinutes(30),
                fn() => collect($sizes)->mapWithKeys(fn($size, $name)
                    => [$name => $media->getTemporaryUrl(now()->addHour(), $name)],
                )->toArray(),
            );
        }

        return $this->mediaUrlsCache[$cacheKey] = $urls;
    }
}
