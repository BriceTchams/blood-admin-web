<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiche_dons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('donneur_id')->index();
            $table->uuid('hopital_id')->index();
            $table->uuid('examen_pre_don_id')->nullable();
            
            $table->string('numero_don')->unique()->index();
            $table->enum('type_donneur', ['benev', 'fam', 'rem'])->default('benev');
            $table->dateTime('date_don');
            $table->date('date_prochain_don')->nullable();
            
            $table->integer('nombre_poches')->default(1);
            $table->integer('volume_preleve_ml')->default(450);
            
            $table->enum('statut', ['en_cours', 'valide', 'annule', 'probleme'])->default('en_cours');
            $table->boolean('deleted')->default(false);
            
            // Sync
            $table->string('sync_statut')->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('donneur_id')->references('id')->on('donneurs')->onDelete('cascade');
            $table->foreign('hopital_id')->references('id')->on('hopitals')->onDelete('cascade');
            $table->foreign('examen_pre_don_id')->references('id')->on('examen_pre_dons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_dons');
    }
};