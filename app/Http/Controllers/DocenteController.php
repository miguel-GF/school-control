<?php

namespace App\Http\Controllers;

use App\Services\Actions\DocenteServiceAction;
use App\Services\Data\DocenteServiceData;
use App\Utils;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocenteController extends Controller
{
	public function docenteCargasAcademicasView()
	{
		$user = Utils::getUser();
		$cargasAcademicas = DocenteServiceData::obtenerCalificacionesPorId([
			'idProf' => $user->idusuarios,
		]);
		return Inertia::render('Docentes/DocenteCargasAcademicas', [
			'cargasAcademicas' => $cargasAcademicas,
			'usuario' => $user,
		]);
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
