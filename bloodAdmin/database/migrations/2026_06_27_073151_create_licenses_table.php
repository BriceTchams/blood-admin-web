<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hopital_id')->unique()->index();
            
            // Clé simple
            $table->string('license_key')->unique()->index();
            
            // Plan et infos
            $table->enum('plan', ['trial', 'basic', 'premium', 'enterprise'])->default('trial');
            $table->integer('max_users')->default(5);
            
            // Dates
            $table->dateTime('created_at');
            $table->dateTime('expires_at');
            
            // Statut
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            
            $table->foreign('hopital_id')->references('id')->on('hopitals')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};