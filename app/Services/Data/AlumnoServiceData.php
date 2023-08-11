<?php

namespace App\Services\Data;

use App\Repos\Data\AlumnoRepoData;

class AlumnoServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [numEstudiante]
   * @return array
   */
  public static function obtenerCalificacionesPorId(array $datos)
  {
    return AlumnoRepoData::obtenerCalificacionesPorId($datos);
  }
}
