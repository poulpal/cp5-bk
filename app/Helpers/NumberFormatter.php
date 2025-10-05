<?php

namespace App\Helpers;

use Coduo\PHPHumanizer\NumberHumanizer;
use Illuminate\Support\Str;

class NumberFormatter
{
    // public function metricSuffix($number)
    // {
    //     $divider = 50;
    //     if ($number <= $divider) {
    //         return $number;
    //     }
    //     if ($number <= 999) {
    //         return (floor($number / $divider) * $divider) . '+';
    //     }
    //     $formatted = NumberHumanizer::metricSuffix($number);
    //     $formatted = Str::replace(
    //         ['k', 'M', 'G', 'T'],
    //         [
    //             ' هزار',
    //             ' میلیون',
    //             ' میلیارد',
    //             ' هزار میلیارد'
    //         ],
    //         $formatted
    //     );
    //     return $formatted;
    // }

    public function enDigits($input)
    {
        if (is_null($input)) {
            return null;
        }
        $replace_pairs = array(
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            'أ' => 'ا',
            'إ' => 'ا',
            'ك' => 'ک',
            'ؤ' => 'و',
            'ة' => 'ه',
            'ۀ' => 'ه',
            'ي' => 'ی',
            // '؛' => ';',
            // '؟' => '?',
            // '،' => ',',
        );
        return strtr($input, $replace_pairs);
    }
}
