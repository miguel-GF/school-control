<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumDocenteArchivo extends Model
{
  use HasFactory;
  protected $table = 'curriculum_docentes_archivos';
  protected $primaryKey = 'curriculum_docente_archivo_id';
  public $timestamps = false;
  const CREATED_AT = 'registro_fecha'; // Definir el nombre de la columna de fecha de creación
  const UPDATED_AT = 'actualizacion_fecha'; // Definir el nombre de la columna de fecha de actualización
}
