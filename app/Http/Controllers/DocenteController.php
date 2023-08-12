<?php

namespace App\Http\Controllers;

use App\Services\Data\DocenteServiceData;
use App\Utils;
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
}
