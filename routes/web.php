<?php

use App\Http\Controllers\CompatibilityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NethsecurityHardwareController;
use App\Http\Controllers\NethserverHardwareController;

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

Route::get('/hardware-nethsecurity', [NethsecurityHardwareController::class, 'index']);
Route::get('/hardware-nethsecurity/{id}', [NethsecurityHardwareController::class, 'show']);
Route::post('/hardware-nethsecurity', [NethsecurityHardwareController::class, 'store']);
Route::put('/hardware-nethsecurity/{id}', [NethsecurityHardwareController::class, 'update']);
Route::delete('/hardware-nethsecurity/{id}', [NethsecurityHardwareController::class, 'destroy']);
Route::post('/hardware-nethsecurity', [NethsecurityHardwareController::class, 'index'])->name('hardware-nethsecurity');

Route::get('/hardware-nethserver', [NethserverHardwareController::class, 'index']);
Route::get('/hardware-nethserver/{id}', [NethserverHardwareController::class, 'show']);
Route::post('/hardware-nethserver', [NethserverHardwareController::class, 'store']);
Route::put('/hardware-nethserver/{id}', [NethserverHardwareController::class, 'update']);
Route::delete('/hardware-nethserver/{id}', [NethserverHardwareController::class, 'destroy']);
Route::post('/hardware-nethserver', [NethserverHardwareController::class, 'index'])->name('hardware-nethserver');


    
