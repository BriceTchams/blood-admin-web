<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groupe_sanguins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();

            // A, B, AB, O
            $table->enum('libelle', ['A', 'B', 'AB', 'O']);
            // + ou -
            $table->enum('rhesus', ['+', '-']);

            $table->boolean('deleted')->default(false);
            $table->string('sync_statut')->default('pending'); // pending | synced | conflict

            $table->timestamps();

            $table->unique(['libelle', 'rhesus']); // un seul A+, un seul O-, etc.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groupe_sanguins');
    }
};
