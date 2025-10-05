<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\BuildingManager;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;

class UserController extends Controller
{
    public function index(Builder $builder)
    {
        $validator = validator(request()->all(), [
            'building' => 'nullable|exists:buildings,id',
            'building_manager' => 'nullable|boolean',
        ]);
        if (request()->ajax()) {
            $query = User::query();
            if (request('building') && request('building_manager')) {
                $query->where('building_id', request('building'));
            }
            if (request('building') && !request('building_manager')) {
                $query->whereHas('building_units', function ($query) {
                    $query->where('building_id', request('building'));
                });
            }
            return DataTables::of(
                $query
            )
                ->editColumn('created_at', function ($user) {
                    return Jalalian::forge($user->created_at)->format('Y/m/d H:i:s');
                })
                ->editColumn('role', function ($user) {
                    return $user->role == 'building_manager' ? 'مدیر ساختمان' : 'ساکن';
                })
                ->addColumn('action', function ($user) {
                    return '<a target="_blank" href="' . route('operator.users.login', $user->id) . '" class="btn btn-sm btn-primary">ورود به پنل</a>';
                })
                ->addColumn('building', function ($user) {
                    $buildings = [];
                    if ($user->role == 'building_manager') {
                        $buildings[] = BuildingManager::find($user->id)->building->name . '-مدیر ساختمان';
                    }
                    foreach ($user->building_units as $building_unit) {
                        $buildings[] = $building_unit->building->name . '-' . $building_unit->unit_number;
                    }
                    return implode('<br>', $buildings);
                })
                ->rawColumns(['action', 'building'])
                ->make(true);
        }

        $table = $builder->columns([
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'نام'],
            ['data' => 'last_name', 'name' => 'last_name', 'title' => 'نام خانوادگی'],
            ['data' => 'mobile', 'name' => 'mobile', 'title' => 'شماره تماس'],
            ['data' => 'otp', 'name' => 'otp', 'title' => 'رمز'],
            ['data' => 'role', 'name' => 'role', 'title' => 'نقش'],
            ['data' => 'building', 'name' => 'building', 'title' => 'ساختمان'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاریخ ثبت نام'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
        ]);

        return view('operator.users', compact('table'));
    }

    public function login(User $user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;
        if (config('app.env') == 'local') {
            return redirect('http://localhost:3000/login?token=' . $token);
        }

        return redirect('https://cp.chargepal.ir/login?token=' . $token);
    }
}
