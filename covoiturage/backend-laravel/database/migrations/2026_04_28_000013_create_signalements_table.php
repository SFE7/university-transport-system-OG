<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signalements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reporter_id')->constrained('membres')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('membres')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['en_attente', 'traite', 'archive'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signalements');
    }
};
