<?php

namespace App\Repos\Data;

use App\Constants;
use Illuminate\Support\Facades\DB;

class AlumnoRepoData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [numEstudiante]
   * @return array
   */
  public static function obtenerCalificacionesPorId(array $datos)
  {
    $query = DB::table('Calificaciones as c')
      ->select(
        'c.numestudiante',
        'c.cvemat',
        'c.materia',
        'c.periodo',
        'c.alumno',
        'c.asistencias',
        'c.faltas',
        'c.primerparcial',
        'c.segundoparcial',
        'c.ordinario',
        'c.extraordinario',
        'c.final',
        'c.status'
      )
      ->where('c.numestudiante', $datos['numEstudiante'])
      ->whereRaw("LOWER(c.status) = ?", [strtolower(Constants::ACTIVO_STATUS)])
      ->orderBy('c.materia');

    return $query->get()->toArray();
  }
}
