<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ViewController extends Controller
{
    public function loginView() {
        return Inertia::render('Login', []);
    }

    public function docenteDashboardView() {
        $session = Session();
        $user = $session->get('user');
        return Inertia::render('Docentes/DashboardDocente', [
            'usuario' => $user,
        ]);
    }

    public function alumnoDashboardView() {
        $session = Session();
        $user = $session->get('user');
        return Inertia::render('Alumnos/DashboardAlumno', [
            'usuario' => $user,
        ]);
    }
}
