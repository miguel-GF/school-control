<?php

namespace App\Http\Controllers;

use App\Services\Actions\DocenteServiceAction;
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
	
	/**
	 * docentePasarAsistenciaCargasAcademicasView
	 *
	 * @param  mixed $request
	 */
	public function docentePasarAsistenciasCargasAcademicasView($claveMateria)
	{
		$user = Utils::getUser();
		$cargasAcademicasAlumnos = DocenteServiceData::obtenerAlumnosPorCargaAcademica([
			'idProf' => $user->idusuarios,
			'claveMateria' => $claveMateria
		]);
		return Inertia::render('Docentes/DocentePasarAsistencia', [
			'alumnos' => $cargasAcademicasAlumnos,
			'usuario' => $user,
		]);
	}

	public function pasarAsistencias(Request $request)
    {
			$request->validate([
				'fecha' => 'required|date',
				'licenciatura' => 'required',
				'semestre' => 'required',
				'grupo' => 'required',
				'claveMateria' => 'required',
				'alumnos' => 'required',
				'periodo' => 'required',
			]);

			$datos = $request->all();

			DocenteServiceAction::pasarAsistencias($datos);

			$user = Utils::getUser();
			$cargasAcademicasAlumnos = DocenteServiceData::obtenerAlumnosPorCargaAcademica([
				'idProf' => $user->idusuarios,
				'claveMateria' => $datos['claveMateria']
			]);
			return Inertia::render('Docentes/DocentePasarAsistencia', [
				'alumnos' => $cargasAcademicasAlumnos,
				'usuario' => $user,
				'status' => 200,
				'mensaje' => 'Asistencias agredadas correctamente',
			]);
    }
}
