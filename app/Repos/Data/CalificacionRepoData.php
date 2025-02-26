<?php

namespace App\Repos\Data;

use App\Repos\RH\CalificacionRH;
use Illuminate\Support\Facades\DB;

class CalificacionRepoData
{
  /**
   * obtenerPeriodosCalificaciones
   *
   * @return array
   */
  public static function obtenerPeriodosCalificaciones()
  {
    $query = DB::table('Calificaciones as c')
      ->select(
        'c.periodo',        
      )
      ->distinct()
      ->orderBy('c.periodo');

    return $query->get()->toArray();
  }

  /**
   * listarCalificaciones
   *
   * @param  mixed $filtros
   * @return array
   */
  public static function listarCalificaciones(array $filtros)
  {
    $query = DB::table('Calificaciones as c')
      ->select(
        'c.idcalificaciones',
        'c.periodo',
        'c.licenciatura',
        'c.semestre',
        'c.grupo',
        'c.cvemat',
        'c.materia',
        'c.numestudiante',
        'c.alumno as alumno_nombre',
        'c.primerparcial',
        'c.segundoparcial',
        'c.ordinario',
        'c.extraordinario',
        'c.final',
        'c.status'
      );

    CalificacionRH::obtenerFiltrosListar($query, $filtros);

    return $query->get()->toArray();
  }

  /**
   * obtenerConfiguracionesCapturaCalificaciones
  * @param  mixed $filtros
   * @return array
   */
  public static function obtenerConfiguracionesCapturaCalificaciones($filtros)
  {
    $query = DB::table('configcapturacalificaciones as ccc')
      ->select(
        'ccc.id',
        'ccc.periodo',
        DB::raw("
          (CASE
            WHEN ccc.statusperiodo = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS statusperiodo,
          (CASE
            WHEN `1erparcial` = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS periodoParcialUno,
          (CASE
            WHEN `2oparcial` = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS periodoParcialDos,
          (CASE
            WHEN ccc.ordinario = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS periodoOrdinario,
          (CASE
            WHEN ccc.extraordinario = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS periodoExtraordinario,
          (CASE
            WHEN ccc.final = 1 THEN 'ACTIVO'
            ELSE 'INACTIVO'
          END) AS periodoFinal
        ")
      );

      CalificacionRH::obtenerFiltrosConfiguracionesCapturaCalificaciones($query, $filtros);

    return $query->get()->toArray();
  }
}
