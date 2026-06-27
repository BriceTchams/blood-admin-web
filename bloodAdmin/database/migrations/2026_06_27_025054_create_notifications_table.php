<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();

            // Donneur 1 ──► 0..* Notification : reçoit
            $table->foreignUuid('donneur_id')
                ->constrained('donneurs')
                ->cascadeOnDelete();

            // Hopital 1 ──► 0..* Notification : envoie
            // Module Hôpital pas encore livré : pas de contrainte FK stricte pour le moment
            // (même traitement que donneurs.hopital_id), à durcir dès que `hopitaux` existera.
            $table->uuid('hopital_id')->nullable()->index();

            $table->text('message');
            $table->dateTime('date_envoi')->nullable(); // renseigné par envoyer()

            // lu | non_lu
            $table->enum('statut', ['lu', 'non_lu'])->default('non_lu');

            $table->boolean('deleted')->default(false);
            $table->string('sync_statut')->default('pending');

            $table->timestamps();

            $table->index(['donneur_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
