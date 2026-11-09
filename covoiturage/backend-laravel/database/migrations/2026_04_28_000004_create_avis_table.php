<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('membres')->cascadeOnDelete();
            $table->foreignId('conducteur_id')->constrained('membres')->cascadeOnDelete();
            $table->foreignId('trajet_id')->constrained('trajets')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
