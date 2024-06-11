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
    Schema::table('curriculum_docentes', function (Blueprint $table) {
      $table->text('comentarios')->nullable();
    });
    Schema::table('curriculum_docentes_archivos', function (Blueprint $table) {
      $table->text('descripcion')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('curriculum_docentes', function (Blueprint $table) {
      $table->dropColumn('comentarios');
    });
    Schema::table('curriculum_docentes_archivos', function (Blueprint $table) {
      $table->dropColumn('descripcion');
    });
  }
};
