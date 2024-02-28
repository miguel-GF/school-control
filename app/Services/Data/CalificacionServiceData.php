<?php

namespace App\Services\Data;

use App\Repos\Data\CalificacionRepoData;

class CalificacionServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos []
   * @return array
   */
  public static function obtenerPeriodosCalificacion()
  {
    return CalificacionRepoData::obtenerPeriodosCalificaciones();
  }

  /**
   * listarCalificaciones
   *
   * @param  mixed $filtros [claveMateria?, periodo?, licenciatura?, semestre?, grupo?, status?]
   * @return array
   */
  public static function listarCalificaciones($filtros)
  {
    return CalificacionRepoData::listarCalificaciones($filtros);
  }
}
