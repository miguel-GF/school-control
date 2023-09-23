<?php

namespace App\Services\Data;

use App\Repos\Data\PeriodoRepoData;

class PeriodoServiceData
{
  /**
   * listarPeriodos
   *
   * @param  mixed $filtros []
   * @return array
   */
  public static function listarPeriodos($filtros)
  {
    return PeriodoRepoData::listarPeriodos($filtros);
  }
}
