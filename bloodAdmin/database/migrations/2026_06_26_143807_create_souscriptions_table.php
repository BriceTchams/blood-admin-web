<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('souscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hopital_id')->unique()->index();
            
            // Plan et tarification
            $table->enum('plan', ['trial', 'basic', 'premium', 'enterprise'])->default('trial');
            $table->enum('billing_period', ['monthly', 'yearly'])->default('yearly');
            
            // Dates
            $table->dateTime('date_souscription');
            $table->dateTime('date_expiration');
            $table->dateTime('date_renouvellement')->nullable();
            
            // Statut
            $table->enum('statut', ['active', 'inactive', 'suspended', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            
            // Sync
            $table->string('sync_statut')->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('hopital_id')->references('id')->on('hopitals')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('souscriptions');
    }
};