<?php

use App\Http\Controllers\CompatibilityController;
use App\Http\Controllers\HardwareController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SelectController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/', [CompatibilityController::class, 'store'])
    ->withoutMiddleware('web')
    ->middleware('api');

Route::get('/select', function() {
    return view('select');
});

Route::post('/hardware', [SelectController::class, 'selectHardware'])->name('hardware');

Route::get('/hardware', [HardwareController::class, 'index'])->name('hardware');