<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donneurs', function (Blueprint $table) {
            // Rendre uuid nullable
            $table->uuid('uuid')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('donneurs', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->change();
        });
    }
};