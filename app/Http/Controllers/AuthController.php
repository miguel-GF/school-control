<?php

namespace App\Http\Controllers;

use App\Services\Actions\AuthServiceAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required_if:tipo,docente|email',
            'numeroEstudiante' => 'required_if:tipo,alumno|numeric',
            'password' => 'required',
            'tipo' => 'required',
        ]);

        $datos = $request->all();

        if ($datos['tipo'] == 'docente') {
            $logeado = AuthServiceAction::loginDocente($datos);
            if ($logeado) {
                return Inertia::location(route('docente.dashboard'));
            }
        } else {
            $logeado = AuthServiceAction::loginAlumno($datos);
            if ($logeado) {
                return Inertia::location(route('alumno.dashboard'));
            }
        }

        return Inertia::render('Login', [
            'status' => 300,
            'error' => 'Usuario o contraseña incorrecto'
        ]);
        // return Inertia::location(route('login', [
        //     'error' => 300,
        //     'mesaje' => 'Usuario o contraseña incorrecto',
        // ]));
    }

    public function logout()
    {
        // Auth::logout();
        $session = Session();
        $session->flush();
        return redirect()->route('login');
    }
}
