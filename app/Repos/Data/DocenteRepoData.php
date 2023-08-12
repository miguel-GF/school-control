<?php

namespace App\Repos\Data;

use App\Constants;
use Illuminate\Support\Facades\DB;

class DocenteRepoData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf]
   * @return array
   */
  public static function obtenerCargasAcademicasPorId(array $datos)
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.idprof',
        'ca.clavemat',
        'ca.licenciatura',
        'ca.materia',
        'ca.semestre',
        'ca.grupo',
        'ca.lun',
        'ca.mar',
        'ca.mie',
        'ca.jue',
        'ca.vie',
        'ca.sab'
      )
      ->where('ca.idprof', $datos['idProf'])
      ->whereRaw("LOWER(ca.status) = ?", [strtolower(Constants::ACTIVO_STATUS)])
      ->orderBy('ca.licenciatura')
      ->orderBy('ca.materia')
      ;
      // ->orderBy('ca.materia');

    return $query->get()->toArray();
  }
}
