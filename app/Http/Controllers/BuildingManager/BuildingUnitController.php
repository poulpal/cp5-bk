<?php

namespace App\Http\Controllers\BuildingManager;

use App\Http\Controllers\Controller;
use App\Models\BuildingUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingUnitController extends Controller
{
    public function index()
    {
        $units = auth()->buildingManager()->building->units()->orderBy('unit_number')->paginate(30);
        return view('buildingManager.units.index')->with('units', $units);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('buildingManager.units.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:11',
            'unit_number' => 'required',
            'charge_fee' => 'required|integer|min:1',
            'ownership' => 'required|in:owner,renter',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            $user = new User();
            $user->mobile = $request->mobile;
            $user->save();
        }

        $building_unit = BuildingUnit::where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $request->unit_number)->first();
        if (!$building_unit) {
            $building_unit = new BuildingUnit();
            $building_unit->building_id = auth()->buildingManager()->building->id;
            $building_unit->unit_number = $request->unit_number;
            $building_unit->charge_fee = $request->charge_fee;
            $building_unit->save();
        } else {
            $building_unit->charge_fee = $request->charge_fee;
            $building_unit->save();
        }

        $building_unit->residents()->attach($user->id, ['ownership' => $request->ownership]);

        return redirect()->route('building_manager.units.index')->with('success', 'ساکن با موفقیت اضافه شد');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BuildingUnit $building_unit)
    {
        $building_manager = auth()->buildingManager();
        if ($building_manager->building->id != $building_unit->building_id) {
            return redirect()->route('building_manager.units.index');
        }
        $validator = Validator::make($request->all(), [
            'charge_fee' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $building_unit->charge_fee = $request->charge_fee;

        $building_unit->save();
        return redirect()->back()->with('success', 'شارژ ماهانه واحد با موفقیت ویرایش شد');
    }

    public function showAddInvoice(BuildingUnit $building_unit)
    {
        $building_manager = auth()->buildingManager();
        if ($building_manager->building->id != $building_unit->building_id) {
            return redirect()->route('building_manager.units.index');
        }
        return view('buildingManager.units.addInvoice')->with('building_unit', $building_unit);
    }

    public function addInvoice(Request $request, BuildingUnit $building_unit)
    {
        $building_manager = auth()->buildingManager();
        if ($building_manager->building->id != $building_unit->building_id) {
            return redirect()->route('building_manager.units.index');
        }
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:deposit,debt',
            'amount' => 'required|integer|min:1',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $building_unit->increment('charge_debt', $request->type == 'deposit' ? $request->amount * -1 : $request->amount);

        $building_unit->invoices()->create([
            'business_id' => $building_unit->building->buildingManager->business->id,
            'amount' => $request->type == 'deposit' ? $request->amount : $request->amount * -1,
            'status' => 'paid',
            'payment_method' => 'cash',
            'serviceable_id' => $building_unit->id,
            'serviceable_type' => BuildingUnit::class,
            'description' => $request->description . ' - واحد : ' . $building_unit->unit_number,
        ]);
        return redirect()->back()->with('success', 'سند با موفقیت اضافه شد');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(BuildingUnit $building_unit)
    {
        $building_manager = auth()->buildingManager();
        if ($building_manager->building->id != $building_unit->building_id) {
            return redirect()->route('building_manager.units.index');
        }
        $building_unit->residents()->detach();
        $building_unit->delete();
        return redirect()->route('building_manager.units.index')->with('success', 'ساکن با موفقیت حذف شد');
    }
}
