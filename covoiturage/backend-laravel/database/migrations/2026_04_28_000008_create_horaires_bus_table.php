<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horaires_bus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ligne_bus_id')->constrained('lignes_bus')->cascadeOnDelete();
            $table->foreignId('chauffeur_id')->constrained('membres')->cascadeOnDelete();
            $table->time('departure_time');
            $table->json('days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horaires_bus');
    }
};
