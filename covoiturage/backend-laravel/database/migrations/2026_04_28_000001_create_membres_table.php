<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membres', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['membre', 'conducteur', 'chauffeur_bus', 'admin'])->default('membre');
            $table->string('phone')->nullable();
            $table->boolean('is_banned')->default(false);
            $table->string('account_type')->nullable();
            $table->string('carte_etudiante_path')->nullable();
            $table->boolean('has_verified_documents')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};
