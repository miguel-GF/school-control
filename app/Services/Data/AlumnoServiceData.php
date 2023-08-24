<?php

namespace App\Services\Data;

use App\Repos\Data\AlumnoRepoData;
use App\Repos\Data\CalificacionRepoData;
use stdClass;

class AlumnoServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [numEstudiante, periodo?]
   * @return stdClass
   */
  public static function obteneDataCalificaciones(array $datos)
  {
    $res = new stdClass;
    $res->periodos = CalificacionRepoData::obtenerPeriodosCalificaciones();
    $res->calificaciones = AlumnoRepoData::obtenerCalificacionesPorId($datos);
    return $res;
  }
}
