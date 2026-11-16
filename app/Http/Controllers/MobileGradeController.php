<?php

namespace App\Http\Controllers;

use App\Services\Data\AlumnoServiceData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileGradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alumno = $request->user();
        $periodo = $request->query('periodo');
        $periodo = is_string($periodo) ? $periodo : '';

        try {
            $res = AlumnoServiceData::obteneDataCalificaciones([
                'numEstudiante' => $alumno->numestudiante,
                'periodo' => $periodo,
            ]);

            $calificaciones = collect($res->calificaciones)
                ->map(fn ($row) => $this->gradeResponse($row))
                ->values();

            $periodos = $calificaciones
                ->pluck('periodo')
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->unique()
                ->sortBy(fn ($value) => mb_strtolower((string) $value))
                ->values();

            return response()->json([
                'data' => $calificaciones,
                'periodos' => $periodos,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error en calificaciones móviles: ' . $exception->getMessage());

            return response()->json([
                'message' => 'No se pudieron obtener las calificaciones.',
            ], 500);
        }
    }

    private function gradeResponse(object $row): array
    {
        return [
            'id' => $row->idcalificaciones ?? null,
            'clave_materia' => $row->cvemat,
            'materia' => $row->materia,
            'periodo' => $row->periodo,
            'semestre' => $this->integer($row->semestre),
            'grupo' => $row->grupo,
            'asistencias' => $this->integer($row->asistencias),
            'faltas' => $this->integer($row->faltas),
            'primer_parcial' => $this->number($row->primerparcial),
            'segundo_parcial' => $this->number($row->segundoparcial),
            'ordinario' => $this->number($row->ordinario),
            'extraordinario' => $this->number($row->extraordinario),
            'final' => $this->number($row->final),
            'status' => $row->status,
        ];
    }

    private function number(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $text = str_replace(',', '.', $text);

        return is_numeric($text) ? (float) $text : null;
    }

    private function integer(mixed $value): ?int
    {
        $number = $this->number($value);

        return $number === null ? null : (int) $number;
    }
}
