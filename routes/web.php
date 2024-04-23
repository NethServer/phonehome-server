<?php

use App\Http\Controllers\CompatibilityController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\PciController;
use Illuminate\Support\Facades\Route;

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
})->name('map');

Route::post('/', [CompatibilityController::class, 'store'])
    ->withoutMiddleware('web')
    ->middleware('api');

Route::get('/select', function () {
    return view('select');
})->name('select-hardware');

Route::get('/hardware/{installation}', [HardwareController::class, 'index'])->name('hardware');

Route::get('/hardware-pci/{installation}', [PciController::class, 'index'])->name('hardware-pci');