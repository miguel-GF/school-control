<?php

// use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocenteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('api.key')->group(function () {
  // Route::prefix('auth')->group(function () {
  //   Route::post('/login', [AuthController::class, 'loginMovil']);
  // });
  Route::prefix('docente')->group(function () {
    Route::post('/cv', [DocenteController::class, 'guardarCV']);
    Route::get('/cvs', [DocenteController::class, 'listarCVS']);
    Route::get('/cv/{id}', [DocenteController::class, 'detalleCV']);
    Route::post('/descargar/documento', [DocenteController::class, 'descargarDocumento']);
  });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
