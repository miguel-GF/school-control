<?php

namespace App\Repos\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DocenteRepoAction
{
  /**
   * agregar nuevo registro en Asistencias
   *
   * @param  mixed $datos [idProf]
   * @return void
   */
  public static function agregar(array $datos)
  {
    try {
      DB::table('Asistencias')
        ->insert($datos);
    } catch (QueryException $th) {
      throw $th;
    }
  }

  /**
   * Método que ejecuta un update a calificaciones por id principal
   *
   * @param  mixed $update
   * @param  mixed $idCalificacion
   * @return void
   */
  public static function actualizarCalificacion(array $update, $idCalificacion)
  {
    try {
      DB::table('Calificaciones')
        ->where('idcalificaciones', $idCalificacion)
        ->update($update);
    } catch (QueryException $th) {
      throw $th;
    }
  }

  /**
   * Actualizar registro en Asistencias
   *
   * @param  mixed $update
   * @param  mixed $idAsistencias
   * @return void
   */
  public static function actualizar(array $update, $idAsistencias)
  {
    try {
      DB::table('Asistencias')
        ->where('idAsistencias', $idAsistencias)
        ->update($update);
    } catch (QueryException $th) {
      throw $th;
    }
  }

  /**
   * agregar nuevo registro en curriculum_docentes
   *
   * @param  mixed $datos
   * @return mixed
   */
  public static function agregarCurriculum(array $datos)
  {
    try {
      return DB::table('curriculum_docentes')->insertGetId(array_merge($datos, [
        'folio' => DB::raw("(
            SELECT COALESCE(MAX(folio), 0) + 1
            FROM (SELECT folio FROM curriculum_docentes) as temp_table
        )")
      ]), 'curriculum_docente_id');
    } catch (QueryException $th) {
      throw $th;
    }
  }
  
  /**
   * agregarCurriculumArchivo
   *
   * @param  mixed $datos
   * @return void
   */
  public static function agregarCurriculumArchivo(array $datos)
  {
    try {
      DB::table('curriculum_docentes_archivos')
        ->insert($datos);
    } catch (QueryException $th) {
      throw $th;
    }
  }
}
