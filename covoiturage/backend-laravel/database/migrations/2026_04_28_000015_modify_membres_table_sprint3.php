<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membres', function (Blueprint $table): void {
            if (! Schema::hasColumn('membres', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('membres', 'account_type')) {
                $table->enum('account_type', ['etudiant', 'professionnel'])->nullable()->after('is_banned');
            }

            if (! Schema::hasColumn('membres', 'carte_etudiante_path')) {
                $table->string('carte_etudiante_path')->nullable()->after('account_type');
            }

            if (! Schema::hasColumn('membres', 'has_verified_documents')) {
                $table->boolean('has_verified_documents')->default(false)->after('carte_etudiante_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('membres', function (Blueprint $table): void {
            if (Schema::hasColumn('membres', 'has_verified_documents')) {
                $table->dropColumn('has_verified_documents');
            }

            if (Schema::hasColumn('membres', 'carte_etudiante_path')) {
                $table->dropColumn('carte_etudiante_path');
            }

            if (Schema::hasColumn('membres', 'account_type')) {
                $table->dropColumn('account_type');
            }

            if (Schema::hasColumn('membres', 'is_banned')) {
                $table->dropColumn('is_banned');
            }
        });
    }
};
