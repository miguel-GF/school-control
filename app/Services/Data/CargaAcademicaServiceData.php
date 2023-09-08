<?php

namespace App\Services\Data;

use App\Repos\Data\CargaAcademicaRepoData;

class CargaAcademicaServiceData
{
  /**
   * listarCargasAcademicas
   *
   * @param  mixed $filtros [idCargaAcademica?]
   * @return array
   */
  public static function listarCargasAcademicas(array $filtros)
  {
    return CargaAcademicaRepoData::listarCargasAcademicas($filtros);
  }
}
