<?php

namespace App\Repos\Data;

use Illuminate\Support\Facades\DB;

class CargaAcademicaRepoData
{
  /**
   * listarCargasAcademicas
   *
   * @param  mixed $datos [idCargaAcademica?]
   * @return array
   */
  public static function listarCargasAcademicas(array $datos)
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.idcargaacademica',
        'ca.idprof',
        'ca.clavemat',
        'ca.licenciatura',
        'ca.materia',
        'ca.semestre',
        'ca.periodo',
        'ca.grupo',
        'ca.lun',
        'ca.mar',
        'ca.mie',
        'ca.jue',
        'ca.vie',
        'ca.sab'
      );

    if (!empty($datos['idCargaAcademica'])) {
      $query->where('ca.idcargaacademica', $datos['idCargaAcademica']);
    }

    return $query->get()->toArray();
  }

  /**
   * obtenerPeriodosCargasAcademicas
   *
   * @return array
   */
  public static function obtenerPeriodosCargasAcademicas()
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.periodo'
      )
      ->distinct()
      ->orderBy('ca.periodo');
      ;

    return $query->get()->toArray();
  }
}
