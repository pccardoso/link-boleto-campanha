<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryPlateController;
use App\Http\Controllers\AuthorizedPlatesController;
use App\Http\Controllers\SGAController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/history-plate', [HistoryPlateController::class, 'store']);
Route::get('/history-plate', [HistoryPlateController::class, 'index']);
Route::get('/authorized-with-history', [AuthorizedPlatesController::class, 'history']);
Route::get('/get-plate/{plate}/{state}', [SGAController::class, 'getPlate']);