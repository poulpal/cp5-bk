<?php

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index']);
        // $this->middleware('hasModule:accounting-basic')->only(['index']);
        $this->middleware('hasModule:accounting-advanced-qr')->except(['index']);
    }

    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'type' => 'nullable|string|in:cash,person,bank,petty_cash,withdrawable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $building = auth()->buildingManager()->building;
        $details = $building->accountingDetails();
        if (request()->type) {
            if (request()->type == 'withdrawable') {
                $details = $details->whereIn('type', ['cash', 'bank'])
                ->whereNotIn('name', ['صندوق شارژپل']);
            } else {
                $details = $details->where('type', request()->type);
            }
        }
        $details = $details->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'details' => $details,
            ]
        ]);
    }

    public function store(Request $request, AccountingDetail $parent_detail)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:cash,person,bank,petty_cash',
                'code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('accounting_details', 'code')->where(function ($query) {
                        return $query->where('building_id', auth()->buildingManager()->building->id)->whereNull('deleted_at');
                    }),
                ],
                'description' => 'nullable|string|max:255',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $detail = new AccountingDetail();
        $detail->building_id = auth()->buildingManager()->building_id;
        $detail->name = $request->name;
        $detail->type = $request->type;
        $detail->code = $request->code;
        $detail->parent_id = $parent_detail->id;

        $detail->save();

        return response()->json([
            'success' => true,
            'message' => __("تفضیل با موفقیت ایجاد شد"),
            'data' => [
                'detail' => $detail,
            ]
        ]);
    }

    public function update(Request $request, AccountingDetail $detail)
    {
        if ($detail->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'id' => __("تفضیل مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:cash,person,bank,petty_cash',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $detail->name = $request->name;

        $detail->save();

        return response()->json([
            'success' => true,
            'message' => __("تفضیل با موفقیت ویرایش شد"),
            'data' => [
                'detail' => $detail,
            ]
        ]);
    }

    public function destroy(AccountingDetail $detail)
    {
        if ($detail->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => __("تفضیل مورد نظر یافت نشد"),
            ], 404);
        }

        if ($detail->is_locked) {
            return response()->json([
                'success' => false,
                'message' => __("تفضیل مورد نظر قفل شده است"),
            ], 404);
        }

        if ($detail->transactions->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => __("تفضیل مورد نظر دارای سند است"),
            ], 404);
        }

        $detail->delete();

        return response()->json([
            'success' => true,
            'message' => __("تفضیل با موفقیت حذف شد"),
        ]);
    }
}
