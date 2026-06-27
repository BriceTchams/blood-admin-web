<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_post_dons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fiche_don_id')->index();
            $table->uuid('donneur_id')->index();
            
            $table->dateTime('date_examen');
            
            // ELISA résultats (DO = Densité Optique, VS = Valeur Seuil)
            $table->float('do_tpha')->nullable();
            $table->float('vs_tpha')->nullable();
            $table->float('do_hiv')->nullable();
            $table->float('vs_hiv')->nullable();
            $table->float('do_hbsag')->nullable();
            $table->float('vs_hbsag')->nullable();
            $table->float('do_hcv')->nullable();
            $table->float('vs_hcv')->nullable();
            
            $table->string('interpretation_elisa')->nullable();
            
            // Groupage et rh
            $table->string('groupe_sanguin')->nullable();
            $table->enum('rhesus', ['+', '-'])->nullable();
            
            // Coombs et biologie
            $table->boolean('coombs_direct')->default(false);
            $table->boolean('coombs_indirect')->default(false);
            $table->string('electrophorese_hb')->nullable();
            $table->boolean('vdrl')->default(false);
            
            // Statut final
            $table->enum('statut_final', ['conforme', 'non_conforme', 'en_attente', 'probleme'])->default('en_attente');
            
            // Sync
            $table->string('sync_statut')->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('fiche_don_id')->references('id')->on('fiche_dons')->onDelete('cascade');
            $table->foreign('donneur_id')->references('id')->on('donneurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_post_dons');
    }
};