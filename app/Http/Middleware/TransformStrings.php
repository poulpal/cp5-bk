<?php

namespace App\Http\Middleware;

use App\Facades\NumberFormatter;
use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Http\Request;

class TransformStrings extends TransformsRequest
{
    protected function transform($key, $value)
    {
        $value = NumberFormatter::enDigits($value);
        return $value;
    }
}
