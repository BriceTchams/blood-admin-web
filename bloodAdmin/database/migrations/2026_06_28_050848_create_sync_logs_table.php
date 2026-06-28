<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hopital_id')->index();
            
            // Type de synchronisation
            $table->enum('type', ['push', 'pull'])->index();
            
            // Statut
            $table->enum('statut', ['pending', 'success', 'failed', 'partial'])->default('pending');
            
            // Détails
            $table->integer('records_pushed')->default(0);
            $table->integer('records_pulled')->default(0);
            $table->integer('conflicts')->default(0);
            $table->text('error_message')->nullable();
            
            // Timestamps
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            
            // IP et user agent
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->foreign('hopital_id')->references('id')->on('hopitals')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};