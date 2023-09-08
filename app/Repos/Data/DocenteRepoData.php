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
   * @param  mixed $datos [idProf, periodo?]
   * @return array
   */
  public static function obtenerCargasAcademicasPorIdDocente(array $datos)
  {
    $query = DB::table('CargaAcademica as ca')
      ->select(
        'ca.idcargaacademica',
        'ca.idprof',
        'ca.clavemat',
        'ca.licenciatura',
        'ca.materia',
        'ca.semestre',
        'ca.periodo',
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
      ->orderBy('ca.materia');

    if (!empty($datos['periodo'])) {
      $query->where('ca.periodo', $datos['periodo']);
    }

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

  /**
   * obtenerCalificacionesPorId
   *
   * @param  mixed $datos [periodo, licenciatura, semestre, grupo, idProf, claveMateria]
   * @return array
   */
  public static function obtenerAlumnosParaCalificacionPorMateria(array $datos)
  {
    $query = DB::table('calificaciones as c')
      ->select(
        'c.idcalificaciones',
        'c.periodo',
        'c.licenciatura',
        'c.semestre',
        'c.grupo',
        'c.cvedoc',
        'c.docente as docente_nombre',
        'c.cvmat',
        'c.materia',
        'c.numestudiante',
        'c.alumno as alumno_nombre',
        'c.primerparcial',
        'c.segundoparcial',
        'c.ordinario',
        'c.extraordinario',
        'c.final',
        'c.status'
      )
      ->where('a.periodo', $datos['periodo'])
      ->where('a.licenciatura', $datos['licenciatura'])
      ->where('a.semestre', $datos['semestre'])
      ->where('a.grupo', $datos['grupo'])
      ->where('c.cvedoc', $datos['idProf'])
      ->where('c.cvmat', $datos['claveMateria'])
      ->orderBy('c.alumno')
      ;

    return $query->get()->toArray();
  }
}
