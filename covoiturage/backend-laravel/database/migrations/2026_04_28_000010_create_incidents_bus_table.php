<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents_bus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ligne_bus_id')->constrained('lignes_bus')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('membres')->cascadeOnDelete();
            $table->enum('type', ['delay', 'breakdown', 'cancelled', 'other']);
            $table->text('description');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents_bus');
    }
};
