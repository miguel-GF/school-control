<?php

namespace App\Services\Actions;

use App\Repos\Actions\DocenteRepoAction;
use App\Services\BO\DocenteBO;
use App\Utils;
use Exception;
use Illuminate\Support\Facades\DB;

class DocenteServiceAction
{
  /**
   * loginDocente
   *
   * @param  mixed $datos [correo, password]
   * @return void
   */
  public static function pasarAsistencias(array $datos)
  {
    try {
      DB::beginTransaction();

      $user = Utils::getUser();
      $datos['idProf'] = $user->idusuarios;
      $alumnos = json_decode($datos['alumnos']);
      $arrayInsert = [];
      foreach ($alumnos as $alumno) {
        $insert = DocenteBO::armarInsertAsistencia($datos, $alumno);
        array_push($arrayInsert, $insert);
      }
      DocenteRepoAction::agregar($arrayInsert);

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }
}
