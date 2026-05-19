<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table): void {
            $table->string('photo_url')->nullable()->after('couleur');
        });
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table): void {
            $table->dropColumn('photo_url');
        });
    }
};
