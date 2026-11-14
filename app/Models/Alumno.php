<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Alumno extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'alumnos';
    protected $primaryKey = 'idalumnos';
    protected $hidden = ['contrasena'];
    public $timestamps = false;
}
