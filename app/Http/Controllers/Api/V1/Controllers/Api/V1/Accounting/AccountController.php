<?php

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Accounting\AccountResrouce;
use App\Http\Resources\Accounting\DetailResource;
use App\Models\Accounting\AccountingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AccountController extends Controller
{

    public function __construct()
    {
        // محدودسازی مدیر ساختمان به کانتکست خودش (همان رفتار قبلی شما)
        $this->middleware('restrictBuildingManager:other');

        // قفل حسابداری پیشرفته روی *همه* اکشن‌ها، شامل index/show
        // اسلاگ کانن: accounting-advanced  (الیاس‌های قدیمی مانند ...-1 یا ...-qr در بک‌اند مپ می‌شوند)
        $this->middleware('hasModule:accounting-advanced');
    }

    public function index()
    {
        $building = auth()->buildingManager()->building;
        $accounts = $building->accountingAccounts()
            ->get();
        $details = $building->accountingDetails()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => AccountResrouce::collection($accounts),
                'details' => DetailResource::collection($details),
            ]
        ]);
    }

    public function store(Request $request, AccountingAccount $parent_account)
    {
        if ($parent_account->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'id' => __("حساب مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        if (Str::length($parent_account->code) > 2) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'id' => __("حساب مورد نظر نمی تواند حساب زیرمجموعه داشته باشد"),
                ],
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:debit,credit,both',
                'code' => [
                    'required', 'string', 'max:255', Rule::unique('accounting_accounts', 'code')->where(function ($query) {
                        return $query->where('building_id', auth()->buildingManager()->building->id)->whereNull('deleted_at');
                    }),
                    'starts_with:' . $parent_account->code,
                ],
                'description' => 'nullable|string|max:255',
            ],
            [
                'code.starts_with' => __("کد حساب باید با کد حساب مادر شروع شود"),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $account = new AccountingAccount();
        $account->building_id = auth()->buildingManager()->building_id;
        $account->name = $request->name;
        $account->description = $request->description ?? null;
        $account->type = $request->type;
        $account->code = $request->code;
        $account->parent_id = $parent_account->id;

        $account->save();

        return response()->json([
            'success' => true,
            'message' => __("حساب با موفقیت ایجاد شد"),
            'data' => [
                'account' => $account,
            ]
        ]);
    }

    public function update(Request $request, AccountingAccount $account)
    {
        if ($account->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'id' => __("حساب مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:debit,credit,both',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($account->is_locked) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر قفل شده است"),
            ], 404);
        }

        $account->name = $request->name;
        $account->description = $request->description;

        if ($account->parent_id) {
            $account->type = $request->type;
        }

        $account->save();

        return response()->json([
            'success' => true,
            'message' => __("حساب با موفقیت ویرایش شد"),
            'data' => [
                'account' => $account,
            ]
        ]);
    }

    public function destroy(AccountingAccount $account)
    {
        if ($account->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر یافت نشد"),
            ], 404);
        }

        if ($account->is_locked) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر قفل شده است"),
            ], 404);
        }

        if ($account->children->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر دارای حساب زیرمجموعه است"),
            ], 404);
        }

        if (!$account->parent_id) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر حساب اصلی است"),
            ], 404);
        }

        if ($account->transactions->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => __("حساب مورد نظر دارای سند است"),
            ], 404);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => __("حساب با موفقیت حذف شد"),
        ]);
    }
}
