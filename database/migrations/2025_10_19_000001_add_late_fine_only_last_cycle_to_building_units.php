<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('building_units') && !Schema::hasColumn('building_units', 'late_fine_only_last_cycle')) {
            Schema::table('building_units', function (Blueprint $table) {
                $table->boolean('late_fine_only_last_cycle')->default(false)->after('resident_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('building_units') && Schema::hasColumn('building_units', 'late_fine_only_last_cycle')) {
            Schema::table('building_units', function (Blueprint $table) {
                $table->dropColumn('late_fine_only_last_cycle');
            });
        }
    }
};
