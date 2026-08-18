<?php

namespace App\Http\Controllers;

use App\Services\Actions\AuthServiceAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

  public function loginPortalCv(Request $request)
  {
    try {
      //code...
      $request->validate([
        'usuario' => 'required',
        'password' => 'required',
      ]);

      $datos = $request->all();

      $success = AuthServiceAction::loginPortal($datos);

      return response([
        'mensaje' => 'Logeo ok!',
        'success' => $success,
        'status' => 200
      ]);
    } catch (\Throwable $th) {
      Log::error('Ocurrio error al logerse portal cv');
      Log::error($th);
      return response([
        'mensaje' => $th->getMessage(),
        'success' => false,
        'status' => 300
      ]);
    }
  }
}
