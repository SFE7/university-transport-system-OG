<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE membres MODIFY COLUMN role ENUM('membre','conducteur','chauffeur_bus','admin') NOT NULL DEFAULT 'membre'");

        Schema::table('membres', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE membres MODIFY COLUMN role ENUM('membre','conducteur') NOT NULL DEFAULT 'membre'");

        Schema::table('membres', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
