<?php

namespace App\Http\Controllers\Api\V1\Public;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\BannerResource;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * Display a listing of banners.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => BannerResource::collection($banners),
        ]);
    }
}
