<?php

use App\Http\Controllers\CompatibilityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HardwareController;

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



Route::get('/hardware', [HardwareController::class, 'index']);
Route::get('/hardware/{id}', [HardwareController::class, 'show']);
Route::post('/hardware', [HardwareController::class, 'store']);
Route::put('/hardware/{id}', [HardwareController::class, 'update']);
Route::delete('/hardware/{id}', [HardwareController::class, 'destroy']);
Route::post('/search-hardware', [HardwareController::class, 'searchHardware'])->name('searchHardware');

    
