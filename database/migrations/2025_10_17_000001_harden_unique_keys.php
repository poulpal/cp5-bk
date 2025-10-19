<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** نام ایندکس‌ها/کلیدها که ایجاد می‌کنیم */
    private string $usersMobileUnique = 'users_mobile_unique';
    private string $plansSlugUnique   = 'plans_slug_unique';

    public function up(): void
    {
        // همه چیز در تراکنش تا اتمیک باشد
        DB::transaction(function () {
            $db = config('database.connections.' . config('database.default') . '.database');

            // 1) users.mobile — یکتا کردن امن با رفع خودکار موارد تکراری
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'mobile')) {
                // تشخیص رکوردهای تکراری
                $dups = DB::select("
                    SELECT mobile, COUNT(*) c
                    FROM users
                    WHERE mobile IS NOT NULL AND mobile <> ''
                    GROUP BY mobile
                    HAVING c > 1
                    FOR UPDATE
                ");

                // اگر تکراری هست، غیر اصلی‌ها را اصلاح کن
                foreach ($dups as $row) {
                    $mobile = $row->mobile;
                    // کمترین id را نگه می‌داریم
                    $keepers = DB::table('users')
                        ->where('mobile', $mobile)
                        ->orderBy('id', 'asc')
                        ->pluck('id')
                        ->toArray();

                    $keepId = array_shift($keepers); // اصلی

                    if (!empty($keepers)) {
                        // اگر ستون soft delete داریم، اول رکوردهای soft-deleted را حذف کن
                        $hasDeletedAt = Schema::hasColumn('users', 'deleted_at');
                        if ($hasDeletedAt) {
                            $softDupIds = DB::table('users')
                                ->whereIn('id', $keepers)
                                ->whereNotNull('deleted_at')
                                ->pluck('id')
                                ->toArray();

                            if (!empty($softDupIds)) {
                                DB::table('users')->whereIn('id', $softDupIds)->delete();
                                // غیر نرم‌حذف‌شده‌ها را نگه می‌داریم برای اصلاح موبایل
                                $keepers = array_values(array_diff($keepers, $softDupIds));
                            }
                        }

                        // برای باقی‌مانده‌ها، موبایل را تغییر می‌دهیم تا Unique برقرار شود (audit-friendly)
                        foreach ($keepers as $dupId) {
                            DB::table('users')
                                ->where('id', $dupId)
                                ->update([
                                    'mobile' => DB::raw("CONCAT(mobile, '-DUP-', {$dupId})")
                                ]);
                        }
                    }
                }

                // ایجاد ایندکس یکتا اگر وجود ندارد
                if (!$this->indexExists($db, 'users', $this->usersMobileUnique)) {
                    Schema::table('users', function (Blueprint $table) {
                        $table->unique('mobile', $this->usersMobileUnique);
                    });
                }
            }

            // 2) plans.slug — یکتا کردن
            if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'slug')) {
                // رفع رکوردهای تکراری
                $dups = DB::select("
                    SELECT slug, COUNT(*) c
                    FROM plans
                    WHERE slug IS NOT NULL AND slug <> ''
                    GROUP BY slug
                    HAVING c > 1
                    FOR UPDATE
                ");
                foreach ($dups as $row) {
                    $slug = $row->slug;
                    $ids = DB::table('plans')->where('slug', $slug)->orderBy('id', 'asc')->pluck('id')->toArray();
                    $keepId = array_shift($ids);
                    foreach ($ids as $dupId) {
                        DB::table('plans')->where('id', $dupId)
                            ->update(['slug' => DB::raw("CONCAT(slug, '-dup-', {$dupId})")]);
                    }
                }

                if (!$this->indexExists($db, 'plans', $this->plansSlugUnique)) {
                    Schema::table('plans', function (Blueprint $table) {
                        $table->unique('slug', $this->plansSlugUnique);
                    });
                }
            }
        });
    }

    public function down(): void
    {
        $db = config('database.connections.' . config('database.default') . '.database');

        if (Schema::hasTable('users') && $this->indexExists($db, 'users', $this->usersMobileUnique)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique($this->usersMobileUnique);
            });
        }
        if (Schema::hasTable('plans') && $this->indexExists($db, 'plans', $this->plansSlugUnique)) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropUnique($this->plansSlugUnique);
            });
        }
    }

    /** بررسی وجود ایندکس/کلید */
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
};
