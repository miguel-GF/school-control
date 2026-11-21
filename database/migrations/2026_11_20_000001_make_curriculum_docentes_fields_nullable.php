<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Permite guardar avances parciales. La obligatoriedad de estos campos
   * se valida en el servicio al momento de enviar el curriculum a revision.
   */
  public function up(): void
  {
    Schema::table('curriculum_docentes', function (Blueprint $table) {
      $table->string('nombre', 220)->nullable()->change();
      $table->date('fecha_nacimiento')->nullable()->change();
      $table->string('ciudad', 30)->nullable()->change();
      $table->string('estado', 30)->nullable()->change();
      $table->string('pais', 30)->nullable()->change();
      $table->string('estado_civil', 15)->nullable()->change();
      $table->string('genero', 20)->nullable()->change();
      $table->string('correo_electronico', 150)->nullable()->change();
      $table->text('domicilio_calle')->nullable()->change();
      $table->string('domicilio_no_exterior', 10)->nullable()->change();
      $table->string('domicilio_colonia', 40)->nullable()->change();
      $table->string('domicilio_ciudad', 40)->nullable()->change();
      $table->string('domicilio_estado', 40)->nullable()->change();
      $table->string('domicilio_codigo_postal', 5)->nullable()->change();
    });
  }

  public function down(): void
  {
    // No revertimos a NOT NULL para no romper expedientes parciales existentes.
  }
};
