<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donneurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();

            // Le module Hôpital n'est pas encore implémenté : on garde la colonne en simple
            // uuid (nullable) sans contrainte de clé étrangère stricte pour ne pas bloquer
            // ce module ; la FK pourra être ajoutée quand `hopitaux` existera.
            $table->uuid('hopital_id')->index();

                    $table->foreignUuid('groupe_sanguin_id')
                ->nullable()
                ->constrained('groupe_sanguins')
                ->nullOnDelete();

            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->float('poids')->nullable(); // en kg, utile pour les critères de don
            $table->string('telephone');        // utilisé pour les SMS J-3 (cf. guide §2.3)
            $table->string('email')->nullable();

            // actif | inactif | suspendu | inéligible ...
            $table->string('statut')->default('actif');

            $table->boolean('deleted')->default(false);
            $table->string('sync_statut')->default('pending');

            $table->timestamps();

            $table->index(['nom', 'prenom']);
            $table->unique('telephone');
            $table->string('image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donneurs');
    }
};
