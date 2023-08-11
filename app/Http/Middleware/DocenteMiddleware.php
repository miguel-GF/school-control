<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class DocenteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = Session();
        $user = $session->get("user", null);
        if (!is_null($user)) {
            if ($user->tipo == "docente") {
                return $next($request);
            } else if ($user->tipo == "alumno") {
                return Inertia::location(route('alumno.dashboard'));
            }
        }

        return Inertia::location(route('login'));
    }
}
