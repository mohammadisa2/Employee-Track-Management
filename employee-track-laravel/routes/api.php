<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeLogController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('log')->group(function () {
    Route::post('/', [EmployeeLogController::class, 'store']);
    Route::get('/', [EmployeeLogController::class, 'index']);
    Route::get('/statistics', [EmployeeLogController::class, 'statistics']);
});
