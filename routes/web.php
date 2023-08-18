<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

// login
Route::get('/login', [ViewController::class, 'loginView'])->middleware('login')->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// ->middleware('auth')
Route::prefix('docente')->middleware('docente')->group(function () {
  Route::get('dashboard', [ViewController::class, 'docenteDashboardView'])->name('docente.dashboard');
  Route::get('cargasAcademicas', [DocenteController::class, 'docenteCargasAcademicasView'])->name('docente.cargas.academicas');
  Route::get('pasarAsistencias/{claveMateria}', [DocenteController::class, 'docentePasarAsistenciasCargasAcademicasView'])->name('docente.pasar.asistencias');
  Route::post('pasarAsistencias', [DocenteController::class, 'pasarAsistencias']);
});

Route::prefix('alumno')->middleware('alumno')->group(function () {
  Route::get('dashboard', [ViewController::class, 'alumnoDashboardView'])->name('alumno.dashboard');
  Route::get('calificaciones', [AlumnoController::class, 'alumnoCalificacionesView'])->name('alumno.calificaciones');
});

// Ruta de fallback para redireccionar a la página de inicio de sesión
Route::get('/{any}', [ViewController::class, 'loginView'])->where('any', '.*')->middleware('login');