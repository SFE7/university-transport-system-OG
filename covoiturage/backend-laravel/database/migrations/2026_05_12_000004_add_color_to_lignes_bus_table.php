<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lignes_bus', function (Blueprint $table): void {
            $table->string('color')->nullable()->default('#00c853')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('lignes_bus', function (Blueprint $table): void {
            $table->dropColumn('color');
        });
    }
};
