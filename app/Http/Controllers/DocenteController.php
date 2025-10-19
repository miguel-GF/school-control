<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Requests\DocenteAgregarCVRequest;
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
use stdClass;

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

  public function guardarCV(DocenteAgregarCVRequest $request)
  {
    try {
      $datos = $request->all();

      DocenteServiceAction::guardarCV($datos);

      return response([
        'mensaje' => 'Curriculum guardado correctamente',
        'status' => 200,
      ]);
    } catch (\Illuminate\Validation\ValidationException $th) {
      return response([
        'mensaje' => $th->getMessage(),
        'status' => 300
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al guardar curriculum' . $th);
      return response([
        'mensaje' => 'Error al guardar curriculum',
        'status' => 300
      ]);
    }
  }

  public function listarCVS(Request $request)
  {
    try {
      // $datos = $request->all();

      $query = DB::table('curriculum_docentes')
        ->select('*');

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
      // $datos = $request->all();
      $queryPrincipal = DB::table('curriculum_docentes')
        ->select('*')
        ->where('curriculum_docente_id', $id)
        ->get()->first();

      $query = DB::table('curriculum_docentes_archivos')
        ->select(
          'curriculum_docente_archivo_id',
          'curriculum_docente_id',
          'tipo',
          'nombre',
          'descripcion'
        )
        ->where('curriculum_docente_id', $id);

      $curriculum = new stdClass();
      $curriculum->curriculum_docente_id = $id;
      $curriculum->nombre = $queryPrincipal->nombre;
      $curriculum->documentos = $query->get()->toArray();

      return response([
        'mensaje' => 'Curriculums archivos obtenidos correctamente',
        'curriculum' => $curriculum,
        'status' => 200,
      ]);
    } catch (\Throwable $th) {
      Log::error('Error al listar curriculum archivos' . $th);
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
    // Asegúrate de que tu conexión de DB es 'mysql' si es diferente a la predeterminada
    $documentos = DB::table('curriculum_docentes_archivos')
      ->select('nombre', 'ruta', 'archivo')
      ->where('status', 'Activo')
      ->get();

    foreach ($documentos as $documento) {
      // Convertir el objeto a array para el constructor del Job
      $data = [
        'nombre' => $documento->nombre,
        'ruta' => $documento->ruta,
        'archivo_base64' => base64_encode($documento->archivo),
      ];

      // Disparar el Job. Usamos dispatch() para ponerlo en la cola de segundo plano.
      RecuperarArchivosCurriculums::dispatch($data);
    }

    return response()->json(['message' => 'Procesamiento de BLOBs iniciado en segundo plano.']);
  }
}
