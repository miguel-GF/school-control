<?php

namespace App\Services\Data;

use App\Repos\Data\DocenteRepoData;

class DocenteServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf]
   * @return array
   */
  public static function obtenerCalificacionesPorId(array $datos)
  {
    return DocenteRepoData::obtenerCargasAcademicasPorId($datos);
  }
}
