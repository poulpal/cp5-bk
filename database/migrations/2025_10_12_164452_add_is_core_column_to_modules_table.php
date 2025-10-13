<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $coreModules = ['accounting', 'stock', 'reserve', 'poll'];
        foreach ($coreModules as $moduleName) {
            // استفاده از Raw SQL برای اطمینان کامل از اجرا
            DB::statement("UPDATE `modules` SET `is_core` = 1 WHERE `name` = '{$moduleName}'");
        }
    }

    public function down()
    {
        // در down، ماژول‌ها را به حالت اولیه برمی‌گردانیم
        $coreModules = ['accounting', 'stock', 'reserve', 'poll'];
        foreach ($coreModules as $moduleName) {
            DB::statement("UPDATE `modules` SET `is_core` = 0 WHERE `name` = '{$moduleName}'");
        }
    }
};