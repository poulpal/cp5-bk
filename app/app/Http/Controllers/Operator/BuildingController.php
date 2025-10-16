<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;

class BuildingController extends Controller
{
    public function index(Builder $builder)
    {
        if (request()->ajax()) {
            return DataTables::of(
                Building::query()
            )
                ->editColumn('created_at', function ($row) {
                    return Jalalian::forge($row->created_at)->format('Y/m/d H:i:s');
                })
                ->editColumn('balance', function ($row) {
                    return number_format($row->balance * 10);
                })
                ->editColumn('toll_balance', function ($row) {
                    return number_format($row->toll_balance * 10);
                })
                ->editColumn('commission', function ($row) {
                    return number_format($row->commission * 10);
                })
                ->addColumn('imported_unit_count', function ($row) {
                    return $row->units()->count();
                })
                ->addColumn('modules', function ($row) {
                    $str = '';
                    foreach ($row->modules as $module) {
                        $str .= $module->title . ' (' . Jalalian::forge($module->pivot->ends_at)->ago() . ')<br>';
                    }
                    return $str;
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('operator.users.index', ['building' => $row->id]) . '" class="btn btn-sm btn-primary mb-1">کاربران</a>' .
                        '<a href="' . route('operator.users.index', ['building' => $row->id, 'building_manager' => 1]) . '" class="btn btn-sm btn-primary mb-1">مدیران</a>' ;
                })
                ->rawColumns(['action', 'modules'])
                ->make(true);
        }

        $table = $builder->columns([
            ['data' => 'name', 'name' => 'name', 'title' => 'نام'],
            ['data' => 'name_en', 'name' => 'name_en', 'title' => 'نام انگلیسی'],
            ['data' => 'unit_count', 'name' => 'unit_count', 'title' => 'تعداد واحد'],
            ['data' => 'imported_unit_count', 'name' => 'imported_unit_count', 'title' => 'تعداد واحد ثبت شده'],
            ['data' => 'balance', 'name' => 'balance', 'title' => 'موجودی صندوق اصلی'],
            ['data' => 'toll_balance', 'name' => 'toll_balance', 'title' => 'موجودی صندوق عمرانی'],
            ['data' => 'sms_balance', 'name' => 'sms_balance', 'title' => 'موجودی پیامک'],
            ['data' => 'commission', 'name' => 'commission', 'title' => 'کمیسیون'],
            ['data' => 'is_verified', 'name' => 'is_verified', 'title' => 'تایید شده'],
            ['data' => 'signed_contract', 'name' => 'signed_contract', 'title' => 'قرارداد امضا شده'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاریخ ثبت نام'],
            ['data' => 'modules', 'name' => 'modules', 'title' => 'ماژول ها'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
            'order' => [[9, 'asc']]
        ]);

        return view('operator.users', compact('table'));
    }
}
