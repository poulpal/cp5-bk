sudo tee app/Console/Commands/BuildingPurgeCommand.php >/dev/null <<'PHP'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BuildingPurgeCommand extends Command
{
    protected $signature = 'building:purge {building_id} {--dry} {--force} {--chunk=2000}';
    protected $description = 'حذف کامل یک ساختمان با تمام تاریخچه. --dry فقط گزارش، --force برای اجرای واقعی.';

    public function handle(): int
    {
        $id    = (int) $this->argument('building_id');
        $dry   = (bool) $this->option('dry');
        $force = (bool) $this->option('force');
        $chunk = (int) $this->option('chunk') ?: 2000;

        // جدول‌هایی که building_id دارند را پیدا کن
        $tables = $this->tablesWithBuildingId();

        if ($tables->isEmpty()) {
            $this->warn('هیچ جدولی با ستون building_id پیدا نشد.');
        }

        // Dry-run: شمارش رکوردها
        $this->info("Dry report برای ساختمان #{$id}:");
        $counts = [];
        foreach ($tables as $t) {
            $cnt = $this->safeCount($t, $id);
            $counts[$t] = $cnt;
            $this->line(str_pad($t, 40) . " : " . $cnt);
        }
        // خود ساختمان
        $hasBuilding = $this->tableExists('buildings');
        $buildingCnt = $hasBuilding ? (int) DB::table('buildings')->where('id', $id)->count() : 0;
        $this->line(str_pad('buildings', 40) . " : " . $buildingCnt);

        if ($dry || !$force) {
            $this->warn('حالت Dry یا Force=false: حذف انجام نشد.');
            $this->line("برای اجرای واقعی: php artisan building:purge {$id} --force");
            return self::SUCCESS;
        }

        if (!$this->confirm("مطمئن هستی همه‌چیز مربوط به ساختمان #{$id} حذف شود؟")) {
            $this->warn('لغو شد.');
            return self::SUCCESS;
        }

        $this->info("شروع حذف مرحله‌ای (چانک {$chunk})…");

        $deleted = [];

        // چند پاسه: تا وقتی پیشرفت داریم، چرخ بزن
        $progress = true;
        $pass = 0;
        while ($progress) {
            $pass++;
            $progress = false;
            $this->line("--- پاس {$pass} ---");
            foreach ($this->deleteOrder($tables) as $t) {
                $sum = 0;
                while (true) {
                    try {
                        $aff = DB::affectingStatement("DELETE FROM `{$t}` WHERE `building_id` = ? LIMIT {$chunk}", [$id]);
                    } catch (Throwable $e) {
                        $this->error("خطا در حذف از {$t}: ".$e->getMessage());
                        Log::error($e);
                        break; // برو سراغ جدول بعد
                    }
                    if ($aff <= 0) break;
                    $sum += $aff;
                    $progress = true;
                    $this->line("{$t}  -  حذف {$aff} (مجموع: {$sum})");
                }
                if (!empty($sum)) {
                    $deleted[$t] = ($deleted[$t] ?? 0) + $sum;
                }
            }
        }

        // حذف فایل‌ها (storage/app/buildings/{id} و public/buildings/{id})
        try {
            $disk = config('filesystems.default', 'local');
            $p1 = "buildings/{$id}";
            if (Storage::disk($disk)->exists($p1)) {
                Storage::disk($disk)->deleteDirectory($p1);
                $this->line("فایل‌ها روی {$disk} در مسیر {$p1} حذف شد.");
            }
            if (Storage::disk('public')->exists($p1)) {
                Storage::disk('public')->deleteDirectory($p1);
                $this->line("فایل‌های public در مسیر {$p1} حذف شد.");
            }
        } catch (Throwable $e) {
            $this->warn("خطا در حذف فایل‌ها: ".$e->getMessage());
            Log::error($e);
        }

        // در پایان، خود رکورد ساختمان
        if ($hasBuilding) {
            try {
                $aff = DB::table('buildings')->where('id', $id)->delete();
                $this->info("buildings - حذف {$aff} ردیف (خود ساختمان)");
            } catch (Throwable $e) {
                $this->error("خطا در حذف رکورد ساختمان: ".$e->getMessage());
                Log::error($e);
            }
        }

        $this->info('پایان عملیات.');
        return self::SUCCESS;
    }

    /** جداولی که ستون building_id دارند. */
    protected function tablesWithBuildingId(): Collection
    {
        $rows = DB::select("
            SELECT TABLE_NAME as name
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND COLUMN_NAME = 'building_id'
        ");
        return collect($rows)->pluck('name')->unique()->filter(function ($t) {
            return $t !== 'buildings'; // خود ساختمان را آخر حذف می‌کنیم
        })->values();
    }

    /** شمارش ایمن */
    protected function safeCount(string $table, int $buildingId): int
    {
        try {
            return (int) DB::table($table)->where('building_id', $buildingId)->count();
        } catch (Throwable $e) {
            Log::error($e);
            return -1; // قابل شمارش نبود
        }
    }

    /** ترتیب حذف پیشنهادی: جدول‌های جزئی/Log/Item اول */
    protected function deleteOrder(Collection $tables): Collection
    {
        $priority = [
            '_items', '_item', '_lines', '_line', '_logs', '_log', '_details', '_detail',
            'pivot', 'media', 'files', 'notifications', 'payments', 'invoices', 'charges',
            'reservations', 'tickets', 'posts', 'units', 'residents',
        ];

        return $tables->sort(function ($a, $b) use ($priority) {
            $pa = $this->score($a, $priority);
            $pb = $this->score($b, $priority);
            return $pa <=> $pb;
        });
    }

    protected function score(string $name, array $priority): int
    {
        foreach ($priority as $i => $needle) {
            if (str_contains($name, $needle)) return $i;
        }
        return PHP_INT_MAX;
    }

    protected function tableExists(string $name): bool
    {
        try {
            DB::select("SELECT 1 FROM `{$name}` LIMIT 1");
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
PHP
