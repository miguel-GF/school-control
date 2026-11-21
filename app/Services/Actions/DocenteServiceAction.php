<?php

namespace App\Services\Actions;

use App\Constants;
use App\OrderConstants;
use App\Repos\Actions\DocenteRepoAction;
use App\Repos\Data\DocenteRepoData;
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
   * Documentos que debe tener el expediente para poder enviarse a revision.
   *
   * @return array
   */
  public static function documentosRequeridosCV(): array
  {
    return [
      Constants::TIPO_ARCHIVO_CURRICULUM => 'Currículum (CV)',
      Constants::TIPO_ARCHIVO_PLANEACION_ACADEMICA => 'Planeación académica',
      Constants::TIPO_ARCHIVO_INE => 'INE',
      Constants::TIPO_ARCHIVO_CURP => 'CURP',
      Constants::TIPO_ARCHIVO_COMPROBANTE_DOMICILIO => 'Comprobante de domicilio',
      Constants::TIPO_ARCHIVO_COMPROBANTE_SITUACION_FISCAL => 'Comprobante de situación fiscal',
      Constants::TIPO_ARCHIVO_ACTA_NACIMIENTO => 'Acta de nacimiento',
      Constants::TIPO_ARCHIVO_CEDULA_PROFESIONAL => 'Cédula profesional',
      Constants::TIPO_ARCHIVO_CUENTA_BANCARIA => 'Fotografía de cuenta bancaria',
    ];
  }

  /**
   * Campos obligatorios para poder enviar el expediente a revision.
   *
   * @return array
   */
  public static function camposRequeridosCV(): array
  {
    return [
      'nombre' => 'Nombre completo',
      'fecha_nacimiento' => 'Fecha de nacimiento',
      'ciudad' => 'Ciudad de nacimiento',
      'estado' => 'Estado de nacimiento',
      'pais' => 'País de nacimiento',
      'estado_civil' => 'Estado civil',
      'genero' => 'Género',
      'correo_institucional' => 'Correo institucional',
      'numero_celular' => 'Número de celular',
      'domicilio_calle' => 'Calle del domicilio',
      'domicilio_no_exterior' => 'Número exterior',
      'domicilio_colonia' => 'Colonia',
      'domicilio_ciudad' => 'Ciudad del domicilio',
      'domicilio_estado' => 'Estado del domicilio',
      'domicilio_codigo_postal' => 'Código postal',
    ];
  }

  /**
   * Normaliza el estado del expediente para el frontend.
   *
   * @param  mixed $status
   * @return string nuevo|en_captura|completado
   */
  public static function estadoCapturaCV($status): string
  {
    $status = strtolower(trim((string) $status));

    if (in_array($status, ['completado', 'finalizado', 'cerrado'], true)) {
      return 'completado';
    }

    return 'en_captura';
  }

  /**
   * Indica si el expediente ya fue enviado a revision y por lo tanto esta bloqueado.
   *
   * @param  mixed $status
   * @return bool
   */
  public static function esCVFinalizado($status): bool
  {
    return self::estadoCapturaCV($status) === 'completado';
  }

  /**
   * Consulta un expediente por numero de celular.
   *
   * @param  string $celular
   * @return array
   */
  public static function consultarCVPorCelular(string $celular): array
  {
    $curriculum = DocenteRepoData::obtenerCVPorCelular($celular);

    if (!$curriculum) {
      return [
        'estado_captura' => 'nuevo',
        'curriculum' => null,
        'documentos' => [],
      ];
    }

    return [
      'estado_captura' => self::estadoCapturaCV($curriculum->status ?? null),
      'curriculum' => $curriculum,
      'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($curriculum->curriculum_docente_id),
    ];
  }

  /**
   * Obtiene el detalle completo de un expediente (datos + documentos).
   *
   * @param  int $curriculumDocenteId
   * @return object|null
   */
  public static function obtenerDetalleCV($curriculumDocenteId)
  {
    $curriculum = DocenteRepoData::obtenerCVPorId($curriculumDocenteId);

    if (!$curriculum) {
      return null;
    }

    $curriculum->documentos = DocenteRepoData::obtenerDocumentosActivosCV($curriculumDocenteId);
    $curriculum->estado_captura = self::estadoCapturaCV($curriculum->status ?? null);
    $curriculum->faltantes = self::obtenerFaltantesCV($curriculum);

    return $curriculum;
  }

  /**
   * Calcula los datos y documentos obligatorios que faltan en un expediente.
   *
   * @param  object|null $curriculum
   * @return array
   */
  public static function obtenerFaltantesCV($curriculum): array
  {
    if (!$curriculum) {
      return ['Expediente'];
    }

    $faltantes = [];

    foreach (self::camposRequeridosCV() as $campo => $label) {
      $valor = $curriculum->{$campo} ?? null;

      if (is_string($valor)) {
        $vacio = trim($valor) === '';
      } else {
        $vacio = empty($valor);
      }

      if ($vacio) {
        $faltantes[] = $label;
      }
    }

    $documentos = DocenteRepoData::obtenerDocumentosActivosCV($curriculum->curriculum_docente_id);
    $tiposDocumentos = array_map(function ($documento) {
      return $documento->tipo;
    }, $documentos);

    foreach (self::documentosRequeridosCV() as $tipo => $label) {
      if (!in_array($tipo, $tiposDocumentos, true)) {
        $faltantes[] = $label;
      }
    }

    return array_values(array_unique($faltantes));
  }

  /**
   * Guarda el avance de un curriculum.
   * Si no se recibe id, busca por celular para actualizar en lugar de duplicar.
   * Si el expediente ya fue enviado a revision, no permite escribir.
   *
   * @param  array $datos
   * @param  int|null $curriculumDocenteId
   * @return stdClass
   */
  public static function guardarCV(array $datos, $curriculumDocenteId = null)
  {
    $datos['celular'] = preg_replace('/\D+/', '', (string) ($datos['celular'] ?? ''));

    if ($datos['celular'] === '') {
      return (object) [
        'status' => 422,
        'mensaje' => 'El número de celular es obligatorio.',
      ];
    }

    $existentePorCelular = DocenteRepoData::obtenerCVPorCelular($datos['celular']);
    $curriculumActual = null;

    if ($curriculumDocenteId) {
      $curriculumActual = DocenteRepoData::obtenerCVPorId($curriculumDocenteId);

      if (!$curriculumActual) {
        return (object) [
          'status' => 404,
          'mensaje' => 'No se encontró el expediente que intentas actualizar.',
        ];
      }

      if (self::esCVFinalizado($curriculumActual->status ?? null)) {
        return (object) [
          'status' => 409,
          'mensaje' => 'Este curriculum ya fue enviado a revisión y ya no puede modificarse.',
          'estado_captura' => 'completado',
          'curriculum' => $curriculumActual,
          'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($curriculumActual->curriculum_docente_id),
        ];
      }

      if (
        $existentePorCelular &&
        (int) $existentePorCelular->curriculum_docente_id !== (int) $curriculumDocenteId
      ) {
        return (object) [
          'status' => 409,
          'mensaje' => 'El número de celular ya está registrado en otro expediente.',
        ];
      }
    } elseif ($existentePorCelular) {
      if (self::esCVFinalizado($existentePorCelular->status ?? null)) {
        return (object) [
          'status' => 409,
          'mensaje' => 'Este curriculum ya fue enviado a revisión y ya no puede modificarse.',
          'estado_captura' => 'completado',
          'curriculum' => $existentePorCelular,
          'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($existentePorCelular->curriculum_docente_id),
        ];
      }

      $curriculumDocenteId = $existentePorCelular->curriculum_docente_id;
      $curriculumActual = $existentePorCelular;
    }

    try {
      $curriculumDocenteId = DB::transaction(function () use ($datos, $curriculumDocenteId, $curriculumActual) {
        $nombrePersona = Utils::limpiarCaracteresEspeciales(
          !empty($datos['nombre']) ? $datos['nombre'] : ($curriculumActual->nombre ?? 'SIN NOMBRE')
        );

        if ($curriculumDocenteId) {
          $update = DocenteBO::armarUpdateCV($datos);
          DocenteRepoAction::actualizarCurriculum($update, $curriculumDocenteId);
        } else {
          $insert = DocenteBO::armarInsertCV($datos);
          $insert['status'] = Constants::CV_STATUS_EN_CAPTURA;
          $curriculumDocenteId = DocenteRepoAction::agregarCurriculum($insert);
        }

        $ruta = DocenteBO::armarRutaArchivo($curriculumDocenteId, $nombrePersona);
        self::guardarArchivosCV($datos, $curriculumDocenteId, $nombrePersona, $ruta);

        return $curriculumDocenteId;
      }, 3);
    } catch (\Throwable $th) {
      Log::error('Error al guardar curriculum: ' . $th->getMessage());
      return (object) [
        'status' => 300,
        'mensaje' => 'Error al guardar el avance del curriculum.',
      ];
    }

    $curriculum = DocenteRepoData::obtenerCVPorId($curriculumDocenteId);

    return (object) [
      'status' => 200,
      'mensaje' => 'Avance guardado correctamente.',
      'curriculum_docente_id' => $curriculumDocenteId,
      'estado_captura' => self::estadoCapturaCV($curriculum->status ?? null),
      'curriculum' => $curriculum,
      'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($curriculumDocenteId),
      'faltantes' => self::obtenerFaltantesCV($curriculum),
    ];
  }

  /**
   * Envia el curriculum a revision y lo bloquea para el docente.
   *
   * @param  int $curriculumDocenteId
   * @return stdClass
   */
  public static function finalizarCV($curriculumDocenteId)
  {
    $curriculum = DocenteRepoData::obtenerCVPorId($curriculumDocenteId);

    if (!$curriculum) {
      return (object) [
        'status' => 404,
        'mensaje' => 'No se encontró el expediente.',
      ];
    }

    if (self::esCVFinalizado($curriculum->status ?? null)) {
      return (object) [
        'status' => 409,
        'mensaje' => 'Este curriculum ya fue enviado a revisión.',
        'estado_captura' => 'completado',
        'curriculum' => $curriculum,
        'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($curriculumDocenteId),
        'faltantes' => [],
      ];
    }

    $faltantes = self::obtenerFaltantesCV($curriculum);

    if (!empty($faltantes)) {
      return (object) [
        'status' => 422,
        'mensaje' => 'Aún falta información obligatoria para enviar tu curriculum.',
        'faltantes' => $faltantes,
      ];
    }

    try {
      $fecha = now()->format('Y-m-d H:i:s');
      DocenteRepoAction::actualizarCurriculum([
        'status' => Constants::CV_STATUS_COMPLETADO,
        'finalizado_fecha' => $fecha,
        'actualizacion_fecha' => $fecha,
      ], $curriculumDocenteId);
    } catch (\Throwable $th) {
      Log::error('Error al finalizar curriculum: ' . $th->getMessage());
      return (object) [
        'status' => 300,
        'mensaje' => 'Error al enviar el curriculum a revisión.',
      ];
    }

    $curriculum = DocenteRepoData::obtenerCVPorId($curriculumDocenteId);

    return (object) [
      'status' => 200,
      'mensaje' => 'Curriculum enviado a revisión correctamente.',
      'estado_captura' => 'completado',
      'curriculum' => $curriculum,
      'documentos' => DocenteRepoData::obtenerDocumentosActivosCV($curriculumDocenteId),
      'faltantes' => [],
    ];
  }

  /**
   * Guarda o reemplaza los archivos del curriculum.
   * Si se envia un archivo nuevo del mismo tipo, el anterior se marca como Baja.
   *
   * @param  array $datos
   * @param  int $curriculumDocenteId
   * @param  string $nombrePersona
   * @param  string $ruta
   * @return void
   */
  private static function guardarArchivosCV(array $datos, int $curriculumDocenteId, string $nombrePersona, string $ruta): void
  {
    $mapa = [
      'archivoCurriculum' => [Constants::TIPO_ARCHIVO_CURRICULUM, 'descripcionCurriculum'],
      'archivoPlaneacionAcademica' => [Constants::TIPO_ARCHIVO_PLANEACION_ACADEMICA, 'descripcionPlaneacionAcademica'],
      'archivoIne' => [Constants::TIPO_ARCHIVO_INE, 'descripcionIne'],
      'archivoCurp' => [Constants::TIPO_ARCHIVO_CURP, 'descripcionCurp'],
      'archivoDomicilio' => [Constants::TIPO_ARCHIVO_COMPROBANTE_DOMICILIO, 'descripcionDomicilio'],
      'archivoSituacionFiscal' => [Constants::TIPO_ARCHIVO_COMPROBANTE_SITUACION_FISCAL, 'descripcionSituacionFiscal'],
      'archivoActaNacimiento' => [Constants::TIPO_ARCHIVO_ACTA_NACIMIENTO, 'descripcionActaNacimiento'],
      'archivoCedula' => [Constants::TIPO_ARCHIVO_CEDULA_PROFESIONAL, 'descripcionCedula'],
      'archivoCuentaBancaria' => [Constants::TIPO_ARCHIVO_CUENTA_BANCARIA, null],
    ];

    foreach ($mapa as $campoArchivo => $configuracion) {
      [$tipo, $campoDescripcion] = $configuracion;
      $archivo = $datos[$campoArchivo] ?? null;

      if (empty($archivo)) {
        if ($campoDescripcion !== null && array_key_exists($campoDescripcion, $datos)) {
          DocenteRepoAction::actualizarDescripcionDocumentoPorTipo(
            $curriculumDocenteId,
            $tipo,
            $datos[$campoDescripcion]
          );
        }
        continue;
      }

      if (!$archivo instanceof \Illuminate\Http\UploadedFile) {
        continue;
      }

      $descripcion = $campoDescripcion !== null ? ($datos[$campoDescripcion] ?? null) : null;
      $insert = DocenteBO::armarInsertArchivoCV(
        $tipo,
        $archivo,
        $descripcion,
        $nombrePersona,
        $curriculumDocenteId,
        $ruta
      );

      DocenteRepoAction::actualizarStatusDocumentosPorTipo(
        $curriculumDocenteId,
        $tipo,
        Constants::CV_STATUS_BAJA
      );
      DocenteRepoAction::agregarCurriculumArchivo([$insert]);

      $rutaCompleta = rtrim($ruta, '/') . '/' . basename($insert['nombre']);

      try {
        Storage::put($rutaCompleta, file_get_contents($archivo->getRealPath()));
      } catch (Exception $e) {
        Log::error('Error al guardar el archivo: ' . $e->getMessage());
      }
    }
  }
}
