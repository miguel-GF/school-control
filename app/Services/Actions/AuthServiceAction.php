<?php

namespace App\Services\Actions;
use App\Constants;
use App\Models\Alumno;
use App\Models\User;

class AuthServiceAction
{    
    /**
     * loginDocente
     *
     * @param  mixed $datos [correo, password]
     * @return bool
     */
    public static function loginDocente(array $datos)
    {
      // $credentials = $request->only('correo', 'password');
      // if (Auth::attempt($credentials)) {
      //     return redirect()->route('user.dashboard');
      // }
      $user = User::select('idusuarios', 'correo', 'claveusuario', 'nombre')
        ->where('correo', $datos['correo'])
        ->where('password', $datos['password'])
        ->where('status', Constants::ACTIVO_STATUS)
        ->get()->first();
      
      if (!empty($user)) {
        $session = Session();
        $user->tipo = "docente";
        $session->put('user', $user);
        return true;
      } else {
        return false;
      }
    }

    /**
     * loginAlumno
     *
     * @param  mixed $datos [numeroEstudiante, password]
     * @return bool
     */
    public static function loginAlumno(array $datos)
    {
      $user = Alumno::select('idalumnos', 'numestudiante', 'nombre')
        ->where('numestudiante', $datos['numeroEstudiante'])
        ->where('contrasena', $datos['password'])
        ->get()->first();
      
      if (!empty($user)) {
        $session = Session();
        $user->tipo = "alumno";
        $session->put('user', $user);
        return true;
      } else {
        return false;
      }
    }
}
