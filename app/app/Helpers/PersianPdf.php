<?php

namespace App\Helpers;

class PersianPdf
{
    public function convert($str){
        $is_numeric = true;
        $space_separated = explode(' ', $str);
        foreach ($space_separated as $part) {
            if (!is_numeric($part) && $part != '/' && $part != ':') {
                $is_numeric = false;
            }
        }
        if (is_numeric($str) || $is_numeric) {
            return $str;
        }

        $arr = [];

        $temp = '';

        for ($i = 0; $i < strlen($str); $i++) {
            if ($i == 0) {
                $temp .= $str[$i];
                continue;
            }
            if ((is_numeric($str[$i]) && !is_numeric($str[$i - 1])) || (!is_numeric($str[$i]) && is_numeric($str[$i - 1]))) {
                array_push($arr, $temp);
                $temp = $str[$i];
            } else {
                $temp .= $str[$i];
            }
        }

        array_push($arr, $temp);

        $str = '';

        $arr = array_reverse($arr);

        foreach ($arr as $item) {
            if (is_numeric($item)  || strlen($item) == 1) {
                $str .= $item;
            } else {
                $str .= $this->_convert($item);
            }
        }

        return $str;
    }

    private function _convert($str){
        $replace_pairs = [
            'لا' => 'xxxxxxx',
        ];
        $str = strtr($str, $replace_pairs);
        $Persian = new Persian();
        $Arabic = new \ArPHP\I18N\Arabic();
        $str = $Persian->utf8Glyphs($str, $max_chars = 200, $hindo = false);

        $replace_pairs = [
            'xxxxxxx' => $Arabic->utf8Glyphs('لا'),
        ];
        $str = strtr($str, $replace_pairs);
        return $str;
    }
}
