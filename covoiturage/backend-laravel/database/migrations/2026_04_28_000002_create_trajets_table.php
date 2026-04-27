<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trajets', function (Blueprint $table): void {
            $table->id();
            $table->string('departure_point');
            $table->string('arrival_point');
            $table->dateTime('departure_time');
            $table->unsignedInteger('available_seats');
            $table->enum('status', ['active', 'full', 'cancelled', 'completed'])->default('active');
            $table->foreignId('membre_id')->constrained('membres')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trajets');
    }
};
