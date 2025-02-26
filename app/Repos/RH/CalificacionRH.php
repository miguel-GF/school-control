<?php

namespace App\Repos\RH;

use App\OrderConstants;

class CalificacionRH
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
    if (!empty($filtros['claveMateria'])) {
      $query->where('c.cvemat', $filtros['claveMateria']);
    }

    if (!empty($filtros['periodo'])) {
      $query->where('c.periodo', $filtros['periodo']);
    }

    if (!empty($filtros['licenciatura'])) {
      $query->where('c.licenciatura', $filtros['licenciatura']);
    }

    if (!empty($filtros['semestre'])) {
      $query->where('c.semestre', $filtros['semestre']);
    }

    if (!empty($filtros['grupo'])) {
      $query->where('c.grupo', $filtros['grupo']);
    }

    if (!empty($filtros['status'])) {
      $query->whereRaw("LOWER(c.status) = ?", [strtolower($filtros['status'])]);
    }

    if (!empty($filtros['ordenar'])) {
      switch ($filtros['ordenar']) {
        case OrderConstants::NOMBRE_ASC:
          $query->orderBy('c.alumno');
          break;
        case OrderConstants::NOMBRE_DESC:
          $query->orderByDesc('c.alumno');
          break;
        default:
          $query->orderBy('c.alumno');
          break;
      }
    }
  }

  /**
   * obtenerFiltrosConfiguracionesCapturaCalificaciones
   *
   * @param  mixed $query
   * @param  mixed $filtros
   * @return void
   */
  public static function obtenerFiltrosConfiguracionesCapturaCalificaciones(&$query, array $filtros)
  {
    if (!empty($filtros['periodo'])) {
      $query->where('ccc.periodo', $filtros['periodo']);
    }

    if (!empty($filtros['ordenar'])) {
      switch ($filtros['ordenar']) {
        case OrderConstants::NOMBRE_ASC:
          $query->orderBy('ccc.periodo');
          break;
        case OrderConstants::NOMBRE_DESC:
          $query->orderByDesc('ccc.periodo');
          break;
        default:
          $query->orderBy('ccc.id');
          break;
      }
    } else {
      $query->orderBy('ccc.id');
    }
  }
}
