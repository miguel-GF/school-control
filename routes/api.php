<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MobileGradeController;
use App\Http\Controllers\MobilePaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Home
|--------------------------------------------------------------------------
|
| Endpoint publico y generico para verificar que version esta desplegada.
|
*/
Route::get('/', fn () => response()->json([
  'name' => config('app.name').' API',
  'type' => 'api',
  'api_version' => config('app.api_version'),
  'laravel' => app()->version(),
  'php' => PHP_VERSION,
  'environment' => app()->environment(),
]));

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('api.key')->group(function () {
  // Route::prefix('auth')->group(function () {
  //   Route::post('/login', [AuthController::class, 'loginMovil']);
  // });
  Route::prefix('auth')->group(function () {
    Route::post('/login/portalcv', [AuthController::class, 'loginPortalCv']);
  });
  Route::prefix('docente')->group(function () {
    Route::post('/cv/buscar', [DocenteController::class, 'buscarPorCelular']);
    Route::post('/cv', [DocenteController::class, 'guardarCV']);
    Route::post('/cv/{id}', [DocenteController::class, 'actualizarCV']);
    Route::put('/cv/{id}', [DocenteController::class, 'actualizarCV']);
    Route::post('/cv/{id}/finalizar', [DocenteController::class, 'finalizarCV']);
    Route::get('/cvs', [DocenteController::class, 'listarCVS']);
    Route::get('/cv/{id}', [DocenteController::class, 'detalleCV']);
    Route::post('/descargar/documento', [DocenteController::class, 'descargarDocumento']);
  });
});

Route::prefix('v1/mobile')->group(function () {
  Route::get('/health', fn () => response()->json([
    'ok' => true,
    'service' => 'school-control-mobile',
    'api_version' => config('app.api_version'),
  ]));
  Route::post('/payments/stripe/webhook', [MobilePaymentController::class, 'webhook']);

  Route::middleware('mobile.app-key')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'loginAlumnoApi'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
      Route::get('/me', [AuthController::class, 'alumnoActual']);
      Route::post('/auth/logout', [AuthController::class, 'logoutAlumnoApi']);
      Route::post('/auth/verify-password', [AuthController::class, 'verificarPasswordAlumno'])->middleware('throttle:10,1');
      Route::get('/calificaciones', [MobileGradeController::class, 'index'])->middleware('throttle:60,1');
      Route::get('/payment-config', [MobilePaymentController::class, 'configuracion']);
      Route::post('/payment-methods/setup-intent', [MobilePaymentController::class, 'crearSetupIntent']);
      Route::post('/payment-methods', [MobilePaymentController::class, 'adjuntarTarjeta']);
      Route::get('/payment-methods', [MobilePaymentController::class, 'listarTarjetas']);
      Route::delete('/payment-methods/{paymentMethodId}', [MobilePaymentController::class, 'eliminarTarjeta']);
      Route::post('/payments', [MobilePaymentController::class, 'crearPago'])->middleware('throttle:20,1');
      Route::post('/payments/{paymentIntentId}/confirm', [MobilePaymentController::class, 'confirmarPago']);
      Route::get('/payments', [MobilePaymentController::class, 'listarPagos']);
    });
  });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
