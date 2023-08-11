<?php

namespace App\Http\Controllers;

use App\Services\Data\AlumnoServiceData;
use App\Utils;
use Inertia\Inertia;

class AlumnoController extends Controller
{

	public function alumnoCalificacionesView()
	{
		$user = Utils::getUser();
		$calificaciones = AlumnoServiceData::obtenerCalificacionesPorId([
			'numEstudiante' => $user->numestudiante,
		]);
		return Inertia::render('Alumnos/AlumnoCalificaciones', [
			'calificaciones' => $calificaciones,
			'usuario' => $user,
		]);
	}
}
