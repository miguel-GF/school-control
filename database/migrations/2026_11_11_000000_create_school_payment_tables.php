<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id');
            $table->string('gateway', 32);
            $table->string('provider_customer_id');
            $table->timestamps();
            $table->unique(['alumno_id', 'gateway']);
            $table->unique(['gateway', 'provider_customer_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('alumno_id')->index();
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);
            $table->string('status', 24)->index();
            $table->string('gateway', 32);
            $table->string('payment_method', 32)->nullable();
            $table->string('description');
            $table->string('provider_payment_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('payment_id')->index();
            $table->string('gateway', 32);
            $table->string('provider_payment_id')->nullable()->index();
            $table->string('status', 24)->index();
            $table->string('failure_code')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_customers');
    }
};
