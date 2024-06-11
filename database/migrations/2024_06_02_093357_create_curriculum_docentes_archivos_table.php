<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('curriculum_docentes_archivos', function (Blueprint $table) {
        $table->id('curriculum_docente_archivo_id');
        $table->unsignedBigInteger('curriculum_docente_id');
        $table->string('tipo', 35);
        $table->text('nombre');
        $table->binary('archivo');
        $table->string('extension', 5);
        $table->string('tamanio_humano', 12);
        $table->bigInteger('tamanio');
        $table->foreign('curriculum_docente_id')->references('curriculum_docente_id')->on('curriculum_docentes');
        $table->string('status')->default('Activo');
        $table->timestamps();
        // Cambiar nombres de campos de fecha
        $table->renameColumn('created_at', 'registro_fecha');
        $table->renameColumn('updated_at', 'actualizacion_fecha');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('curriculum_docentes_archivos');
    }
};
