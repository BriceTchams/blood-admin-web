<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  // database/migrations/xxxx_create_hopitals_table.php

public function up(): void
{
    Schema::create('hopitals', function (Blueprint $table) {
        //  Clé primaire UUID
        $table->uuid('id')->primary();

        //  Référence au User (FK polymorphe)
        $table->uuid('user_id')->unique();
        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

        //  Propriétés spécifiques à Hopital
        $table->string('nom'); // Nom de l'hôpital
        $table->string('ville');
        $table->text('adresse')->nullable();
        $table->string('logo')->nullable(); // Chemin du logo
        $table->string('email')->nullable();
        $table->string('telephone_principal')->nullable();

        //  Statut et subscription
        $table->enum('statut', [
            'active',
            'suspended',
            'inactive'
        ])->default('active');

        $table->timestamp('license_expires_at')->nullable();

        // ✅ Sync
        $table->string('code_hopital')->unique(); // Code unique HOSP-001
        $table->boolean('deleted')->default(false);
        $table->enum('sync_statut', [
            'pending',
            'synced',
            'failed'
        ])->default('pending');
        $table->timestamp('last_synced_at')->nullable();

        $table->timestamps();
        $table->softDeletes();

        // Indexes
        $table->index('code_hopital');
        $table->index('statut');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hopitals');
    }
};
