<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trajets', function (Blueprint $table): void {
            $table->string('car_category')->nullable()->after('available_seats');
            $table->string('car_model')->nullable()->after('car_category');
            $table->string('car_photo_url')->nullable()->after('car_model');
        });
    }

    public function down(): void
    {
        Schema::table('trajets', function (Blueprint $table): void {
            $table->dropColumn(['car_category', 'car_model', 'car_photo_url']);
        });
    }
};
