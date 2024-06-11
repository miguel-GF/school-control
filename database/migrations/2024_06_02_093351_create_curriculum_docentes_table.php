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
        Schema::create('curriculum_docentes', function (Blueprint $table) {
            $table->id('curriculum_docente_id');
            $table->bigInteger('folio');
            $table->string('nombre', 220);
            $table->date('fecha_nacimiento');
            $table->string('ciudad', 30);
            $table->string('estado', 30);
            $table->string('pais', 30);
            $table->string('estado_civil', 15);
            $table->string('genero', 20);
            $table->string('correo_electronico', 150);
            $table->string('numero_celular', 20);
            $table->text('facebook')->nullable();
            $table->string('numero_casa', 20)->nullable();
            // DOMICILIO
            $table->text('domicilio_calle');
            $table->string('domicilio_no_exterior', 10);
            $table->string('domicilio_no_interior', 10)->nullable();
            $table->string('domicilio_colonia', 40);
            $table->string('domicilio_ciudad', 40);
            $table->string('domicilio_estado', 40);
            $table->string('domicilio_codigo_postal', 5);
            // DATOS FISCALES
            $table->string('dato_fiscal_rfc', 15)->nullable();
            $table->string('dato_fiscal_curp', 25)->nullable();
            $table->string('dato_fiscal_clabe', 50)->nullable();
            $table->string('dato_fiscal_banco', 50)->nullable();
            // DOMICILIO FISCAL
            $table->text('dato_fiscal_calle')->nullable();
            $table->string('dato_fiscal_no_exterior', 10)->nullable();
            $table->string('dato_fiscal_no_interior', 10)->nullable();
            $table->string('dato_fiscal_colonia', 40)->nullable();
            $table->string('dato_fiscal_ciudad', 40)->nullable();
            $table->string('dato_fiscal_estado', 40)->nullable();
            $table->string('dato_fiscal_codigo_postal', 5)->nullable();
            // PORCENTAJE INGRESOS
            $table->decimal('porcentaje_actividad_profesional', 12, 2)->default(0);
            $table->decimal('porcentaje_asalariado', 12, 2)->default(0);
            $table->decimal('porcentaje_pensionado', 12, 2)->default(0);
            $table->decimal('porcentaje_docencia', 12, 2)->default(0);
            // INFORMACION COMPLEMENTARIA
            $table->text('enfermedades')->nullable();
            $table->text('alergias')->nullable();
            $table->string('tipo_sangre', 4)->nullable();
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
        Schema::dropIfExists('curriculum_docentes');
    }
};
