<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LateFineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $buildingId = $user->building_id ?? ($user->building->id ?? null);
        if (!$buildingId) {
            return response()->json(['success' => false, 'message' => 'Building not resolved.'], 422);
        }

        $units = DB::table('building_units')
            ->select('id', 'unit_number', 'late_fine_only_last_cycle')
            ->where('building_id', $buildingId)
            ->orderBy('unit_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['units' => $units],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $buildingId = $user->building_id ?? ($user->building->id ?? null);
        if (!$buildingId) {
            return response()->json(['success' => false, 'message' => 'Building not resolved.'], 422);
        }

        $data = $request->validate([
            'apply_all' => 'nullable|in:last,all',
            'units'     => 'nullable|array',
            'units.*.id' => 'required_with:units|integer',
            'units.*.late_fine_only_last_cycle' => 'required_with:units|boolean',
        ]);

        if (!empty($data['apply_all'])) {
            $val = $data['apply_all'] === 'last' ? 1 : 0;
            DB::table('building_units')
                ->where('building_id', $buildingId)
                ->update(['late_fine_only_last_cycle' => $val, 'updated_at' => now()]);
            return response()->json(['success' => true, 'message' => 'اعمال روی تمام واحدها انجام شد.']);
        }

        if (!empty($data['units'])) {
            $ids = collect($data['units'])->pluck('id')->all();

            $validIds = DB::table('building_units')
                ->where('building_id', $buildingId)
                ->whereIn('id', $ids)
                ->pluck('id')->all();

            DB::beginTransaction();
            try {
                foreach ($data['units'] as $u) {
                    if (in_array($u['id'], $validIds)) {
                        DB::table('building_units')
                            ->where('id', $u['id'])
                            ->update([
                                'late_fine_only_last_cycle' => $u['late_fine_only_last_cycle'] ? 1 : 0,
                                'updated_at' => now()
                            ]);
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return response()->json(['success' => false, 'message' => 'خطا در به‌روزرسانی'], 500);
            }

            return response()->json(['success' => true, 'message' => 'به‌روزرسانی انجام شد.']);
        }

        return response()->json(['success' => false, 'message' => 'داده‌ای برای تغییر ارسال نشده است.'], 422);
    }
}
