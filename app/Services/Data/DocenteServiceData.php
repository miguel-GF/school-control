<?php

namespace App\Services\Data;

use App\Repos\Data\CargaAcademicaRepoData;
use App\Repos\Data\DocenteRepoData;
use stdClass;

class DocenteServiceData
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
    $res->cargasAcademicas = DocenteRepoData::obtenerCargasAcademicasPorIdDocente($datos);
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

  /**
   * obtenerAlumnosParaCalificacionPorMateria
   *
   * @param  mixed $datos [semestre, grupo, licenciatura, claveMateria]
   * @return array
   */
  public static function obtenerAlumnosParaCalificacionPorMateria(array $datos)
  {
    $alumnos = DocenteRepoData::obtenerAlumnosParaCalificacionPorMateria($datos);
    return $alumnos;
  }
}
