<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('users', function (Blueprint $table) {
                // ✅ Clé primaire UUID
                $table->uuid('id')->primary();

                // ✅ Type d'utilisateur (Single Table Inheritance)
                $table->string('type')->default('admin'); // 'admin' ou 'hopital'

                // ✅ Propriétés de la classe abstraite Utilisateur
                $table->string('login')->unique();
                $table->string('password_hash'); // Stocker le hash, pas le mot de passe
                $table->string('role')->default('user'); // admin, supervisor, agent
                $table->string('telephone')->nullable();
                $table->string('uuid')->unique()->nullable(); // Sync UUID
                $table->boolean('deleted')->default(false); // Soft delete
                $table->enum('sync_statut', [
                    'pending',
                    'synced',
                    'failed',
                    'conflict'
                ])->default('pending');

                // ✅ Timestamps
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index('type');
                $table->index('login');
                $table->index('sync_statut');
                $table->rememberToken();
            });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
