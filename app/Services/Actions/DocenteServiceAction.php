<?php

namespace App\Services\Actions;

use App\Constants;
use App\OrderConstants;
use App\Repos\Actions\DocenteRepoAction;
use App\Services\BO\DocenteBO;
use App\Services\Data\AsistenciaServiceData;
use App\Utils;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
   * guardarCV
   *
   * @param  mixed $datos
   * @return void
   */
  public static function guardarCV(array $datos)
  {
    DB::transaction(function () use ($datos) {
      // Insert principal
      $insert = DocenteBO::armarInsertCV($datos);
      $curriculumDocenteId = DocenteRepoAction::agregarCurriculum($insert);
      // Insert archivos
      $nombrePersona = Utils::limpiarCaracteresEspeciales($datos['nombre']);
      $inserts = [];
      $archivos = [];
      $ruta = DocenteBO::armarRutaArchivo($curriculumDocenteId, $nombrePersona);
      if (!empty($datos['archivoCurriculum'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CURRICULUM,
          $datos['archivoCurriculum'],
          $datos['descripcionCurriculum'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoCurriculum'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoIne'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_INE,
          $datos['archivoIne'],
          $datos['descripcionIne'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoIne'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoCurp'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CURP,
          $datos['archivoCurp'],
          $datos['descripcionCurp'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoCurp'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoDomicilio'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_COMPROBANTE_DOMICILIO,
          $datos['archivoDomicilio'],
          $datos['descripcionDomicilio'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoDomicilio'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoSituacionFiscal'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_COMPROBANTE_SITUACION_FISCAL,
          $datos['archivoSituacionFiscal'],
          $datos['descripcionSituacionFiscal'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoSituacionFiscal'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoActaNacimiento'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_ACTA_NACIMIENTO,
          $datos['archivoActaNacimiento'],
          $datos['descripcionActaNacimiento'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoActaNacimiento'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoCedula'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CEDULA_PROFESIONAL,
          $datos['archivoCedula'],
          $datos['descripcionCedula'],
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoCedula'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($datos['archivoCuentaBancaria'])) {
        $insert = DocenteBO::armarInsertArchivoCV(
          Constants::TIPO_ARCHIVO_CUENTA_BANCARIA,
          $datos['archivoCuentaBancaria'],
          null,
          $nombrePersona,
          $curriculumDocenteId,
          $ruta
        );
        $inserts[] = $insert;
        $insertObj = new stdClass();
        $insertObj->archivo = $datos['archivoCuentaBancaria'];
        $insertObj->nombre = $insert['nombre'];
        $archivos[] = $insertObj;
      }
      if (!empty($inserts)) {
        DocenteRepoAction::agregarCurriculumArchivo($inserts);
        foreach ($archivos as $archivo) {
          // Validar el contenido del archivo
          if (empty($archivo->archivo) || is_string($archivo->archivo)) {
            Log::error("Contenido del archivo inválido: " . $archivo->nombre);
            Log::error(empty($archivo->archivo));
            Log::error(!is_string($archivo->archivo));
            continue;
          }

          // Limpiar el nombre del archivo
          $archivo->nombre = preg_replace('/[^A-Za-z0-9_\-.]/', '', $archivo->nombre);

          // Construir la ruta completa
          $rutaCompleta = rtrim($ruta, '/') . '/' . basename($archivo->nombre);

          // Guardar el archivo
          try {
            Storage::put($rutaCompleta, file_get_contents($archivo->archivo));
            Log::info("Archivo guardado correctamente: " . $rutaCompleta);
          } catch (Exception $e) {
            Log::error("Error al guardar el archivo: " . $e->getMessage());
          }
        }
      }
      throw new Exception("llego al final");
    }, 3);
  }
}
