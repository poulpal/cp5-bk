<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $fkPoll   = 'poll_votes_poll_id_foreign';
    private string $fkUnit   = 'poll_votes_building_unit_id_foreign';
    private string $fkUser   = 'poll_votes_user_id_foreign';

    private array $idx = [
        'poll_votes_poll_id_index'          => ['table' => 'poll_votes', 'column' => 'poll_id'],
        'poll_votes_building_unit_id_index' => ['table' => 'poll_votes', 'column' => 'building_unit_id'],
        'poll_votes_user_id_index'          => ['table' => 'poll_votes', 'column' => 'user_id'],
    ];

    public function up(): void
    {
        $db = config('database.connections.' . config('database.default') . '.database');

        if (!Schema::hasTable('poll_votes')) return;

        // 1) ایندکس‌ها (برای کارایی و نیاز FK)
        foreach ($this->idx as $indexName => $meta) {
            if (Schema::hasColumn($meta['table'], $meta['column']) && !$this->indexExists($db, $meta['table'], $indexName)) {
                Schema::table($meta['table'], function (Blueprint $table) use ($meta, $indexName) {
                    $table->index($meta['column'], $indexName);
                });
            }
        }

        // 2) کلیدهای خارجی
        Schema::table('poll_votes', function (Blueprint $table) use ($db) {
            // به poll_id → polls.id
            if (Schema::hasColumn('poll_votes', 'poll_id') && Schema::hasTable('polls')) {
                $this->addForeignIfMissing($table, 'poll_votes', 'poll_id', 'polls', 'id', $this->fkPoll);
            }
            // به building_unit_id → building_units.id
            if (Schema::hasColumn('poll_votes', 'building_unit_id') && Schema::hasTable('building_units')) {
                $this->addForeignIfMissing($table, 'poll_votes', 'building_unit_id', 'building_units', 'id', $this->fkUnit);
            }
            // به user_id → users.id
            if (Schema::hasColumn('poll_votes', 'user_id') && Schema::hasTable('users')) {
                $this->addForeignIfMissing($table, 'poll_votes', 'user_id', 'users', 'id', $this->fkUser);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('poll_votes')) return;

        // حذف FK‌ها اگر وجود دارند
        Schema::table('poll_votes', function (Blueprint $table) {
            $this->dropForeignIfExists($table, $this->fkPoll);
            $this->dropForeignIfExists($table, $this->fkUnit);
            $this->dropForeignIfExists($table, $this->fkUser);
        });

        // ایندکس‌ها را (در صورت تمایل) حذف نکنیم تا کارایی حفظ شود
        // اگر خواستی حذف شود، این کامنت را باز کن:
        // Schema::table('poll_votes', function (Blueprint $table) {
        //   $table->dropIndex('poll_votes_poll_id_index');
        //   $table->dropIndex('poll_votes_building_unit_id_index');
        //   $table->dropIndex('poll_votes_user_id_index');
        // });
    }

    /** Helpers */

    private function indexExists(string $db, string $table, string $index): bool
    {
        $q = "
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?
            LIMIT 1
        ";
        $r = DB::select($q, [$db, $table, $index]);
        return !empty($r);
    }

    private function foreignExists(string $table, string $fkName): bool
    {
        $q = "
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = ?
            LIMIT 1
        ";
        $db = config('database.connections.' . config('database.default') . '.database');
        $r  = DB::select($q, [$db, $table, $fkName]);
        return !empty($r);
    }

    private function addForeignIfMissing(Blueprint $table, string $fromTable, string $fromColumn, string $toTable, string $toColumn, string $fkName): void
    {
        if ($this->foreignExists($fromTable, $fkName)) return;

        // توجه: اگر داده‌ی یتیم وجود داشته باشد، اضافه کردن FK خطا می‌دهد.
        // به همین خاطر قبلش orphanها را پاک/اصلاح می‌کنیم:
        DB::statement("
            DELETE pv FROM {$fromTable} pv
            LEFT JOIN {$toTable} t ON pv.{$fromColumn} = t.{$toColumn}
            WHERE t.{$toColumn} IS NULL AND pv.{$fromColumn} IS NOT NULL
        ");

        $table->foreign($fromColumn, $fkName)->references($toColumn)->on($toTable)->cascadeOnDelete();
    }

    private function dropForeignIfExists(Blueprint $table, string $fkName): void
    {
        try {
            $table->dropForeign($fkName);
        } catch (\Throwable $e) {
            // نادیده بگیر اگر وجود نداشت
        }
    }
};
