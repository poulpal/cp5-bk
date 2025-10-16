<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VersionController extends Controller
{
    public function index(Request $request)
    {
        $latest_version = Cache::remember('latest_version_bazaar', 60 * 15, function () {
            return $this->getVersionFromBazaar();
        });
        if (!$latest_version) {
            Cache::forget('latest_version_bazaar');
        }
        return response()->json([
            'version' => $latest_version,
            'force' => false,
            'urls' => [
                'cafebazaar' => 'https://cafebazaar.ir/app/ir.chargepal',
                'direct' => 'https://chargepal.ir/chargepal.apk'
            ]
        ]);
    }

    protected function getVersionFromBazaar()
    {
        try{
            $response = Http::withHeaders([
                'CAFEBAZAAR-PISHKHAN-API-SECRET' => config('bazaar.apikey')
            ])
                ->withoutVerifying()
                ->get('https://api.pishkhan.cafebazaar.ir/v1/apps/releases/last-published');
            return $response->json()['release']['packages'][0]['version_name'];
        }catch (\Exception $e){
            return '';
        }
    }
}
