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
        Schema::table('signalements', function (Blueprint $table): void {
            $table->foreignId('conducteur_id')->nullable()->after('reported_id')->constrained('membres')->nullOnDelete();
            $table->foreignId('trajet_id')->nullable()->after('conducteur_id')->constrained('trajets')->nullOnDelete();
            $table->text('description')->nullable()->after('reason');
            $table->unique(['reporter_id', 'conducteur_id', 'trajet_id'], 'signalements_reporter_conducteur_trajet_unique');
        });

        DB::table('signalements')
            ->whereNull('conducteur_id')
            ->update(['conducteur_id' => DB::raw('reported_id')]);
    }

    public function down(): void
    {
        Schema::table('signalements', function (Blueprint $table): void {
            $table->dropUnique('signalements_reporter_conducteur_trajet_unique');
            $table->dropConstrainedForeignId('trajet_id');
            $table->dropConstrainedForeignId('conducteur_id');
            $table->dropColumn('description');
        });
    }
};
