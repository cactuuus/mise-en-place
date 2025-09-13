<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TagHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $types = $request->query('types');

        $query = Tag::query();
        if ($types && is_string($types)) {
            $types = explode(',', $types);
            $query->whereIn('type', $types);
        }

        $tags = $query->get();

        return response()->json(TagHelper::groupTagsByType($tags, $types));
    }
}
