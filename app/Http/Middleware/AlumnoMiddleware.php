<?php

namespace App\Http\Middleware;

use App\Utils;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AlumnoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Utils::getUser();
        if (!is_null($user)) {
            if ($user->tipo == "alumno") {
                return $next($request);
            } else if ($user->tipo == "docente") {
                return Inertia::location(route('docente.dashboard'));
            }
        }

        return Inertia::location(route('login'));
    }
}
