<?php

namespace App\Repos\Data;

use App\Constants;
use Illuminate\Support\Facades\DB;

class CalificacionRepoData
{
  /**
   * obtenerPeriodosCalificaciones
   *
   * @return array
   */
  public static function obtenerPeriodosCalificaciones()
  {
    $query = DB::table('Calificaciones as c')
      ->select(
        'c.periodo',        
      )
      ->distinct()
      ->orderBy('c.periodo');

    return $query->get()->toArray();
  }
}
