<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Modules\ModuleActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ActivationTestController extends Controller
{
    /**
     * فقط برای تست فعال‌سازی (بعداً در verify پرداخت صدا زده می‌شود)
     * POST /v1/subscriptions/activate/test
     * body: { "items": ["accounting-advanced-qr"], "period": "yearly" }
     */
    public function activate(Request $request, ModuleActivationService $svc)
    {
        $u = Auth::user();
        if (!$u) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);

        $v = Validator::make($request->all(), [
            'items'  => ['required', 'array', 'min:1'],
            'items.*'=> ['string','min:1'],
            'period' => ['nullable','string'],
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $svc->activate(
            $u->id,
            method_exists($u, 'currentBuildingId') ? $u->currentBuildingId() : null,
            $request->input('items', []),
            $request->input('period', 'monthly'),
            now()
        );

        return response()->json(['success' => true, 'message' => 'Activated']);
    }
}
