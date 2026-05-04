<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file is loaded by bootstrap/app.php (Laravel 11+ slim skeleton).
| You can put API endpoints here (typically protected by auth:sanctum).
|
*/

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
