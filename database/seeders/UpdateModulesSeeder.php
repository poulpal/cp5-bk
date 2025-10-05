<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class UpdateModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // حذف تمام ماژول‌های قبلی
        Module::query()->delete();

        // تعریف ماژول‌های جدید
        $modules = [
            // پکیج‌های اصلی (3 نوع با قیمت)
            [
                'title' => 'پکیج 10',
                'slug' => 'base-10',
                'order' => 1,
                'type' => 'base',
                'price' => 3900000, // 3,900 تومان
                'features' => [
                    'limit' => 10,
                ],
                'description' => 'پکیج پایه مدیریت ساختمان‌های تا 10 واحدی',
            ],
            [
                'title' => 'پکیج 32',
                'slug' => 'base-32',
                'order' => 2,
                'type' => 'base',
                'price' => 5900000, // 5,900 تومان
                'features' => [
                    'limit' => 32,
                ],
                'description' => 'پکیج پایه مدیریت ساختمان‌های 11 تا 32 واحدی',
            ],
            [
                'title' => 'پکیج بی نهایت',
                'slug' => 'base-inf',
                'order' => 3,
                'type' => 'base',
                'price' => 9900000, // 9,900 تومان
                'features' => [
                    'limit' => 500000,
                ],
                'description' => 'پکیج پایه مدیریت ساختمان‌های بزرگ و شهرک',
            ],
            
            // افزودنی‌های رایگان
            [
                'title' => 'حسابداری پایه',
                'slug' => 'accounting-basic',
                'order' => 4,
                'type' => 'extra',
                'price' => 0,
                'description' => 'ثبت سند اتوماتیک، مشاهده اسناد',
            ],
            [
                'title' => 'حسابداری عمومی',
                'slug' => 'accounting-general',
                'order' => 5,
                'type' => 'extra',
                'price' => 0,
                'description' => 'ثبت سند دستی، ایجاد لینک پرداخت',
            ],
            [
                'title' => 'انبارداری',
                'slug' => 'stocks',
                'order' => 6,
                'type' => 'extra',
                'price' => 0,
                'description' => 'انبارداری',
            ],
            [
                'title' => 'نظرسنجی و رزرو',
                'slug' => 'reserve-and-poll',
                'order' => 7,
                'type' => 'extra',
                'price' => 0,
                'description' => 'نظرسنجی، انتخابات، رزرو مشاعات',
            ],
            [
                'title' => 'جریمه و تخفیف',
                'slug' => 'fine-and-reward',
                'order' => 8,
                'type' => 'extra',
                'price' => 0,
                'description' => 'اعمال اتوماتیک جریمه دیرکرد و تخفیف خوشحسابی',
            ],
            
            // حسابداری پیشرفته (با قیمت)
            [
                'title' => 'حسابداری پیشرفته',
                'slug' => 'accounting-advanced',
                'order' => 9,
                'type' => 'accounting',
                'price' => 6900000, // 6,900 تومان
                'description' => 'تغییر کدینگ، گزارش پیشرفته، ترازنامه، صورت سود و زیان',
            ],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }

        $this->command->info('Modules updated successfully!');
    }
}
