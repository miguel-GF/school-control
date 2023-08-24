<?php

namespace App\Services\Data;

use App\Repos\Data\CargaAcademicaRepoData;
use App\Repos\Data\DocenteRepoData;
use stdClass;

class DocenteServiceData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf, periodo?]
   * @return stdClass
   */
  public static function obtenerDataCargasAcademicasPorId(array $datos)
  {
    $res = new stdClass();
    $res->periodos = CargaAcademicaRepoData::obtenerPeriodosCargasAcademicas();
    $res->cargasAcademicas = DocenteRepoData::obtenerCargasAcademicasPorId($datos);
    return $res;
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
