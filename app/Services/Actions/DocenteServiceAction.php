<?php

namespace App\Services\Actions;

use App\OrderConstants;
use App\Repos\Actions\DocenteRepoAction;
use App\Services\BO\DocenteBO;
use App\Services\Data\AsistenciaServiceData;
use App\Utils;
use Illuminate\Support\Facades\DB;
use stdClass;

class DocenteServiceAction
{
  /**
   * pasarAsistencias
   *
   * @param  mixed $datos [idProf, alumnos]
   * @return stdClass
   */
  public static function pasarAsistencias(array $datos)
  {
    try {
      DB::beginTransaction();

      $asistencias = AsistenciaServiceData::listarAsistencias([
        'licenciatura' => $datos['licenciatura'],
        'periodo' => $datos['periodo'],
        'semestre' => $datos['semestre'],
        'grupo' => $datos['grupo'],
        'materia' => $datos['materia'],
        'ordenar' => OrderConstants::NOMBRE_ASC,
        'fecha' => $datos['fecha'],
      ]);

      $res = new stdClass();
      $res->status = 200;
      $res->mensaje = "Asistencias agredadas correctamente";
      if (!empty($asistencias)) {
        $res->status = 201;
        $res->mensaje = "Ya existen asistencias para el día de {$datos['fecha']}";
        $res->asistencias = $asistencias;
        return $res;
      }

      $user = Utils::getUser();
      $datos['idProf'] = $user->claveusuario;
      $alumnos = json_decode($datos['alumnos']);
      $arrayInsert = [];
      foreach ($alumnos as $alumno) {
        $insert = DocenteBO::armarInsertAsistencia($datos, $alumno);
        array_push($arrayInsert, $insert);
      }
      DocenteRepoAction::agregar($arrayInsert);

      DB::commit();

      return $res;
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }

  /**
   * actualizarAsistencias
   *
   * @param  mixed $datos [idProf, alumnos]
   */
  public static function actualizarAsistencias(array $datos)
  {
    try {
      DB::beginTransaction();

      $user = Utils::getUser();
      $datos['idProf'] = $user->claveusuario;
      $alumnos = json_decode($datos['alumnos']);
      $arrayInsert = [];
      foreach ($alumnos as $alumno) {
        if (isset($alumno->idAsistencias)) {
          $update = DocenteBO::armarUpdateAsistencia($alumno);
          DocenteRepoAction::actualizar($update, $alumno->idAsistencias);
        } else {
          $insert = DocenteBO::armarInsertAsistencia($datos, $alumno);
          array_push($arrayInsert, $insert);
        }
      }
      DocenteRepoAction::agregar($arrayInsert);

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }

  

  /**
   * guardarCalificaciones
   *
   * @param  mixed $datos [califiaciones]
   * @return void
   */
  public static function guardarCalificaciones(array $datos)
  {
    try {
      DB::beginTransaction();

      $calificaciones = json_decode($datos['calificaciones']);
      foreach ($calificaciones as $calificacion) {
        $update = DocenteBO::armarUpdateCalificacion($datos, $calificacion);
        DocenteRepoAction::actualizarCalificacion($update, $calificacion->idcalificaciones);
      }

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }
}
