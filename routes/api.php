<?php

use App\Http\Controllers\DataNodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Внимание: префикс /api уже добавлен системой
Route::prefix('nodes')->group(function () {
    Route::get('/roots', [DataNodeController::class, 'roots']);
    Route::get('/{id}/children', [DataNodeController::class, 'children']);
    Route::post('/', [DataNodeController::class, 'store']);
    Route::put('/{id}', [DataNodeController::class, 'update']);
    Route::delete('/{id}', [DataNodeController::class, 'destroy']);
});
