<?php

namespace App\Repos\Data;

use Illuminate\Support\Facades\DB;

class PeriodoRepoData
{
  /**
   * listarPeriodos
   *
   * @param  mixed $filtros
   * @return array
   */
  public static function listarPeriodos(array $filtros)
  {
    $query = DB::table('periodos as p')
      ->select(
        'p.id',
        'p.periodo',
        'p.default'
      )
      ->orderByDesc('p.id');

    return $query->get()->toArray();
  }
}
