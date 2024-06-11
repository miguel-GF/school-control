<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\User; // Asegúrate de importar tu modelo User u otro modelo relacionado si es diferente
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiKeyMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next)
  {
    // Extraer el token del encabezado Authorization
    $token = $request->bearerToken();

    if (!$token) {
      return Response::json([
        'error' => 'Token inválido'
      ], 401); // Unauthorized
    }

    if ($token) {
      // Buscar el token en la base de datos
      $apiKey = ApiKey::where('key', md5($token))->where('status', 'Activo') ->first();

      if (!$apiKey) {
        return Response::json([
          'error' => 'No autorizado'
        ], 401); // Unauthorized
      }
    }

    // Retornar respuesta de error si no se encuentra el token o el usuario no es válido
    return $next($request);
  }
}
