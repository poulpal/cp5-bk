<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class UpdateModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Deleting all existing modules to start fresh
        Module::query()->delete();

        // 2. Defining the new structure within the single 'modules' table
        $modules = [
            // Base Packages (Plans) stored as modules with type 'base'
            [
                'title' => 'پکیج پایه (تا ۸ واحد)',
                'slug' => 'plan-basic',
                'type' => 'plan',
                'price' => 3900000,
                'description' => 'شامل تمام ماژول‌های اصلی برای ساختمان‌های کوچک',
                'features' => json_encode(['max_units' => 8, 'max_managers' => 2]),
            ],
            [
                'title' => 'پکیج استاندارد (تا ۱۵ واحد)',
                'slug' => 'plan-standard',
                'type' => 'plan',
                'price' => 5900000,
                'description' => 'بهترین انتخاب برای مجتمع‌های متوسط',
                'features' => json_encode(['max_units' => 15, 'max_managers' => 5]),
            ],
            [
                'title' => 'پکیج حرفه‌ای (بیش از ۱۵ واحد)',
                'slug' => 'plan-professional',
                'type' => 'plan',
                'price' => 9900000,
                'description' => 'برای برج‌ها و مجتمع‌های بزرگ با تمام امکانات',
                'features' => json_encode(['max_units' => 1000, 'max_managers' => 10]), // Using a high number for "unlimited"
            ],

            // Core Add-ons (Included for free, but defined as separate modules)
            [
                'title' => 'حسابداری عمومی و پایه',
                'slug' => 'accounting-core',
                'type' => 'core_module',
                'price' => 0,
                'description' => 'شامل انبارداری، رزرو، انتخابات، نظرسنجی و مدیریت مشاعات',
            ],
            
            // Paid Add-on Modules
            [
                'title' => 'حسابداری پیشرفته و QR Code',
                'slug' => 'addon-advanced-accounting',
                'type' => 'addon',
                'price' => 6900000,
                'description' => 'شامل حسابداری پیشرفته و پرداخت سریع با QR Code',
            ],
            [
                'title' => 'پشتیبانی ساکنین (تیکتینگ)',
                'slug' => 'addon-support',
                'type' => 'addon',
                'price' => 1900000,
                'description' => 'سیستم تیکتینگ برای ارتباط مستقیم ساکنین با مدیریت',
            ],
            [
                'title' => 'هوش مصنوعی برای ساکنین',
                'slug' => 'addon-ai-assistant',
                'type' => 'addon',
                'price' => 1900000,
                'description' => 'دستیار هوشمند برای پاسخگویی به سوالات متداول ساکنین',
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }
        
        $this->command->info('The modules and plans structure has been seeded successfully into the modules table!');
    }
}