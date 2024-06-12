<?php

namespace App\Services\Actions;

use App\Constants;
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

  /**
   * pasarAsistencias
   *
   * @param  mixed $datos [nombre, fechaNacimiento]
   * @return void
   */
  public static function guardarCV(array $datos)
  {
    DB::transaction(function () use($datos) {
      // Insert principal
      $insert = DocenteBO::armarInsertCV($datos);
      $curriculumDocenteId = DocenteRepoAction::agregarCurriculum($insert);
      // Insert archivos
      $nombrePersona = $datos['nombre'];
      $inserts = [];
      if (!empty($datos['archivoCurriculum'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CURRICULUM,
          $datos['archivoCurriculum'],
          $datos['descripcionCurriculum'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoIne'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_INE,
          $datos['archivoIne'],
          $datos['descripcionIne'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoCurp'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CURP,
          $datos['archivoCurp'],
          $datos['descripcionCurp'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoDomicilio'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_COMPROBANTE_DOMICILIO,
          $datos['archivoDomicilio'],
          $datos['descripcionDomicilio'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoSituacionFiscal'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_COMPROBANTE_SITUACION_FISCAL,
          $datos['archivoSituacionFiscal'],
          $datos['descripcionSituacionFiscal'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoActaNacimiento'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_ACTA_NACIMIENTO,
          $datos['archivoActaNacimiento'],
          $datos['descripcionActaNacimiento'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoCedula'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CEDULA_PROFESIONAL,
          $datos['archivoCedula'],
          $datos['descripcionCedula'],
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($datos['archivoCuentaBancaria'])) {
        $inserts[] = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CUENTA_BANCARIA,
          $datos['archivoCuentaBancaria'],
          null,
          $nombrePersona,
          $curriculumDocenteId
        );
      }
      if (!empty($inserts)) {
        DocenteRepoAction::agregarCurriculumArchivo($inserts);
      }
    }, 3);
  }
}
