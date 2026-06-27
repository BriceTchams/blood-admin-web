<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_pre_dons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('donneur_id')->index();
            $table->dateTime('date_examen');
            $table->float('poids')->nullable();
            $table->float('taille')->nullable();
            $table->string('tension_arterielle')->nullable();
            $table->integer('pouls')->nullable();
            $table->float('hemoglobine')->nullable();
            $table->string('groupe_sanguin_rh')->nullable();
            
            // Tests sérologiques
            $table->boolean('hbv5')->default(false);
            $table->boolean('hcv')->default(false);
            $table->boolean('hiv')->default(false);
            $table->boolean('tpha')->default(false);
            $table->boolean('tdr_palu')->default(false);
            
            // Décision
            $table->text('avis_responsable')->nullable();
            $table->boolean('autorise')->default(false);
            $table->date('date_prochaine_visite')->nullable();
            $table->text('remarques')->nullable();
            
            // Sync & soft delete
            $table->string('sync_statut')->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('donneur_id')->references('id')->on('donneurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_pre_dons');
    }
};