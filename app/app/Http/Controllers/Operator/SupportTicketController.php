<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\DepositRequest;
use App\Models\PendingDeposit;
use App\Models\SupportTicket;
use App\Notifications\BuildingManager\TicketAnswered;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;

class SupportTicketController extends Controller
{
    public function index(Builder $builder)
    {
        if (request()->ajax()) {
            return DataTables::of(
                SupportTicket::query()->orderBy('created_at', 'desc')
            )
                ->addColumn('building', function ($item) {
                    return $item->building->name;
                })
                ->editColumn('created_at', function ($item) {
                    return Jalalian::forge($item->created_at)->format('Y/m/d H:i:s');
                })
                ->addColumn('status', function ($item) {
                    if ($item->status == 'closed') {
                        return '<span class="badge badge-success">بسته شده</span>';
                    }
                    if ($item->status == 'open') {
                        return '<span class="badge badge-warning">باز</span>';
                    }
                })
                ->addColumn('action', function ($item) {
                    return '<a href="' . route('operator.supportTickets.show', ['supportTicket' => $item->id]) . '" class="btn btn-sm btn-primary m-1">پاسخ</a>' .
                        '<a href="' . route('operator.supportTickets.toggleStatus', ['supportTicket' => $item->id]) . '" class="btn btn-sm btn-primary m-1">تغییر وضعیت</a>';

                })
                ->rawColumns(['action', 'building', 'status'])
                ->make(true);
        }

        $table = $builder->columns([
            ['data' => 'subject', 'name' => 'subject', 'title' => 'عنوان'],
            ['data' => 'building', 'name' => 'building', 'title' => 'ساختمان'],
            ['data' => 'status', 'name' => 'status', 'title' => 'وضعیت'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاریخ'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
        ]);


        return view('operator.supportTickets.index')->with([
            'table' => $table,
        ]);
    }

    public function show(SupportTicket $supportTicket)
    {
        return view('operator.supportTickets.show')->with([
            'supportTicket' => $supportTicket->load('building', 'replies'),
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supportTicket->replies()->create([
            'from' => 'support',
            'message' => $request->message,
        ]);

        $supportTicket->update([
            'status' => 'open',
        ]);

        // notify last reply user
        $supportTicket->replies()->where('from', 'user')->latest()->first()->user->notify(new TicketAnswered($supportTicket->subject));
        session()->flash('success', 'پاسخ با موفقیت ارسال شد');
        return redirect()->back();
    }

    public function toggleStatus(SupportTicket $supportTicket)
    {
        $supportTicket->update([
            'status' => $supportTicket->status == 'open' ? 'closed' : 'open',
        ]);

        session()->flash('success', 'وضعیت تیکت با موفقیت تغییر یافت');
        return redirect()->back();
    }
}
