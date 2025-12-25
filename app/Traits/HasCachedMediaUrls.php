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
            $baseUrl = $media->getTemporaryUrl(now()->addDays(6));

            $urls = Cache::remember(
                "{$collection}_urls_{$media->id}",
                now()->addDays(6),
                fn() => collect($sizes)->mapWithKeys(fn($size, $name)
                    => [$name => $this->buildCloudflareUrl($baseUrl, $size)],
                )->toArray(),
            );
        }

        return $this->mediaUrlsCache[$cacheKey] = $urls;
    }

    private function buildCloudflareUrl(string $baseUrl, int $width): string
    {
        $domain = config('app.url');
        return "{$domain}/cdn-cgi/image/width={$width}/{$baseUrl}";
    }
}
