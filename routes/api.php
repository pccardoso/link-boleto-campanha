<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryPlateController;
use App\Http\Controllers\AuthorizedPlatesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/history-plate', [HistoryPlateController::class, 'store']);
Route::get('/history-plate', [HistoryPlateController::class, 'index']);
Route::get('/authorized-with-history', [AuthorizedPlatesController::class, 'history']);