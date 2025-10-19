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
  Route::get('asistenciasCargasAcademicas', [DocenteController::class, 'docenteAsistenciasCargasAcademicasView'])->name('docente.asistencias.cargas.academicas');
  Route::get('reporteAsistencias', [DocenteController::class, 'reporteAsistenciasView'])->name('docente.reporte.asistencias');
  Route::get('pasarAsistencias/{idCargaAcademica}/{fecha}', [DocenteController::class, 'docentePasarAsistenciasCargasAcademicasView'])->name('docente.pasar.asistencias');
  Route::post('pasarAsistencias', [DocenteController::class, 'pasarAsistencias']);
  Route::post('actualizarAsistencias', [DocenteController::class, 'actualizarAsistencias']);
  Route::get('capturarCalificaciones/{idCargaAcademica}', [DocenteController::class, 'docenteCapturarCalificacionesView'])->name('docente.capturar.calificaciones');
  Route::post('guardarCalificaciones', [DocenteController::class, 'guardarCalificaciones']);
});

Route::prefix('alumno')->middleware('alumno')->group(function () {
  Route::get('dashboard', [ViewController::class, 'alumnoDashboardView'])->name('alumno.dashboard');
  Route::get('calificaciones', [AlumnoController::class, 'alumnoCalificacionesView'])->name('alumno.calificaciones');
});

// Ruta de fallback para redireccionar a la página de inicio de sesión
Route::get('/docentes/restore/curriculum/archivos/{token}', [DocenteController::class, 'recuperarCurriculumsBlobs']);
Route::get('/{any}', [ViewController::class, 'loginView'])->where('any', '.*')->middleware('login');