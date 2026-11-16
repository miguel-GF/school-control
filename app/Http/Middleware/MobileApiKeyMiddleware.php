<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MobileApiKeyMiddleware
{
    /**
     * Valida la key pública de la app móvil enviada en X-App-Key.
     *
     * Se mantiene separada de Authorization porque Sanctum usa ese header
     * para el token del alumno. No reemplaza la autenticación: es un filtro
     * adicional para clientes que no conocen la key de la app.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('services.mobile.app_key_enforced', true)) {
            return $next($request);
        }

        $plainKey = $request->header('X-App-Key');

        if (!is_string($plainKey) || trim($plainKey) === '') {
            return $this->unauthorized('Falta la API key de la aplicación.');
        }

        $hash = hash('sha256', $plainKey);
        $apiKey = ApiKey::where('key_hash', $hash)
            ->where('status', 'Activo')
            ->first();

        if (!$apiKey) {
            return $this->unauthorized('API key de la aplicación inválida.');
        }

        $request->attributes->set('mobile_flavor', $apiKey->flavor);

        return $next($request);
    }

    private function unauthorized(string $message)
    {
        return Response::json([
            'code' => 'invalid_app_key',
            'message' => $message,
        ], 401);
    }
}
