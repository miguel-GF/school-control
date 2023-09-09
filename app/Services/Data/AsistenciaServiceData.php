<?php

namespace App\Services\Data;

use App\Repos\Data\AsistenciaRepoData;

class AsistenciaServiceData
{
  /**
   * listarCalificaciones
   *
   * @param  mixed $filtros [materia?, periodo?, licenciatura?, semestre?, grupo?, fecha?]
   * @return array
   */
  public static function listarAsistencias($filtros)
  {
    $calificaciones = AsistenciaRepoData::listarAsistencias($filtros);
    return $calificaciones;
  }
}
