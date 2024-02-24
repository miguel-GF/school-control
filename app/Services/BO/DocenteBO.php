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

  /**
   * armarUpdateAsistencia
   *
   * @param  mixed $alumno
   * @return array
   */
  public static function armarUpdateAsistencia(stdClass $alumno): array
  {
    $insert = [];
    $insert['asistencia'] = $alumno->asistencia ? 1 : 0;

    return $insert;
  }

  /**
   * armarUpdateCalificacion
   *
   * @param  mixed $datos
   * @param  mixed $calificacion
   * @return array
   */
  public static function armarUpdateCalificacion(array $datos, stdClass $calificacion): array
  {
    $update = [];
    $update['primerparcial'] = $calificacion->primerparcial ?: null;
    $update['segundoparcial'] = $calificacion->segundoparcial ?: null;
    $update['ordinario'] = $calificacion->ordinario ?: null;
    $update['extraordinario'] = $calificacion->extraordinario ?: null;
    $update['final'] = $calificacion->final ?: null;
    $update['fechacambio'] = !empty($datos['fecha']) ? $datos['fecha'] : null;

    return $update;
  }
}
