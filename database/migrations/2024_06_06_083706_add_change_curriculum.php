<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('curriculum_docentes_archivos', function (Blueprint $table) {
      DB::statement('ALTER TABLE curriculum_docentes_archivos MODIFY archivo LONGBLOB');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('curriculum_docentes_archivos', function (Blueprint $table) {
      DB::statement('ALTER TABLE curriculum_docentes_archivos MODIFY archivo BLOB');
    });
  }
};
