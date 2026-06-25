<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\KurikulumController;
use App\Http\Controllers\Api\V1\NilaiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->middleware('iae.key')->group(function () {
    // Kurikulum Routes
    Route::get('/kurikulum', [KurikulumController::class, 'index']);
    Route::get('/kurikulum/{kode}', [KurikulumController::class, 'show']);

    // Nilai Routes
    Route::get('/nilai', [NilaiController::class, 'index']);
    Route::get('/nilai/{nim}', [NilaiController::class, 'show']);
    Route::post('/nilai', [NilaiController::class, 'store']);
});
