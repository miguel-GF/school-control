<?php

namespace App\Http\Controllers;

use App\Constants;
use App\OrderConstants;
use App\Repos\Data\AsistenciaRepoData;
use App\Services\Actions\DocenteServiceAction;
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
				'idProf' => $user->idusuarios,
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
				'idProf' => $user->idusuarios,
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
			$datos['idProf'] = $user->idusuarios;
			$asistencias = AsistenciaRepoData::obtenerReporteAsistencias($datos);
			return Inertia::render('Docentes/DocenteAsistenciasReporte', [
				'asistencias' => $asistencias,
				'usuario' => $user,
				'fecha' => $datos['fecha'],
				'datos' => $datos['periodo'] . " " . $datos['semestre'] ."° ". $datos['grupo'] . " " . $datos['materia'] . " - " . $datos['licenciatura']
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
	public function docentePasarAsistenciasCargasAcademicasView($idCargaAcademica)
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
		}
		return Inertia::render('Docentes/DocentePasarAsistencia', [
			'alumnos' => $calificaciones,
			'usuario' => $user,
			'idCargaAcademica' => $idCargaAcademica
		]);
	}

	public function pasarAsistencias(Request $request)
	{
		try {
			$request->validate([
				'fecha' => 'required|date',
				'licenciatura' => 'required',
				'semestre' => 'required',
				'grupo' => 'required',
				'alumnos' => 'required',
				'periodo' => 'required',
			]);

			$datos = $request->all();

			$res = DocenteServiceAction::pasarAsistencias($datos);

			$user = Utils::getUser();
			$cargasAcademicas = CargaAcademicaServiceData::listarCargasAcademicas([
				'idCargaAcademica' => $datos['idCargaAcademica']
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
			}
			return Inertia::render('Docentes/DocentePasarAsistencia', [
				'alumnos' => $calificaciones,
				'usuario' => $user,
				'status' => $res->status,
				'mensaje' => $res->mensaje,
				'idCargaAcademica' => $datos['idCargaAcademica'],
			]);
		} catch (\Throwable $th) {
			Log::error('Error en pasar asistencias ' . $th);
			return Inertia::render('Docentes/DocentePasarAsistencia', [
				'alumnos' => $calificaciones ?? [],
				'status' => 300,
				'mensaje' => 'Ocurrio un error al guardar asistencias',
				'idCargaAcademica' => $datos['idCargaAcademica'],
			]);
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
