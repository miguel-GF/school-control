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
        'as.numestudiante',
        'as.nombre',
        DB::raw("SUM(as.asistencia) as cantidad_asistencias")
      )
      ->where('as.fecha', '>=', $datos['fechaInicio'])
      ->where('as.fecha', '<=', $datos['fechaFin'])
      ->where('as.cvedoc', $datos['idProf'])
      ->where('as.sem', $datos['semestre'])
      ->where('as.grupo', $datos['grupo'])
      ->where('as.materia', $datos['materia'])
      ->where('as.periodo', $datos['periodo'])
      ->where('as.licenciatura', $datos['licenciatura'])
      ->groupBy('as.numestudiante', 'as.nombre')
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
