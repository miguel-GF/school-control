<?php

namespace App\Services\BO;

use App\Utils;
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

  public static function armarInsertCV($datos)
  {
    $insert = [];
    $insert['nombre'] = $datos['nombre'] ? strtoupper($datos['nombre']) : null;
    $insert['fecha_nacimiento'] = $datos['fechaNacimiento'] ?? null;
    $insert['ciudad'] = $datos['ciudad'] ? strtoupper($datos['ciudad']) : null;
    $insert['estado'] = $datos['estado'] ? strtoupper($datos['estado']) : null;
    $insert['pais'] = $datos['pais'] ? strtoupper($datos['pais']) : null;
    $insert['estado_civil'] = $datos['estadoCivil'] ?? null;
    $insert['genero'] = $datos['genero'] ?? null;
    $insert['correo_electronico'] = $datos['correo'] ?? null;
    $insert['correo_institucional'] = $datos['correoInstitucional'] ?? null;
    $insert['numero_celular'] = $datos['celular'] ?? null;
    $insert['facebook'] = $datos['facebook'] ?? null;
    $insert['numero_casa'] = $datos['telefono'] ?? null;
    // DOMICILIO
    $insert['domicilio_calle'] = $datos['domicilioCalle'] ? strtoupper($datos['domicilioCalle']) : null;
    $insert['domicilio_no_exterior'] = $datos['domicilioNoExterior'] ?? null;
    $insert['domicilio_no_interior'] = $datos['domicilioNoInterior'] ?? null;
    $insert['domicilio_colonia'] = $datos['domicilioColonia'] ? strtoupper($datos['domicilioColonia']) : null;
    $insert['domicilio_ciudad'] = $datos['domicilioCiudad'] ? strtoupper($datos['domicilioCiudad']) : null;
    $insert['domicilio_estado'] = $datos['domicilioEstado'] ? strtoupper($datos['domicilioEstado']) : null;
    $insert['domicilio_codigo_postal'] = $datos['domicilioCodigoPostal'];
    // DATOS FISCALES
    $insert['dato_fiscal_rfc'] = $datos['rfc'] ? strtoupper($datos['rfc']) : null;
    $insert['dato_fiscal_curp'] = $datos['curp'] ? strtoupper($datos['curp']) : null;
    $insert['dato_fiscal_clabe'] = $datos['clabe'] ? strtoupper($datos['clabe']) : null;
    $insert['dato_fiscal_banco'] = $datos['banco'] ? strtoupper($datos['banco']) : null;
    // DOMICILIO FISCAL
    $insert['dato_fiscal_calle'] = $datos['fiscalCalle'] ? strtoupper($datos['fiscalCalle']) : null;
    $insert['dato_fiscal_no_exterior'] = $datos['fiscalNoExterior'] ?? null;
    $insert['dato_fiscal_no_interior'] = $datos['fiscalNoInterior'] ?? null;
    $insert['dato_fiscal_colonia'] = $datos['fiscalColonia'] ? strtoupper($datos['fiscalColonia']) : null;
    $insert['dato_fiscal_ciudad'] = $datos['fiscalCiudad'] ? strtoupper($datos['fiscalCiudad']) : null;
    $insert['dato_fiscal_estado'] = $datos['fiscalEstado'] ? strtoupper($datos['fiscalEstado']) : null;
    $insert['dato_fiscal_codigo_postal'] = $datos['fiscalCodigoPostal'] ?? null;
    // PORCENTAJE INGRESOS
    $insert['porcentaje_actividad_profesional'] = $datos['porcentajeActividadProf'] ?? 0;
    $insert['porcentaje_asalariado'] = $datos['porcentajeAsalariado'] ?? 0;
    $insert['porcentaje_pensionado'] = $datos['porcentajePensionado'] ?? 0;
    $insert['porcentaje_docencia'] = $datos['porcentajeDocencia'] ?? 0;
    // INFORMACION COMPLEMENTARIA
    $insert['enfermedades'] = $datos['enfermedades'] ? strtoupper($datos['enfermedades']) : null;
    $insert['alergias'] = $datos['alergias'] ? strtoupper($datos['alergias']) : null;
    $insert['tipo_sangre'] = $datos['tipoSangre'] ?? null;
    $insert['comentarios'] = $datos['informacionAdicional'] ? strtoupper($datos['informacionAdicional']) : null;
    $insert['registro_fecha'] = now()->format('Y-m-d H:i:s');

    return $insert;
  }

  /**
   * armarInsertArchivoCV
   *
   * @param  mixed $tipo
   * @param  mixed $archivo
   * @param  mixed $descripcion
   * @param  mixed $nombrePersona
   * @param  mixed $curriculumDocenteId
   * @param  mixed $ruta
   * @return array
   */
  public static function armarInsertArchivoCV(string $tipo, $archivo, $descripcion, $nombrePersona, $curriculumDocenteId, $ruta): array
  {
    $tamanio = $archivo->getSize();
    $fecha = now()->format('Y-m-d H:i:s');
    $fechaArchivo = now()->format('Y_m_d_H_i_s');
    $extension = strtolower($archivo->getClientOriginalExtension());
    $insert = [];

    $insert['curriculum_docente_id'] = $curriculumDocenteId;
    $insert['archivo'] = file_get_contents($archivo->getRealPath());
    $insert['tipo'] = $tipo;
    $insert['nombre'] = self::armarNombreTipoArchivo($tipo, $nombrePersona, $fechaArchivo) . "." . $extension;
    $insert['extension'] = $extension;
    $insert['tamanio'] = $tamanio;
    $insert['tamanio_humano'] = Utils::obtenerTamanioLegibleArchivo($tamanio);
    $insert['descripcion'] = $descripcion ? strtoupper($descripcion) : null;
    $insert['registro_fecha'] = $fecha;
    $insert['ruta'] = $ruta;

    return $insert;
  }

  /**
   * armarNombreTipoArchivo
   *
   * @param  mixed $tipo
   * @param  mixed $nombrePersona
   * @param  mixed $fecha
   * @return string
   */
  public static function armarNombreTipoArchivo(string $tipo, string $nombrePersona, $fecha): string
  {
    $nombreArchivo = explode(' ', trim($nombrePersona));
    $nombreArchivo[] = $tipo;
    $nombreArchivo[] = $fecha;
    $nombreArchivo = strtoupper(implode('_', $nombreArchivo));
    return $nombreArchivo;
  }

  /**
   * armarNombreTipoArchivo
   *
   * @param  int $curriculumId
   * @param  mixed $nombrePersona
   * @return string
   */
  public static function armarRutaArchivo(int $curriculumId, string $nombrePersona): string
  {
    $idNombre = "$curriculumId $nombrePersona";
    $nombreRuta[] = "curriculums_archivos";
    $nombreRuta[] = implode('_', explode(' ',trim($idNombre)));
    $nombreRuta = strtoupper(implode('/', $nombreRuta));
    return $nombreRuta;
  }
}
