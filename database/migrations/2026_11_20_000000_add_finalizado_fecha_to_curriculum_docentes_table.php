<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Fecha en la que el docente envio su curriculum a revision.
   * A partir de ese momento el expediente queda bloqueado para el docente.
   */
  public function up(): void
  {
    if (!Schema::hasColumn('curriculum_docentes', 'finalizado_fecha')) {
      Schema::table('curriculum_docentes', function (Blueprint $table) {
        $table->timestamp('finalizado_fecha')->nullable()->after('status');
      });
    }
  }

  public function down(): void
  {
    if (Schema::hasColumn('curriculum_docentes', 'finalizado_fecha')) {
      Schema::table('curriculum_docentes', function (Blueprint $table) {
        $table->dropColumn('finalizado_fecha');
      });
    }
  }
};
