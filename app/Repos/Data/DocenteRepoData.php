<?php

namespace App\Repos\Data;

use App\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocenteRepoData
{
  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf]
   * @return array
   */
  public static function obtenerCargasAcademicasPorId(array $datos)
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.idprof',
        'ca.clavemat',
        'ca.licenciatura',
        'ca.materia',
        'ca.semestre',
        'ca.grupo',
        'ca.lun',
        'ca.mar',
        'ca.mie',
        'ca.jue',
        'ca.vie',
        'ca.sab'
      )
      ->where('ca.idprof', $datos['idProf'])
      ->whereRaw("LOWER(ca.status) = ?", [strtolower(Constants::ACTIVO_STATUS)])
      ->orderBy('ca.licenciatura')
      ->orderBy('ca.materia')
      ;

    return $query->get()->toArray();
  }

  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [idProf, claveMateria]
   * @return array
   */
  public static function obtenerAlumnosPorCargaAcademica(array $datos)
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.idprof',
        'ca.clavemat',
        'ca.licenciatura',
        'ca.semestre',
        'ca.grupo',
        'ca.materia',
        'ca.periodo',
        'c.numestudiante',
        'c.alumno as alumno_nombre'
      )
      ->join('Calificaciones as c', 'c.cvemat', 'ca.clavemat')
      ->where('ca.idprof', $datos['idProf'])
      ->where('ca.claveMat', $datos['claveMateria'])
      ->orderBy('c.alumno')
      ;

    return $query->get()->toArray();
  }
}
