<?php

namespace App\Http\Controllers;

use App\Services\Data\AlumnoServiceData;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AlumnoController extends Controller
{

	public function alumnoCalificacionesView(Request $request)
	{
		try {
			$user = Utils::getUser();
			$datos = $request->all();
			$res = AlumnoServiceData::obteneDataCalificaciones([
				'numEstudiante' => $user->numestudiante,
				'periodo' => $datos['periodo'] ?? "",
			]);
			$filtros['periodo'] = $datos['periodo'] ?? "";
			return Inertia::render('Alumnos/AlumnoCalificaciones', [
				'calificaciones' => $res->calificaciones,
				'periodos' => $res->periodos,
				'usuario' => $user,
				'filtrosRes' => $filtros,
			]);
		} catch (\Throwable $th) {
			Log::error('Error en alumno calificaciones view' . $th);
			throw $th;
		}
	}
}
