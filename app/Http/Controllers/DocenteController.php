<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Requests\DocentePasarAsistenciaRequest;
use App\OrderConstants;
use App\Repos\Data\AsistenciaRepoData;
use App\Services\Actions\DocenteServiceAction;
use App\Services\Data\AsistenciaServiceData;
use App\Services\Data\CalificacionServiceData;
use App\Services\Data\CargaAcademicaServiceData;
use App\Services\Data\DocenteServiceData;
use App\Utils;
use Illuminate\Http\Request;
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
		return Inertia::render('Docentes/DocenteCapturarCalificaciones', [
			'calificaciones' => $calificaciones,
			'usuario' => $user,
			'idCargaAcademica' => $datos['idCargaAcademica'],
			'status' => 200,
			'mensaje' => 'Calificaciones guardadas correctamente',
		]);
	}
}
