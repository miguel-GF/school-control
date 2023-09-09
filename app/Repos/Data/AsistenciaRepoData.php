<?php

namespace App\Repos\Data;

use App\Repos\RH\AsistenciaRH;
use Illuminate\Support\Facades\DB;

class AsistenciaRepoData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf, fecha, semestre, grupo, materia, periodo]
   * @return array
   */
  public static function obtenerReporteAsistencias(array $datos)
  {
    $query = DB::table('Asistencias as as')
      ->select(
        'as.idAsistencias',
        'as.fecha',
        'as.licenciatura',
        'as.sem',
        'as.grupo',
        'as.materia',
        'as.periodo',
        'as.cvedoc',
        'as.numestudiante',
        'as.nombre',
        'as.asistencia',
        DB::raw("
          CASE
            WHEN as.asistencia THEN 'Si'
            ELSE 'No'
          END as asistencia_nombre
        ")
      )
      ->where('as.fecha', $datos['fecha'])
      ->where('as.cvedoc', $datos['idProf'])
      ->where('as.sem', $datos['semestre'])
      ->where('as.grupo', $datos['grupo'])
      ->where('as.materia', $datos['materia'])
      ->where('as.periodo', $datos['periodo'])
      ->where('as.licenciatura', $datos['licenciatura'])
      ->orderBy('as.nombre');

    return $query->get()->toArray();
  }

  /**
   * listarAsistencias
   *
   * @param  mixed $filtros
   * @return array
   */
  public static function listarAsistencias(array $filtros)
  {
    $query = DB::table('Asistencias as a')
      ->select(
        'a.idAsistencias',
        'a.fecha',
        'a.plan',
        'a.licenciatura',
        'a.sem',
        'a.grupo',
        'a.materia',
        'a.numestudiante',
        'a.nombre as alumno_nombre',
        'a.asistencia',
        'a.periodo'
      );

    AsistenciaRH::obtenerFiltrosListar($query, $filtros);

    return $query->get()->toArray();
  }
}
