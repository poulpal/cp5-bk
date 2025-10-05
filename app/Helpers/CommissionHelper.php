<?php

namespace App\Helpers;

use Coduo\PHPHumanizer\NumberHumanizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CommissionHelper
{
    public function getUSDPrice()
    {
        return Cache::remember('usd_price', 60 * 60 * 24, function () {
            $response = Http::get('https://alanchand.com/api/price-free?type=currencies');

            if ($response->successful()) {
                $currencies = collect($response->json());

                $usd_hav = $currencies->where('slug', 'usd')->first();

                return $usd_hav['sell'];
            }else{

                $response = Http::get('http://api.navasan.tech/latest/?api_key=freeottpDrupLaaGPPZJgOmNQW4rRzX4');

                if ($response->successful()) {
                    $usd_sell = $response->json()['usd_sell'];

                    return (int)$usd_sell['value'];
                }else{
                    return 49000;
                }
            }
        });
    }

    public function calculateMaxCommission($building = null){
        if (!$building){
            return 500;
        }
        return $building->commission;
        $usd_price = $this->getUSDPrice();
        $max_commission = $usd_price / 12.25;
        return floor($max_commission / 100) * 100;
    }

    public function getSmsPrice()
    {
        return 224;
    }
}
