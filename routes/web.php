<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// health check
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'database connection failed'
        ], 503);
    }
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
