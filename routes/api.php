<?php

use App\Http\Controllers\CompatibilityController;
use App\Http\Controllers\InstallationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('compatibility', [CompatibilityController::class, 'index']);
Route::apiResource('installation', InstallationController::class)->only('store');
