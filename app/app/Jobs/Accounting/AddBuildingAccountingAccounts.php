<?php

namespace App\Jobs\Accounting;

use App\Models\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddBuildingAccountingAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public $building_id
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $building = Building::find($this->building_id);
        if (!$building) {
            return;
        }

        $accounts_array = [
            [
                "name" => "داراییهای جاری",
                "code" => 1,
                "type" => "debit",
                "children" => [
                    [
                        "name" => "سفارشات وپیش پرداختها",
                        "code" => 11,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سفارشات مواد اولیه",
                                "code" => 1101,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سفارشات قطعات و لوازم یدکی",
                                "code" => 1102,
                                "type" => "debit"
                            ],
                            [
                                "name" => "پیش پرداخت خریدکاال وخدمات",
                                "code" => 1103,
                                "type" => "debit"
                            ],
                            [
                                "name" => "پیش پرداخت بیمه",
                                "code" => 1104,
                                "type" => "debit"
                            ],
                            [
                                "name" => "پیش پرداخت مالیات بردرآمد",
                                "code" => 1105,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر پیش پرداختها",
                                "code" => 1106,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "موجودی موادوکالا",
                        "code" => 12,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "موجودی کالای ساخته شده",
                                "code" => 1201,
                                "type" => "debit"
                            ],
                            [
                                "name" => "کالای درجریان ساخت",
                                "code" => 1202,
                                "type" => "debit"
                            ],
                            [
                                "name" => "مواد اولیه و بسته بندی",
                                "code" => 1203,
                                "type" => "debit"
                            ],
                            [
                                "name" => "قطعات و لوازم یدکی",
                                "code" => 1204,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر موجودیها",
                                "code" => 1205,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ذخیره کاهش ارزش موجودیهای جنسی",
                                "code" => 1299,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "دریافتنی های تجاری",
                        "code" => 13,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "اسناد دریافتنی نزد صندوق",
                                "code" => 1301,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اسناد دریافتنی نزد بانكها",
                                "code" => 1302,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اسناد درجریان وصول نزد بانكها",
                                "code" => 1303,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حسابهای دریافتنی تجاری - شرکتهاوموسسات وسازمانها",
                                "code" => 1304,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حسابهای دریافتنی تجاری - اشخاص حقیقی",
                                "code" => 1305,
                                "type" => "debit"
                            ],
                            [
                                "name" => "چكهای برگشتی مشتریان",
                                "code" => 1306,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ذخیره کاهش ارزش دریافتنی های تجاری",
                                "code" => 1307,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایردریافتنی ها",
                        "code" => 14,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "حسابهای دریافتنی غیر تجاری - شرکتهاوموسسات وسازمانها",
                                "code" => 1401,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حسابهای دریافتنی غیر تجاری - اشخاص حقیقی",
                                "code" => 1402,
                                "type" => "debit"
                            ],
                            [
                                "name" => "وام کارکنان",
                                "code" => 1403,
                                "type" => "debit"
                            ],
                            [
                                "name" => "مساعده کارکنان",
                                "code" => 1404,
                                "type" => "debit"
                            ],
                            [
                                "name" => "مالیات برارزش افزوده",
                                "code" => 1405,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سود سهام دریافتنی",
                                "code" => 1406,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده اجاره",
                                "code" => 1407,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده اخذ ضمانتنامه بانكی",
                                "code" => 1408,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده های موقت",
                                "code" => 1409,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر بدهكاران",
                                "code" => 1410,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ذخیره کاهش ارزش دریافتنی های غیر تجاری",
                                "code" => 1499,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سرمایه گذاری کوتاه مدت",
                        "code" => 15,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سهام شرکتهای پذیرفته شده دربورس وفرابورس",
                                "code" => 1501,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر اوراق بهادار",
                                "code" => 1502,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سرمایه گذاری درسهام شرکتها",
                                "code" => 1503,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سرمایه گذاری دراوراق بهادار",
                                "code" => 1504,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده سرمایه گذاری کوتاه مدت نزد بانكها",
                                "code" => 1505,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ذخیره کاهش ارزش سرمایه گذاری ها",
                                "code" => 1599,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "موجودی نقد",
                        "code" => 16,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "موجودی نزد بانكها - ریالی",
                                "code" => 1601,
                                "type" => "debit"
                            ],
                            [
                                "name" => "موجودی نزد بانكها - ارزی",
                                "code" => 1602,
                                "type" => "debit"
                            ],
                            [
                                "name" => "موجودی صندوق ریالی",
                                "code" => 1603,
                                "type" => "debit"
                            ],
                            [
                                "name" => "موجودی صندوق ارزی",
                                "code" => 1604,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تنخواه گردانها",
                                "code" => 1605,
                                "type" => "debit"
                            ],
                            [
                                "name" => "وجوه در راه",
                                "code" => 1606,
                                "type" => "debit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "داراییهای غیرجاری",
                "code" => 2,
                "type" => "debit",
                "children" => [
                    [
                        "name" => "داراییهای ثابت مشهود",
                        "code" => 21,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "زمین",
                                "code" => 2101,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ساختمان",
                                "code" => 2102,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تاسیسات",
                                "code" => 2103,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ماشین آلات وتجهیزات",
                                "code" => 2104,
                                "type" => "debit"
                            ],
                            [
                                "name" => "وسایل نقلیه",
                                "code" => 2105,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اثاثه ومنصوبات",
                                "code" => 2106,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ابزار آلات کارگاهی",
                                "code" => 2107,
                                "type" => "debit"
                            ],
                            [
                                "name" => "داراییهای درجریان ساخت",
                                "code" => 2109,
                                "type" => "debit"
                            ],
                            [
                                "name" => "استهلاک انباشته ساختمان",
                                "code" => 2122,
                                "type" => "credit"
                            ],
                            [
                                "name" => "استهلاک انباشته تاسیسات",
                                "code" => 2123,
                                "type" => "credit"
                            ],
                            [
                                "name" => "استهلاک انباشته ماشین آلات وتجهیزات",
                                "code" => 2124,
                                "type" => "credit"
                            ],
                            [
                                "name" => "استهلاک انباشته وسایل نقلیه",
                                "code" => 2125,
                                "type" => "credit"
                            ],
                            [
                                "name" => "استهلاک انباشته اثاثیه ومنصوبات",
                                "code" => 2126,
                                "type" => "credit"
                            ],
                            [
                                "name" => "استهلاک انباشته ابزارآلات کارگاهی",
                                "code" => 2127,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سرمایه گذاری در املاک",
                        "code" => 22,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سرمایه گذاری در املاک",
                                "code" => 2201,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "دارائیهای نامشهود",
                        "code" => 23,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "حق امتیاز برق",
                                "code" => 2301,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق امتیاز تلفن",
                                "code" => 2302,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق امتیاز گاز",
                                "code" => 2303,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق امتیاز آب",
                                "code" => 2304,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سرقفلی",
                                "code" => 2305,
                                "type" => "debit"
                            ],
                            [
                                "name" => "دانش فنی",
                                "code" => 2306,
                                "type" => "debit"
                            ],
                            [
                                "name" => "نرم افزارهای رایانه ای",
                                "code" => 2307,
                                "type" => "debit"
                            ],
                            [
                                "name" => "استهلاک انباشته نرم افزار",
                                "code" => 2311,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سرمایه گذاریهای بلند مدت",
                        "code" => 24,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سرمایه گذاری در سهام شرکتها",
                                "code" => 2401,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سرمایه گذاری در سایر اوراق بهادار",
                                "code" => 2402,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده سرمایه گذاری بلند مدت بانكی",
                                "code" => 2403,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "دریافتنی های بلند مدت",
                        "code" => 25,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "اسناددریافتنی تجاری",
                                "code" => 2501,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حسابهای دریافتنی تجاری",
                                "code" => 2502,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر دریافتنی ها (اسناد دریافتنی)",
                                "code" => 2503,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر دریافتنی ها (حسابهای دریافتنی)",
                                "code" => 2504,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ذخیره کاهش ارزش دریافتنی ها",
                                "code" => 2599,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایر داراییها",
                        "code" => 26,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "وجوه بانكی مسدود شده",
                                "code" => 2601,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سپرده نزد صندوق دادگستری",
                                "code" => 2602,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سایر داراییها",
                                "code" => 2603,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه های انتقالی به دوره های آتی",
                                "code" => 2604,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ترازافتتاحیه واختتامیه",
                                "code" => 2901,
                                "type" => "both"
                            ]
                        ]
                    ],
                    [
                        "name" => "دارایی های غیر تجاری نگهداری شده برای فروش",
                        "code" => 27,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "زمین و ساختمان انبار",
                                "code" => 2701,
                                "type" => "debit"
                            ],
                            [
                                "name" => "دارایی های مرتبط با کارخانه تولید محصولات",
                                "code" => 2702,
                                "type" => "debit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "حقوق مالکانه",
                "code" => 3,
                "type" => "credit",
                "children" => [
                    [
                        "name" => "سرمایه",
                        "code" => 30,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "سرمایه",
                                "code" => 3001,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "افزایش سرمایه درجریان",
                        "code" => 31,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "افزایش سرمایه از محل مطالبات سهامداران",
                                "code" => 3101,
                                "type" => "credit"
                            ],
                            [
                                "name" => "افزایش سرمایه از محل آورده نقدی سهامداران",
                                "code" => 3102,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "صرف سهام و صرف سهام خزانه",
                        "code" => 32,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "صرف سهام افزایش سرمایه96",
                                "code" => 3201,
                                "type" => "credit"
                            ],
                            [
                                "name" => "صرف سهام افزایش سرمایه97",
                                "code" => 3202,
                                "type" => "credit"
                            ],
                            [
                                "name" => "صرف سهام خزانه",
                                "code" => 3203,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "اندوخته قانونی",
                        "code" => 33,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "اندوخته قانونی",
                                "code" => 3301,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایراندوخته ها",
                        "code" => 34,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "اندوخته عمومی",
                                "code" => 3401,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اندوخته طرح توسعه",
                                "code" => 3402,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "مازاد تجدید ارزیابی دارایی ها",
                        "code" => 35,
                        "type" => "credit"
                    ],
                    [
                        "name" => "تفاوت تسعیر ارز عملیات خارجی",
                        "code" => 36,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "تفاوت تسعیر ارز عملیات خارجی در کشور ...",
                                "code" => 3601,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سود انباشته",
                        "code" => 37,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "سودوزیان انباشته",
                                "code" => 3701,
                                "type" => "credit"
                            ],
                            [
                                "name" => "تعدیالت سنواتی طی دوره",
                                "code" => 3702,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "عملکرد سودوزیان",
                        "code" => 38,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "حساب عملكرد فروش",
                                "code" => 3801,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حساب عملكرد خدمات کارمزدی",
                                "code" => 3802,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حساب سودوزیان جاری",
                                "code" => 3803,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سهام خزانه",
                        "code" => 39,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سهام خزانه",
                                "code" => 3901,
                                "type" => "debit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "بدهیهای بلند مدت",
                "code" => 4,
                "type" => "credit",
                "children" => [
                    [
                        "name" => "پرداختنی های بلند مدت",
                        "code" => 41,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "اسناد پرداختنی تجاری",
                                "code" => 4101,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حسابهای پرداختنی تجاری",
                                "code" => 4102,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر پرداختنی ها (اسناد پرداختنی)",
                                "code" => 4103,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر پرداختنی ها (حسابهای پرداختنی)",
                                "code" => 4104,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "تسهیلات مالی بلند مدت",
                        "code" => 42,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "تسهیلات دریافتی از بانكها",
                                "code" => 4201,
                                "type" => "credit"
                            ],
                            [
                                "name" => "انتشاراوراق مشارکت با نرخ %20",
                                "code" => 4202,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اوراق خرید دین ( تنزیل اسناد دریافتنی )",
                                "code" => 4203,
                                "type" => "credit"
                            ],
                            [
                                "name" => "تعهدات اجاره سرمایه ای",
                                "code" => 4204,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "ذخیره مزایای پایان خدمت",
                        "code" => 43,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "ذخیره مزایای پایان خدمت کارکنان",
                                "code" => 4301,
                                "type" => "credit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "بدهیهای جاری",
                "code" => 5,
                "type" => "credit",
                "children" => [
                    [
                        "name" => "پرداختنی های تجاری",
                        "code" => 51,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "اسنادپرداختنی ریالی",
                                "code" => 5101,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حسابهای پرداختنی تجاری -شرکتهاوموسسات ،سازمانها",
                                "code" => 5102,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حسابهای پرداختنی تجاری -اشخاص حقیقی",
                                "code" => 5103,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حساب معلق خرید",
                                "code" => 5104,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایر پرداختنی ها",
                        "code" => 52,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "حسابهای پرداختنی غیر تجاری - شرکت وموسسات وسازمانها",
                                "code" => 5201,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حسابهای پرداختنی غیر تجاری - اشخاص حقیقی",
                                "code" => 5202,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حقوق ودستمزد پرداختنی",
                                "code" => 5203,
                                "type" => "credit"
                            ],
                            [
                                "name" => "حق بیمه پرداختنی",
                                "code" => 5204,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اداره امورمالیاتی(مالیات حقوق )",
                                "code" => 5205,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اداره امورمالیاتی(مالیات تكلیفی )",
                                "code" => 5206,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اداره امورمالیاتی (مالیات برارزش افزوده )",
                                "code" => 5207,
                                "type" => "credit"
                            ],
                            [
                                "name" => "جاری شرکاء/سهامداران",
                                "code" => 5208,
                                "type" => "credit"
                            ],
                            [
                                "name" => "ذخیره هزینه های تحقق یافته پرداخت نشده",
                                "code" => 5209,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سپرده های دریافتی از دیگران",
                                "code" => 5210,
                                "type" => "credit"
                            ],
                            [
                                "name" => "رند حقوق",
                                "code" => 5211,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "مالیات پرداختنی",
                        "code" => 53,
                        "type" => "credit"
                    ],
                    [
                        "name" => "سود سهام پرداختنی",
                        "code" => 54,
                        "type" => "credit"
                    ],
                    [
                        "name" => "تسهیلات مالی جاری",
                        "code" => 55,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "تسهیلات دریافتی از بانكها",
                                "code" => 5501,
                                "type" => "credit"
                            ],
                            [
                                "name" => "تسهیلات دریافتی از اشخاص",
                                "code" => 5502,
                                "type" => "credit"
                            ],
                            [
                                "name" => "انتشاراوراق مشارکت با نرخ %20",
                                "code" => 5503,
                                "type" => "credit"
                            ],
                            [
                                "name" => "اوراق خرید دین ( تنزیل اسناد دریافتنی )",
                                "code" => 5504,
                                "type" => "credit"
                            ],
                            [
                                "name" => "تعهدات اجاره سرمایه ای",
                                "code" => 5505,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "ذخایر",
                        "code" => 56,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "ذخیره تضمین محصولات",
                                "code" => 5601,
                                "type" => "credit"
                            ],
                            [
                                "name" => "ذخیره قرار دادهای زیان بار",
                                "code" => 5602,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر ذخایر",
                                "code" => 5603,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "پیش دریافتها",
                        "code" => 57,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "پیش دریافت از اشخاص و شرکتها",
                                "code" => 5701,
                                "type" => "credit"
                            ],
                            [
                                "name" => "پیش دریافت از نمایندگی های فروش",
                                "code" => 5702,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر پیش دریافتها",
                                "code" => 5703,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "بدهی های مرتبط با دارایی های غیر جاری نگهداری شده برای فروش",
                        "code" => 58,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "بدهی های مرتبط با دارایی های غیر جاری نگهداری شده برای فروش",
                                "code" => 5801,
                                "type" => "credit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "درآمدها",
                "code" => 6,
                "type" => "credit",
                "children" => [
                    [
                        "name" => "درآمدهای عملیاتی",
                        "code" => 61,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "فروش محصول/ درآمد خدمات",
                                "code" => 6101,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر فروش",
                                "code" => 6102,
                                "type" => "credit"
                            ],
                            [
                                "name" => "درآمد حاصل از شارژ",
                                "code" => 6103,
                                "type" => "credit"
                            ],
                            [
                                "name" => "فروش صادراتی",
                                "code" => 6106,
                                "type" => "credit"
                            ],
                            [
                                "name" => "برگشت ازفروش",
                                "code" => 6111,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تخفیفات فروش",
                                "code" => 6112,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "درآمد ارائه خدمات",
                        "code" => 62,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "خدمات کارمزدی",
                                "code" => 6201,
                                "type" => "credit"
                            ],
                            [
                                "name" => "خدمات بسته بندی",
                                "code" => 6202,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر درآمد خدماتی",
                                "code" => 6203,
                                "type" => "credit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "هزینه ها",
                "code" => 7,
                "type" => "debit",
                "children" => [
                    [
                        "name" => "هزینه حقوق ودستمزد",
                        "code" => 71,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "حقوق پایه",
                                "code" => 7101,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اضافه کاری",
                                "code" => 7102,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق مسكن",
                                "code" => 7103,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق اولاد",
                                "code" => 7104,
                                "type" => "debit"
                            ],
                            [
                                "name" => "کمک هزینه اقالم مصرفی خانوار ( بن کالای سابق)",
                                "code" => 7105,
                                "type" => "debit"
                            ],
                            [
                                "name" => "نوبت کاری وشبكاری",
                                "code" => 7106,
                                "type" => "debit"
                            ],
                            [
                                "name" => "23%حق بیمه سهم کارفرما",
                                "code" => 7107,
                                "type" => "debit"
                            ],
                            [
                                "name" => "عیدی و پاداش",
                                "code" => 7108,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق الجذب",
                                "code" => 7109,
                                "type" => "debit"
                            ],
                            [
                                "name" => "بازخرید مرخصی استفاده نشده",
                                "code" => 7110,
                                "type" => "debit"
                            ],
                            [
                                "name" => "مزایای پایان خدمت کارکنان (سنوات پایان خدمت )",
                                "code" => 7111,
                                "type" => "debit"
                            ],
                            [
                                "name" => "کمكهای غیر نقدی",
                                "code" => 7112,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "هزینه های عملیاتی",
                        "code" => 72,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "مواداولیه مصرفی تولید",
                                "code" => 7201,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تعمیرو نگهداری ساختمان وتاسیسات",
                                "code" => 7202,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تعمیرونگهداری ماشین آلات وتجهیزات",
                                "code" => 7203,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تعمیرونگهداری وسایل نقلیه",
                                "code" => 7204,
                                "type" => "debit"
                            ],
                            [
                                "name" => "تعمیرونگهداری اثاثیه ومنصوبات",
                                "code" => 7205,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سوخت و انرژی",
                                "code" => 7206,
                                "type" => "debit"
                            ],
                            [
                                "name" => "آب ،برق،گاز،تلفن",
                                "code" => 7207,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ملزومات و نوشت افزارو آگهی",
                                "code" => 7208,
                                "type" => "debit"
                            ],
                            [
                                "name" => "آبدارخانه",
                                "code" => 7209,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حمل ونقل",
                                "code" => 7210,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اجاره مكان",
                                "code" => 7211,
                                "type" => "debit"
                            ],
                            [
                                "name" => "بیمه داراییها",
                                "code" => 7212,
                                "type" => "debit"
                            ],
                            [
                                "name" => "استهلاک داراییها",
                                "code" => 7213,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ملزومات مصرفی تولید",
                                "code" => 7214,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه های بسته بندی",
                                "code" => 7215,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ایاب و ذهاب",
                                "code" => 7216,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه غذای کارکنان",
                                "code" => 7217,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه پیک وپست",
                                "code" => 7218,
                                "type" => "debit"
                            ],
                            [
                                "name" => "پوشاك کارکنان",
                                "code" => 7219,
                                "type" => "debit"
                            ],
                            [
                                "name" => "بهداشت ودرمان",
                                "code" => 7220,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه سفروفوق العاده ماموریت",
                                "code" => 7221,
                                "type" => "debit"
                            ],
                            [
                                "name" => "کارمزدهای بانكی",
                                "code" => 7222,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق المشاوره",
                                "code" => 7223,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه های رایانه ای",
                                "code" => 7224,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه ثبتی وحق تمبر و وکالت",
                                "code" => 7225,
                                "type" => "debit"
                            ],
                            [
                                "name" => "پاداش هیئت مدیره",
                                "code" => 7226,
                                "type" => "debit"
                            ],
                            [
                                "name" => "خدمات حسابداری",
                                "code" => 7227,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "هزینه های توزیع وفروش",
                        "code" => 73,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "هزینه های تبلیغات وکاتالوگ و بروشور",
                                "code" => 7301,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه حمل ونقل کالای فروش رفته",
                                "code" => 7302,
                                "type" => "debit"
                            ],
                            [
                                "name" => "حق العمل کاری و کمیسیون فروش",
                                "code" => 7303,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه گارانتی محصولات",
                                "code" => 7304,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه انبارداری",
                                "code" => 7305,
                                "type" => "debit"
                            ],
                            [
                                "name" => "بیمه موجودی کالا",
                                "code" => 7306,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه مطالبات مشكوك الوصول وسوخت شده",
                                "code" => 7307,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایر درآمدهای عملیاتی",
                        "code" => 74,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "فروش ضایعات",
                                "code" => 7401,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سود ناشی از تسعیر دارایی های ارزی عملیاتی",
                                "code" => 7402,
                                "type" => "credit"
                            ],
                            [
                                "name" => "درآمد اجاره",
                                "code" => 7403,
                                "type" => "credit"
                            ],
                            [
                                "name" => "خالص اضافی انبارها",
                                "code" => 7404,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سایر",
                                "code" => 7405,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایر هزینه ها",
                        "code" => 75,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "ضایعات غیر عادی تولید",
                                "code" => 7501,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه های جذب نشده در تولید",
                                "code" => 7502,
                                "type" => "debit"
                            ],
                            [
                                "name" => "زیان کاهش ارزش موجودی ها",
                                "code" => 7503,
                                "type" => "debit"
                            ],
                            [
                                "name" => "زیان ناشی از تسعیر بدهی های ارزی عملیاتی",
                                "code" => 7504,
                                "type" => "debit"
                            ],
                            [
                                "name" => "خالص کسری انبارها",
                                "code" => 7505,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه کاهش ارزش دریافتنی ها",
                                "code" => 7506,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "هزینه های مالی",
                        "code" => 76,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "سود تضمین شده وکارمزدتسهیلات دریافتی",
                                "code" => 7501,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سود اوراق مشارکت پرداختی",
                                "code" => 7502,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه اوراق خرید دین ( تنزیل اسناد دریافتنی )",
                                "code" => 7503,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه تعهدات اجاره سرمایه ای ( لیزینگ )",
                                "code" => 7504,
                                "type" => "debit"
                            ],
                            [
                                "name" => "خرید سفته",
                                "code" => 7505,
                                "type" => "debit"
                            ],
                            [
                                "name" => "هزینه کارشناسی و ترهین املاک تسهیلات دریافتی",
                                "code" => 7506,
                                "type" => "debit"
                            ]
                        ]
                    ],
                    [
                        "name" => "سایر درآمدهاوهزینه های غیر عملیاتی",
                        "code" => 77,
                        "type" => "credit",
                        "children" => [
                            [
                                "name" => "سودوزیان ناشی ازفروش داراییهای ثابت مشهود",
                                "code" => 7701,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سودوزیان ناشی ازفروش داراییهای نامشهود",
                                "code" => 7702,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سودحاصل از فروش مواد اولیه",
                                "code" => 7703,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سود حاصل ازسایر اوراق بهادار و سپرده های سرمایه گذاری بانكی",
                                "code" => 7704,
                                "type" => "credit"
                            ],
                            [
                                "name" => "سود سرمایه گذاری در سهام",
                                "code" => 7705,
                                "type" => "credit"
                            ],
                            [
                                "name" => "زیان کاهش ارزش سرمایه گذاری های بلند مدت",
                                "code" => 7706,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سود ناشی از فروش سرمایه گذاری های بلند مدت",
                                "code" => 7707,
                                "type" => "credit"
                            ],
                            [
                                "name" => "درآمد (هزینه ) ناشی از ارزیابی سرمایه گذاری های جاری سریع المعامله به ارزش بازار",
                                "code" => 7708,
                                "type" => "both"
                            ],
                            [
                                "name" => "سود یا زیان تسعیریا تسویه دارایی ها وبدهی های ارزی غیر مرتبط با عملیات",
                                "code" => 7709,
                                "type" => "both"
                            ],
                            [
                                "name" => "سود تسهیلات اعطایی به دیگران",
                                "code" => 7710,
                                "type" => "credit"
                            ],
                            [
                                "name" => "زیان کاهش ارزش دارایی های غیر جاری",
                                "code" => 7711,
                                "type" => "debit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "بهای تمام شده کالای فروش رفته",
                "code" => 8,
                "type" => "debit",
                "children" => [
                    [
                        "name" => "بهای تمام شده کالای فروش رفته",
                        "code" => 81,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "بهای تمام شده کالای فروش رفته",
                                "code" => 8101,
                                "type" => "debit"
                            ],
                            [
                                "name" => "بهای تمام شده خدمات کارمزدی",
                                "code" => 8111,
                                "type" => "debit"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "name" => "حسابهای انتظامی",
                "code" => 9,
                "type" => "debit",
                "children" => [
                    [
                        "name" => "حسابهای انتظامی",
                        "code" => 91,
                        "type" => "debit",
                        "children" => [
                            [
                                "name" => "اسناد تضمینی مانزد دیگران",
                                "code" => 9101,
                                "type" => "debit"
                            ],
                            [
                                "name" => "سفته های تضمینی مانزد دیگران",
                                "code" => 9102,
                                "type" => "debit"
                            ],
                            [
                                "name" => "ضمانت نامه های بانكی مانزد دیگران",
                                "code" => 9103,
                                "type" => "debit"
                            ],
                            [
                                "name" => "اسنادتضمینی دیگران نزد ما",
                                "code" => 9104,
                                "type" => "debit"
                            ],
                            [
                                "name" => "طرف اسناد تضمینی مانزد دیگران",
                                "code" => 9201,
                                "type" => "credit"
                            ],
                            [
                                "name" => "طرف سفته های تضمینی ما نزد دیگران",
                                "code" => 9202,
                                "type" => "credit"
                            ],
                            [
                                "name" => "طرف ضمانت نامه های بانكی ما نزددیگران",
                                "code" => 9203,
                                "type" => "credit"
                            ],
                            [
                                "name" => "طرف اسناد تضمینی دیگران نزد ما",
                                "code" => 9204,
                                "type" => "credit"
                            ]
                        ]
                    ],
                    [
                        "name" => "طرف حسابهای انتظامی",
                        "code" => 92,
                        "type" => "debit",
                    ]
                ]
            ]
        ];

        foreach ($accounts_array as $account) {
            $created_account = $building->accountingAccounts()->create([
                'name' => $account['name'],
                'code' => $account['code'],
                'type' => $account['type'],
                // 'is_locked' => true,
            ]);
            if (isset($account['children'])) {
                foreach ($account['children'] as $child) {
                    $created_child = $building->accountingAccounts()->create([
                        'name' => $child['name'],
                        'code' => $child['code'],
                        'type' => $child['type'],
                        // 'is_locked' => true,
                        'parent_id' => $created_account->id,
                    ]);
                    if (isset($child['children'])) {
                        foreach ($child['children'] as $grandchild) {
                            $building->accountingAccounts()->create([
                                'name' => $grandchild['name'],
                                'code' => $grandchild['code'],
                                'type' => $grandchild['type'],
                                // 'is_locked' => true,
                                'parent_id' => $created_child->id,
                            ]);
                        }
                    }
                }
            }
        }

        $debtTypes = [
            'شارژ جاری',
            'شارژ عمرانی',
            'جریمه دیرکرد',
            'تخفیف',
            'اجاره',
        ];

        foreach ($debtTypes as $debtType) {
            $building->debtTypes()->firstOrCreate([
                'name' => $debtType,
            ], [
                'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '1305')->first()->id,
                'income_accounting_account_id' => $building->accountingAccounts()->where('code', '6103')->first()->id,
            ]);
        }
    }
}
