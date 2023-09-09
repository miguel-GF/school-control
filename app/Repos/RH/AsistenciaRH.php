<?php

namespace App\Repos\RH;

use App\OrderConstants;

class AsistenciaRH
{
  /**
   * obtenerFiltrosListar
   *
   * @param  mixed $query
   * @param  mixed $filtros
   * @return void
   */
  public static function obtenerFiltrosListar(&$query, array $filtros)
  {
    if (!empty($filtros['fecha'])) {
      $query->where('a.fecha', $filtros['fecha']);
    }

    if (!empty($filtros['materia'])) {
      $query->where('a.materia', $filtros['materia']);
    }

    if (!empty($filtros['periodo'])) {
      $query->where('a.periodo', $filtros['periodo']);
    }

    if (!empty($filtros['licenciatura'])) {
      $query->where('a.licenciatura', $filtros['licenciatura']);
    }

    if (!empty($filtros['semestre'])) {
      $query->where('a.sem', $filtros['semestre']);
    }

    if (!empty($filtros['grupo'])) {
      $query->where('a.grupo', $filtros['grupo']);
    }

    if (!empty($filtros['ordenar'])) {
      switch ($filtros['ordenar']) {
        case OrderConstants::NOMBRE_ASC:
          $query->orderBy('a.nombre');
          break;
        case OrderConstants::NOMBRE_DESC:
          $query->orderByDesc('a.nombre');
          break;
        default:
          $query->orderBy('a.nombre');
          break;
      }
    }
  }
}
