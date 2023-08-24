<?php

namespace App\Repos\Data;

use Illuminate\Support\Facades\DB;

class CargaAcademicaRepoData
{
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
