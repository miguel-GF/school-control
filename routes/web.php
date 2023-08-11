<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

// login
Route::get('/login', [ViewController::class, 'loginView'])->middleware('login')->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// ->middleware('auth')
Route::prefix('docente')->middleware('docente')->group(function () {
  Route::get('dashboard', [ViewController::class, 'docenteDashboardView'])->name('docente.dashboard');
});

Route::prefix('alumno')->middleware('alumno')->group(function () {
  Route::get('dashboard', [ViewController::class, 'alumnoDashboardView'])->name('alumno.dashboard');
});
