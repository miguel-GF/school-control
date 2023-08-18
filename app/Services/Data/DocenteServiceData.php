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

  /**
   * obtenerAlumnosPorCargaAcademica
   *
   * @param  mixed $datos [idProf, claveMateria]
   * @return array
   */
  public static function obtenerAlumnosPorCargaAcademica(array $datos)
  {
    $alumnos = DocenteRepoData::obtenerAlumnosPorCargaAcademica($datos);
    foreach ($alumnos as $alumno) {
      $alumno->asistencia = false;
    }
    return $alumnos;
  }
}
