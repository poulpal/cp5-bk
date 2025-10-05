<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\DepositRequest;
use App\Models\PendingDeposit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;

class DepositRequestController extends Controller
{
    public function index(Builder $builder)
    {
        if (request()->ajax()) {
            return DataTables::of(
                DepositRequest::query()->orderBy('created_at', 'desc')
            )
                ->addColumn('building', function ($item) {
                    return $item->building->name;
                })
                ->editColumn('amount', function ($item) {
                    return number_format($item->amount * 10) . ' ریال';
                })
                ->editColumn('created_at', function ($item) {
                    return Jalalian::forge($item->created_at)->format('Y/m/d H:i:s');
                })
                ->addColumn('status', function ($item) {
                    if ($item->status == 'pending') {
                        return '<span class="badge badge-warning">در انتظار تایید</span>';
                    }
                    if ($item->status == 'rejected') {
                        return '<span class="badge badge-danger">رد شده</span>';
                    }
                    if ($item->status == 'accepted') {
                        return '<span class="badge badge-success">انجام شده</span>';
                    }
                })
                ->addColumn('action', function ($item) {
                    if ($item->status == 'pending') {
                        return '<a href="' . route('operator.depositRequests.accept', ['depositRequest' => $item->id]) . '" class="btn btn-sm btn-primary">تایید</a>';
                    }
                    return '';
                })
                ->rawColumns(['action', 'building', 'status', 'amount'])
                ->make(true);
        }

        $table = $builder->columns([
            ['data' => 'building', 'name' => 'building', 'title' => 'ساختمان'],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'مبلغ'],
            ['data' => 'status', 'name' => 'status', 'title' => 'وضعیت'],
            ['data' => 'sheba', 'name' => 'sheba', 'title' => 'حساب'],
            ['data' => 'description', 'name' => 'description', 'title' => 'توضیحات'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاریخ'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
        ]);

        $building_with_most_balance = Building::orderByRaw('balance + toll_balance DESC')->first();

        return view('operator.depositRequests')->with([
            'table' => $table,
            'building_with_most_balance' => $building_with_most_balance,
        ]);
    }

    public function create()
    {
        $buildings = Building::orderByRaw('balance + toll_balance DESC')->get();
        $pending_deposits = PendingDeposit::where('status', 'pending');
        if (request()->building == 2) {
            $pending_deposits = $pending_deposits->where('created_at', '>=', Carbon::parse('2024-04-03 00:00:00'));
        }
        $pending_deposits = $pending_deposits->get();
        return view('operator.create-depositRequests')->with([
            'buildings' => $buildings,
            'pending_deposits' => $pending_deposits,
        ]);
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'building' => 'required',
        //     'amount' => 'required|string|min:1',
        //     'sheba' => 'required',
        //     'description' => 'required',
        //     // 'pending_deposits' => 'nullable|array',
        //     // 'pending_deposits.*' => 'nullable|exists:pending_deposits,id',
        // ]);
        $validator = Validator::make($request->all(), [
            'building' => ['required', 'exists:buildings,id'],
            'amount' => ['required', 'int', 'min:1'],
            'sheba' => ['required'],
            'description' => ['required'],
            'type' => 'nullable|in:charge,toll',
            // 'pending_deposits' => ['nullable', 'array'],
            // 'pending_deposits.*' => ['nullable', 'exists:pending_deposits,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('errors', $validator->errors());
        }

        $building = Building::findOrfail($request->building);
        $amount = str_replace(',', '', $request->amount);

        // if ($request->amount > ($building->balance * 10)) {
        //     return redirect()->back()->with('error', 'موجودی صندوق کافی نیست');
        // }

        DepositRequest::create([
            'building_id' => $building->id,
            'amount' => ($amount / 10),
            'status' => 'accepted',
            'deposit_to' => 'other',
            'sheba' => $request->sheba,
            'description' => $request->description,
        ]);

        if ($request->type && $request->type == 'toll') {
            $building->toll_balance = $building->toll_balance - ($amount / 10);
            $building->save();
        }else{
            $building->balance = $building->balance - ($amount / 10);
            $building->save();
        }


        // foreach ($request->pending_deposits as $pending_deposit) {
        //     $pending_deposit = PendingDeposit::findOrfail($pending_deposit);
        //     $pending_deposit->status = 'paid';
        //     $pending_deposit->save();
        // }

        return redirect()->route('operator.depositRequests.index')->with('success', 'درخواست با موفقیت ثبت شد');
    }

    public function accept(DepositRequest $depositRequest)
    {
        if ($depositRequest->status == 'accepted') {
            return redirect()->back()->with('error', 'این درخواست قبلا تایید شده است');
        }

        return view('operator.accept-depositRequests')->with([
            'depositRequest' => $depositRequest,
        ]);
    }

    public function acceptStore(Request $request, DepositRequest $depositRequest)
    {

        $validator = Validator::make($request->all(), [
            'description' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('errors', $validator->errors());
        }

        $depositRequest->status = 'accepted';
        $depositRequest->description = $request->description;
        $depositRequest->save();

        $building = $depositRequest->building;
        $building->balance = $building->balance - $depositRequest->amount;
        $building->save();

        if ($depositRequest->balance) {
            $depositRequest->balance->amount = $depositRequest->balance->amount - $depositRequest->amount;
            $depositRequest->balance->save();
        }

        return redirect()->route('operator.depositRequests.index')->with('success', 'درخواست با موفقیت تایید شد');
    }
}
