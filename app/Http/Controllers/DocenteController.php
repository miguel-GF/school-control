<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Requests\DocenteGuardarCVRequest;
use App\Http\Requests\DocentePasarAsistenciaRequest;
use App\Jobs\RecuperarArchivosCurriculums;
use App\Models\CurriculumDocenteArchivo;
use App\OrderConstants;
use App\Repos\Data\AsistenciaRepoData;
use App\Repos\Data\CalificacionRepoData;
use App\Services\Actions\DocenteServiceAction;
use App\Services\Data\AsistenciaServiceData;
use App\Services\Data\CalificacionServiceData;
use App\Services\Data\CargaAcademicaServiceData;
use App\Services\Data\DocenteServiceData;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DocenteController extends Controller
{
  public function docenteCargasAcademicasView(Request $request)
  {
    try {
      $user = Utils::getUser();
      $datos = $request->all();
      $res = DocenteServiceData::obtenerDataCargasAcademicasPorId([
        'idProf' => $user->claveusuario,
        'periodo' => $datos['periodo'] ?? "",
      ]);
      $filtros['periodo'] = $datos['periodo'] ?? "";
      return Inertia::render('Docentes/DocenteCargasAcademicas', [
        'cargasAcademicas' => $res->cargasAcademicas,
        'periodos' => $res->periodos,
        'usuario' => $user,
        'filtrosRes' => $filtros,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error en docente cargas academicas view' . $th);
      throw $th;
    }
  }

  public function docenteAsistenciasCargasAcademicasView(Request $request)
  {
    try {
      $user = Utils::getUser();
      $datos = $request->all();
      $res = DocenteServiceData::obtenerDataCargasAcademicasPorId([
        'idProf' => $user->claveusuario,
        'periodo' => $datos['periodo'] ?? "",
      ]);
      $filtros['periodo'] = $datos['periodo'] ?? "";
      return Inertia::render('Docentes/DocenteAsistenciasCargasAcademicas', [
        'cargasAcademicas' => $res->cargasAcademicas,
        'periodos' => $res->periodos,
        'usuario' => $user,
        'filtrosRes' => $filtros,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error en docente asistencias cargas academicas view' . $th);
      throw $th;
    }
  }

  public function reporteAsistenciasView(Request $request)
  {
    try {
      $user = Utils::getUser();
      $datos = $request->all();
      $datos['idProf'] = $user->claveusuario;
      $asistencias = AsistenciaRepoData::obtenerReporteAsistencias($datos);
      return Inertia::render('Docentes/DocenteAsistenciasReporte', [
        'asistencias' => $asistencias,
        'usuario' => $user,
        'fecha' => "Del " . $datos['fechaInicio'] . " al " . $datos['fechaFin'],
        'datos' => $datos,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error en reporte asistencias view' . $th);
      throw $th;
    }
  }

  /**
   * docentePasarAsistenciaCargasAcademicasView
   *
   * @param  mixed $request
   */
  public function docentePasarAsistenciasCargasAcademicasView($idCargaAcademica, $fecha)
  {
    $user = Utils::getUser();
    $cargasAcademicas = CargaAcademicaServiceData::listarCargasAcademicas([
      'idCargaAcademica' => $idCargaAcademica
    ]);
    $calificaciones = [];
    if (!empty($cargasAcademicas)) {
      $calificaciones = CalificacionServiceData::listarCalificaciones([
        'claveMateria' => $cargasAcademicas[0]->clavemat,
        'licenciatura' => $cargasAcademicas[0]->licenciatura,
        'periodo' => $cargasAcademicas[0]->periodo,
        'semestre' => $cargasAcademicas[0]->semestre,
        'grupo' => $cargasAcademicas[0]->grupo,
        'status' => Constants::ACTIVO_STATUS,
        'ordenar' => OrderConstants::NOMBRE_ASC,
      ]);
      $asistencias = AsistenciaServiceData::listarAsistencias([
        'licenciatura' => $cargasAcademicas[0]->licenciatura,
        'periodo' => $cargasAcademicas[0]->periodo,
        'semestre' => $cargasAcademicas[0]->semestre,
        'grupo' => $cargasAcademicas[0]->grupo,
        'materia' => $cargasAcademicas[0]->materia,
        'ordenar' => OrderConstants::NOMBRE_ASC,
        'fecha' => str_replace('-', '/', $fecha),
      ]);
      $totalAsistencias = count($asistencias);
      foreach ($calificaciones as $calificacion) {
        $existe = current(array_filter($asistencias, function ($asistencia) use ($calificacion) {
          return $asistencia->numestudiante == $calificacion->numestudiante;
        }));
        if ($existe) {
          $calificacion->idAsistencias = $existe->idAsistencias;
          $calificacion->asistencia = $existe->asistencia ? true : false;
          $calificacion->valor_inicial = $existe->asistencia ? true : false;
          $calificacion->es_inicial = false;
        } else {
          $calificacion->idAsistencias = null;
          $calificacion->asistencia = false;
          $calificacion->valor_inicial = false;
          $calificacion->es_inicial = true;
        }
      }
    }
    return Inertia::render('Docentes/DocentePasarAsistencia', [
      'alumnos' => $calificaciones,
      'usuario' => $user,
      'idCargaAcademica' => $idCargaAcademica,
      'fechaFiltro' => $fecha,
      'totalAsistencias' => $totalAsistencias,
    ]);
  }

  public function pasarAsistencias(DocentePasarAsistenciaRequest $request)
  {
    try {
      $datos = $request->all();
      $res = DocenteServiceAction::pasarAsistencias($datos);

      // $user = Utils::getUser();
      // $cargasAcademicas = CargaAcademicaServiceData::listarCargasAcademicas([
      // 	'idCargaAcademica' => $datos['idCargaAcademica']
      // ]);
      // $calificaciones = [];
      // if (!empty($cargasAcademicas)) {
      // 	$calificaciones = CalificacionServiceData::listarCalificaciones([
      // 		'claveMateria' => $cargasAcademicas[0]->clavemat,
      // 		'licenciatura' => $cargasAcademicas[0]->licenciatura,
      // 		'periodo' => $cargasAcademicas[0]->periodo,
      // 		'semestre' => $cargasAcademicas[0]->semestre,
      // 		'grupo' => $cargasAcademicas[0]->grupo,
      // 		'status' => Constants::ACTIVO_STATUS,
      // 		'ordenar' => OrderConstants::NOMBRE_ASC,
      // 	]);
      // }
      return response([
        'mensaje' => $res->mensaje,
        'status' => $res->status,
        'asistencias' => $res->asistencias ?? [],
      ]);
    } catch (\Throwable $th) {
      Log::error('Error en pasar asistencias ' . $th);
      response([
        'mensaje' => 'Ocurrio un error al guardar asistencias',
        'status' => 300
      ], 300);
    }
  }

  public function actualizarAsistencias(DocentePasarAsistenciaRequest $request)
  {
    try {
      $datos = $request->all();
      DocenteServiceAction::actualizarAsistencias($datos);

      return response([
        'mensaje' => 'Asistencias actualizadas correctamente',
        'status' => 200,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error en actualizar asistencias ' . $th);
      response([
        'mensaje' => 'Ocurrio un error al actualizar asistencias',
        'status' => 300
      ], 300);
    }
  }

  /**
   * docenteCapturarCalificacionesView
   *
   * @param  mixed $request
   */
  public function docenteCapturarCalificacionesView($idCargaAcademica)
  {
    $user = Utils::getUser();
    $cargasAcademicas = CargaAcademicaServiceData::listarCargasAcademicas([
      'idCargaAcademica' => $idCargaAcademica
    ]);
    $configuracionCapturaCalificaciones = CalificacionRepoData::obtenerConfiguracionesCapturaCalificaciones([
      'periodo' => $cargasAcademicas[0]->periodo,
    ]);
    $calificaciones = CalificacionServiceData::listarCalificaciones([
      'claveMateria' => $cargasAcademicas[0]->clavemat,
      'licenciatura' => $cargasAcademicas[0]->licenciatura,
      'periodo' => $cargasAcademicas[0]->periodo,
      'semestre' => $cargasAcademicas[0]->semestre,
      'grupo' => $cargasAcademicas[0]->grupo,
      'status' => Constants::ACTIVO_STATUS,
      'ordenar' => OrderConstants::NOMBRE_ASC,
    ]);
    return Inertia::render('Docentes/DocenteCapturarCalificaciones', [
      'calificaciones' => $calificaciones,
      'usuario' => $user,
      'idCargaAcademica' => $idCargaAcademica,
      'configuracionCapturaCalificaciones' => $configuracionCapturaCalificaciones
    ]);
  }

  public function guardarCalificaciones(Request $request)
  {
    $request->validate([
      'calificaciones' => 'required',
      'idCargaAcademica' => 'required',
      'fecha' => 'nullable',
    ]);

    $datos = $request->all();

    DocenteServiceAction::guardarCalificaciones($datos);

    $user = Utils::getUser();
    $cargasAcademicas = CargaAcademicaServiceData::listarCargasAcademicas([
      'idCargaAcademica' => $datos['idCargaAcademica']
    ]);
    $calificaciones = CalificacionServiceData::listarCalificaciones([
      'claveMateria' => $cargasAcademicas[0]->clavemat,
      'licenciatura' => $cargasAcademicas[0]->licenciatura,
      'periodo' => $cargasAcademicas[0]->periodo,
      'semestre' => $cargasAcademicas[0]->semestre,
      'grupo' => $cargasAcademicas[0]->grupo,
      'status' => Constants::ACTIVO_STATUS,
      'ordenar' => OrderConstants::NOMBRE_ASC,
    ]);
    $configuracionCapturaCalificaciones = CalificacionRepoData::obtenerConfiguracionesCapturaCalificaciones([
      'periodo' => $cargasAcademicas[0]->periodo,
    ]);
    return Inertia::render('Docentes/DocenteCapturarCalificaciones', [
      'calificaciones' => $calificaciones,
      'usuario' => $user,
      'idCargaAcademica' => $datos['idCargaAcademica'],
      'status' => 200,
      'mensaje' => 'Calificaciones guardadas correctamente',
      'configuracionCapturaCalificaciones' => $configuracionCapturaCalificaciones
    ]);
  }

  /**
   * Busca un expediente por numero de celular para saber si el docente
   * ya tiene un curriculum en captura o ya lo envio a revision.
   */
  public function buscarPorCelular(Request $request)
  {
    try {
      $request->validate([
        'celular' => 'required|string|max:20',
      ]);

      $resultado = DocenteServiceAction::consultarCVPorCelular($request->input('celular'));

      return response([
        'mensaje' => $resultado['estado_captura'] === 'nuevo'
          ? 'No se encontró un curriculum con ese número de celular.'
          : 'Curriculum encontrado correctamente.',
        'estado_captura' => $resultado['estado_captura'],
        'curriculum' => $resultado['curriculum'],
        'documentos' => $resultado['documentos'],
        'status' => 200,
      ]);
    } catch (\Illuminate\Validation\ValidationException $th) {
      return response([
        'mensaje' => $th->getMessage(),
        'status' => 422,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al buscar curriculum por celular: ' . $th->getMessage());
      return response([
        'mensaje' => 'Error al buscar el curriculum.',
        'status' => 300,
      ]);
    }
  }

  /**
   * Crea o actualiza un curriculum (guardar avance).
   * Si el celular ya existe, actualiza el expediente en lugar de duplicarlo.
   */
  public function guardarCV(DocenteGuardarCVRequest $request)
  {
    return $this->responderGuardadoCV($request);
  }

  /**
   * Actualiza un curriculum existente.
   */
  public function actualizarCV(DocenteGuardarCVRequest $request, $id)
  {
    return $this->responderGuardadoCV($request, $id);
  }

  /**
   * Guarda el avance y arma la respuesta comun para crear/actualizar.
   */
  private function responderGuardadoCV(DocenteGuardarCVRequest $request, $id = null)
  {
    try {
      $datos = $request->all();
      $res = DocenteServiceAction::guardarCV($datos, $id);

      return response([
        'mensaje' => $res->mensaje,
        'status' => $res->status,
        'curriculum_docente_id' => $res->curriculum_docente_id ?? null,
        'estado_captura' => $res->estado_captura ?? null,
        'curriculum' => $res->curriculum ?? null,
        'documentos' => $res->documentos ?? [],
        'faltantes' => $res->faltantes ?? [],
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al guardar curriculum: ' . $th->getMessage());
      return response([
        'mensaje' => 'Error al guardar curriculum.',
        'status' => 300,
      ]);
    }
  }

  /**
   * Envia el curriculum a revision y lo bloquea.
   */
  public function finalizarCV($id)
  {
    try {
      $res = DocenteServiceAction::finalizarCV($id);

      return response([
        'mensaje' => $res->mensaje,
        'status' => $res->status,
        'estado_captura' => $res->estado_captura ?? null,
        'curriculum' => $res->curriculum ?? null,
        'documentos' => $res->documentos ?? [],
        'faltantes' => $res->faltantes ?? [],
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al finalizar curriculum: ' . $th->getMessage());
      return response([
        'mensaje' => 'Error al enviar el curriculum a revisión.',
        'status' => 300,
      ]);
    }
  }

  public function listarCVS(Request $request)
  {
    try {
      // $datos = $request->all();

      $query = DB::table('curriculum_docentes')
        ->select('*')
        ->orderByDesc('registro_fecha')
        ->orderByDesc('curriculum_docente_id');

      Log::info($query->get()->toArray());

      return response([
        'mensaje' => 'Curriculums listados correctamente',
        'curriculums' => $query->get()->toArray(),
        'status' => 200,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al listar curriculum' . $th);
      return response([
        'mensaje' => 'Error al listar curriculum',
        'status' => 300
      ]);
    }
  }

  public function detalleCV(Request $request, $id)
  {
    try {
      $curriculum = DocenteServiceAction::obtenerDetalleCV($id);

      if (!$curriculum) {
        return response([
          'mensaje' => 'No se encontró el curriculum.',
          'status' => 404,
          'curriculum' => null,
        ]);
      }

      return response([
        'mensaje' => 'Curriculums archivos obtenidos correctamente',
        'curriculum' => $curriculum,
        'status' => 200,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al listar curriculum archivos: ' . $th->getMessage());
      return response([
        'mensaje' => 'Error al listar curriculum archivos',
        'status' => 300
      ]);
    }
  }

  public function descargarDocumento(Request $request)
  {
    $datos = $request->all();
    $id = $datos['archivoId'];
    $archivo = CurriculumDocenteArchivo::findOrFail($id);

    // Asegúrate de que la ruta esté protegida y sea válida
    $ruta = storage_path("app/$archivo->ruta/$archivo->nombre");

    if (!file_exists($ruta)) {
      abort(404, 'Archivo no encontrado ???');
    }

    return response()->download($ruta, $archivo->nombre);
  }

  public function recuperarCurriculumsBlobs(string $token)
  {
    if (md5($token) !== 'afef3521557a6b9cfebc718de0d6e3d0') {
      return response()->json(['message' => 'Token inválido.']);
    }

    $pageSize = 10;
    // Asegúrate de que tu conexión de DB es 'mysql' si es diferente a la predeterminada
    $documentos = DB::table('curriculum_docentes_archivos')
      ->select('nombre', 'ruta', 'archivo')
      ->where('status', 'Activo')
      ->orderBy('curriculum_docente_archivo_id')
      ->chunk($pageSize, function ($documentos) {

        // $documentos es una colección de 50 (o el tamaño que definas) registros
        foreach ($documentos as $documento) {

          // CRÍTICO: Verificar si el BLOB no es NULL antes de codificar
          if (empty($documento->archivo)) {
            Log::warning("Registro omitido por BLOB vacío: Nombre -> " . $documento->nombre);
            continue;
          }

          $data = [
            'nombre' => $documento->nombre,
            'ruta' => $documento->ruta,
            // Codificamos y enviamos al Job
            'archivo_base64' => base64_encode($documento->archivo),
          ];

          // Disparamos el Job para que se ejecute en segundo plano
          RecuperarArchivosCurriculums::dispatch($data);
        }
      });

    return response()->json(['message' => 'Procesamiento de BLOBs iniciado en segundo plano.']);
  }
}
