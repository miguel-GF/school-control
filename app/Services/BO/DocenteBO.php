<?php

namespace App\Services\BO;

use stdClass;

class DocenteBO
{
  /**
   * armarInsertAsistencia
   *
   * @param  mixed $datos
   * @param  mixed $alumno
   * @return array
   */
  public static function armarInsertAsistencia(array $datos, stdClass $alumno): array
  {
    $insert = [];
    $insert['fecha'] = $datos['fecha'];
    $insert['plan'] = 'BUAP';
    $insert['licenciatura'] = $datos['licenciatura'];
    $insert['sem'] = $datos['semestre'];
    $insert['grupo'] = $datos['grupo'];
    $insert['materia'] = $datos['materia'];
    $insert['cvedoc'] = $datos['idProf'];
    $insert['numestudiante'] = $alumno->numestudiante;
    $insert['nombre'] = $alumno->alumno_nombre;
    $insert['asistencia'] = $alumno->asistencia ? 1 : 0;
    $insert['periodo'] = $datos['periodo'];

    return $insert;
  }
}
