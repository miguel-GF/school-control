<?php

namespace App\Services\Data;

use App\Repos\Data\CalificacionRepoData;

class CalificacionServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [numEstudiante, periodo?]
   * @return array
   */
  public static function obtenerPeriodosCalificacion()
  {
    return CalificacionRepoData::obtenerPeriodosCalificaciones();
  }
}
