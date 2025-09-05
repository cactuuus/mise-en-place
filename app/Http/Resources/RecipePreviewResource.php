<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecipePreviewResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'user'             => $this->whenLoaded('user'),
            'tags'             => $this->whenLoaded('tags'),
            'prep_time'        => $this->prep_time,
            'cook_time'        => $this->cook_time,
            'total_time'       => $this->total_time,
            'serves'           => $this->serves,
            'difficulty_level' => $this->difficulty_level,
            'source_url'       => $this->source_url,
            'is_public'        => $this->is_public,
            'average_rating'   => $this->average_rating,
            'total_ratings'    => $this->total_ratings,
            'image_urls'       => $this->image_urls ?? null,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
