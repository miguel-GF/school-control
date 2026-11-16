<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id')->index();
            $table->string('gateway', 32);
            $table->string('provider_payment_method_id');
            $table->string('brand', 32)->default('unknown');
            $table->string('last4', 4)->default('');
            $table->unsignedTinyInteger('exp_month')->nullable();
            $table->unsignedSmallInteger('exp_year')->nullable();
            $table->boolean('attached')->default(false);
            $table->timestamps();

            $table->unique(
                ['alumno_id', 'gateway', 'provider_payment_method_id'],
                'payment_methods_alumno_gateway_provider_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
