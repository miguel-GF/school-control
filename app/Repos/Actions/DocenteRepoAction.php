<?php

namespace App\Repos\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DocenteRepoAction
{
  /**
   * agregar nuevo registro en Asistencias
   *
   * @param  mixed $datos [idProf]
   * @return void
   */
  public static function agregar(array $datos)
  {
    try {
      DB::table('Asistencias')
        ->insert($datos);
    } catch (QueryException $th) {
      throw $th;
    }
  }

  /**
   * Método que ejecuta un update a calificaciones por id principal
   *
   * @param  mixed $update
   * @param  mixed $idCalificacion
   * @return void
   */
  public static function actualizarCalificacion(array $update, $idCalificacion)
  {
    try {
      DB::table('calificaciones')
        ->where('idcalificaciones', $idCalificacion)
        ->update($update);
    } catch (QueryException $th) {
      throw $th;
    }
  }
}
