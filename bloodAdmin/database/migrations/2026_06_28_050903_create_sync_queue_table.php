<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hopital_id')->index();
            
            // Type de données
            $table->string('table_name')->index();
            $table->uuid('record_id')->index();
            
            // Opération
            $table->enum('operation', ['create', 'update', 'delete'])->index();
            
            // Données
            $table->longText('payload')->nullable();
            
            // Statut
            $table->enum('statut', ['pending', 'synced', 'failed', 'conflict'])->default('pending')->index();
            $table->integer('attempt_count')->default(0);
            
            // Timestamps
            $table->timestamp('queued_at');
            $table->timestamp('synced_at')->nullable();
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            $table->foreign('hopital_id')->references('id')->on('hopitals')->onDelete('cascade');
            
            $table->index(['hopital_id', 'statut', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queues');
    }
};