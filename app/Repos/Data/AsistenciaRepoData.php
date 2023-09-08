<?php

namespace App\Repos\Data;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    Log::info($query->toSql());
    Log::info($query->getBindings());

    return $query->get()->toArray();
  }
}
