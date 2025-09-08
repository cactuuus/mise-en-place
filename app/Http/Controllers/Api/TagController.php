<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        return response()->json(Tag::withType($type)->get(['id', 'name']));
    }
}
