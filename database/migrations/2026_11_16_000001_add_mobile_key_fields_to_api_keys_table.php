<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('flavor', 32)->nullable()->after('descripcion');
            $table->string('key_hash', 64)->nullable()->after('key')->index();
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropIndex(['key_hash']);
            $table->dropColumn(['flavor', 'key_hash']);
        });
    }
};
