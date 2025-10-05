<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"         => ":attribute باید پذیرفته شده باشد.",
    "active_url"       => "آدرس :attribute معتبر نیست",
    "after"            => ":attribute باید تاریخی بعد از :date باشد.",
    "alpha"            => ":attribute باید شامل حروف الفبا باشد.",
    "alpha_dash"       => ":attribute باید شامل حروف الفبا و عدد و خظ تیره(-) باشد.",
    "alpha_num"        => ":attribute باید شامل حروف الفبا و عدد باشد.",
    "array"            => ":attribute باید شامل آرایه باشد.",
    "before"           => ":attribute باید تاریخی قبل از :date باشد.",
    "between"          => [
        "numeric" => ":attribute باید بین :min و :max باشد.",
        "file"    => ":attribute باید بین :min و :max کیلوبایت باشد.",
        "string"  => ":attribute باید بین :min و :max کاراکتر باشد.",
        "array"   => ":attribute باید بین :min و :max آیتم باشد.",
    ],
    "boolean"          => "فیلد :attribute فقط میتواند صحیح و یا غلط باشد",
    "confirmed"        => ":attribute با تاییدیه مطابقت ندارد.",
    "date"             => ":attribute یک تاریخ معتبر نیست.",
    "date_format"      => ":attribute با الگوی :format مطاقبت ندارد.",
    "different"        => ":attribute و :other باید متفاوت باشند.",
    "digits"           => ":attribute باید :digits رقم باشد.",
    "digits_between"   => ":attribute باید بین :min و :max رقم باشد.",
    "email"            => "فرمت :attribute معتبر نیست.",
    "exists"           => ":attribute انتخاب شده، معتبر نیست.",
    "filled"           => "فیلد :attribute الزامی است",
    "image"            => ":attribute باید تصویر باشد.",
    "in"               => ":attribute انتخاب شده، معتبر نیست.",
    "integer"          => ":attribute باید نوع داده ای عددی (integer) باشد.",
    "decimal"          => ":attribute باید نوع داده ای عددی باشد.",
    "ip"               => ":attribute باید IP آدرس معتبر باشد.",
    "max"              => [
        "numeric" => ":attribute نباید بزرگتر از :max باشد.",
        "file"    => ":attribute نباید بزرگتر از :max کیلوبایت باشد.",
        "string"  => ":attribute نباید بیشتر از :max کاراکتر باشد.",
        "array"   => ":attribute نباید بیشتر از :max آیتم باشد.",
    ],
    "mimes"            => ":attribute باید یکی از فرمت های :values باشد.",
    "min"              => [
        "numeric" => ":attribute نباید کوچکتر از :min باشد.",
        "file"    => ":attribute نباید کوچکتر از :min کیلوبایت باشد.",
        "string"  => ":attribute نباید کمتر از :min کاراکتر باشد.",
        "array"   => ":attribute نباید کمتر از :min آیتم باشد.",
    ],
    "not_in"           => ":attribute انتخاب شده، معتبر نیست.",
    "numeric"          => ":attribute باید شامل عدد باشد.",
    "regex"            => ":attribute یک فرمت معتبر نیست.",
    "required"         => "فیلد :attribute الزامی است.",
    "required_if"      => "فیلد :attribute هنگامی که :other برابر با :value است، الزامیست.",
    "required_with"    => ":attribute الزامی است زمانی که :values موجود است.",
    "required_with_all" => ":attribute الزامی است زمانی که :values موجود است.",
    "required_without" => ":attribute الزامی است زمانی که :values موجود نیست.",
    "required_without_all" => ":attribute الزامی است زمانی که :values موجود نیست.",
    "same"             => ":attribute و :other باید مانند هم باشند.",
    "size"             => [
        "numeric" => ":attribute باید برابر با :size باشد.",
        "file"    => ":attribute باید برابر با :size کیلوبایت باشد.",
        "string"  => ":attribute باید برابر با :size کاراکتر باشد.",
        "array"   => ":attribute باسد شامل :size آیتم باشد.",
    ],
    "string"           => "The :attribute must be a string.",
    "timezone"         => "فیلد :attribute باید یک منطقه صحیح باشد.",
    "unique"           => ":attribute قبلا انتخاب شده است.",
    "url"              => "فرمت آدرس :attribute اشتباه است.",
    'uploaded'         => ':attribute آپلود نشد',
    'gt' => [
        'numeric' => 'فیلد :attribute باید از مقدار :value بزرگتر باشد.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'start_date' => [
            'after' => 'زمان شروع تخفیف باید تاریخی بعد از امروز باشد.',
        ],
        'menu' => [
            'required_if' => 'با توجه به انتخاب شما فیلد منو الزامی است.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [
        "name" => "نام",
        "username" => "نام کاربری",
        "email" => "پست الکترونیکی",
        "first_name" => "نام",
        "last_name" => "نام خانوادگی",
        "family" => "نام خانوادگی",
        "password" => "رمز عبور",
        "password_confirmation" => "تاییدیه ی رمز عبور",
        "city" => "شهر",
        "country" => "کشور",
        "address" => "نشانی",
        "phone" => "تلفن",
        "mobile" => "تلفن همراه",
        "age" => "سن",
        "sex" => "جنسیت",
        "gender" => "جنسیت",
        "day" => "روز",
        "month" => "ماه",
        "year" => "سال",
        "hour" => "ساعت",
        "minute" => "دقیقه",
        "second" => "ثانیه",
        "title" => "عنوان",
        "text" => "متن",
        "content" => "محتوا",
        "description" => "توضیحات",
        "excerpt" => "گلچین کردن",
        "date" => "تاریخ",
        "time" => "زمان",
        "available" => "موجود",
        "size" => "اندازه",
		"file" => "فایل",
		"fullName" => "نام کامل",
        "slug"=>"نام نمایشی URL",
        "image"=>"تصویر",
        "deadline"=>"مهلت انجام کار",
        "guarantee"=>"وجه ضمانت",
        "prepayment"=>"پیش پرداخت",
        //
        "amount" => "مقدار",
        "picture" => "تصویر",
        "profile_picture" => "تصویر پروفایل",
        "price" => "هزینه",
        "start_date" => "زمان شروع",
        "end_date" => "زمان پایان",
        "duration" => "مدت",
        "type" => "نوع",
        "attempts" => "دفعات",
        "payment" => "شیوه پرداخت",
        "uuid" => 'کد',
        "expires_at" => 'انقضا',
        "user_id" => 'کاربر',
        //
        "coupons_count" => 'تعداد تخفیف ها',
        "conditions" => 'مقررات',
        "coupons.*.title" => 'عنوان زیرتخفیف',
        "coupons.*.price" => 'قیمت زیرتخفیف',
        "coupons.*.original_price" => 'قیمت اصلی زیرتخفیف',
        "coupons.*.picture" => 'تصویر زیرتخفیف',
        "today" => 'امروز',
        "pictures" => 'تصاویر',
        "pictures.*" => 'تصاویر',
        "sheba" => 'شبا',
        "code_melli" => 'کدملی',
        "shop_phone" => 'شماره تلفن ثابت کسب و کار',
        "shop_name" => 'نام کسب و کار',
        "shop_address" => 'آدرس کسب و کار',
        "tos" => 'قوانین و مقررات',
        "captcha" => 'کد اعتبار سنجی',
        "national_id" => 'کد ملی',
        "postal_code" => 'کد پستی',
        "charge_fee" => 'هزینه شارژ',
        "ownership" => 'مالکیت',
        "building_id" => 'ساختمان',
        "phone_number" => 'شماره تلفن',
        "province" => 'استان',
        "district" => 'منطقه',
        "sheba_number" => 'شماره شبا',
        "card_number" => 'شماره کارت',
        "national_card_image" => 'تصویر کارت ملی',
        "deposit_to" => 'واریز به',
        "attachment" => 'فایل پیوست',
        "building_name_en" => 'نام انگلیسی ساختمان',
        "options" => "گزینه ها",
        "referral_mobile" => "موبایل معرف",
        'charge_day' => 'روز اعمال شارژ',
        'custom_payment' => 'پرداخت دلخواه',
        'late_fine' => 'خسارت تاخیر',
        'late_fine_percent' => 'درصد خسارت تاخیر',
        'late_fine_days' => 'روز خسارت تاخیر',
        'manual_payment' => 'پرداخت دستی',
        'auto_add_monthly_charge' => 'اعمال خودکار شارژ ماهیانه',
        'invoice_number' => 'شماره',
        'discount_code' => 'کد تخفیف',
        'code' => 'کد',
        'units' => 'واحد ها',
        'count' => 'تعداد',
        'quantity' => 'تعداد',
        'unit_count' => 'تعداد واحد ها',
    ],
];
