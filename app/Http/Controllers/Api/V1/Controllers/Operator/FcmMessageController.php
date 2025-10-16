<?php

namespace App\Http\Controllers\Operator;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendFcmMessage;
use App\Mail\CustomMail;
use Illuminate\Http\Request;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\item;
use App\Models\FcmMessage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Str;


class FcmMessageController extends Controller
{
    public function index(Builder $builder)
    {
        if (request()->ajax()) {
            return DataTables::of(
                FcmMessage::query()->orderBy('status', 'asc')->orderBy('created_at', 'desc')
            )
                ->editColumn('pattern', function ($item) {
                    // replace all \n with <br>
                    $pattern = str_replace("\n", "<br>", $item->pattern);
                    return $pattern;
                })
                ->editColumn('created_at', function ($item) {
                    return Jalalian::forge($item->created_at)->format('Y/m/d H:i:s');
                })
                ->addColumn('action', function ($item) {
                    if ($item->status !== 'pending') {
                        return '';
                    }
                    return '<a href="' . route('operator.fcmMessages.accept', $item->id) . '" class="btn btn-sm btn-success">تایید و ارسال</a>';
                })
                ->addColumn('status', function ($item) {
                    if ($item->status == 'pending') {
                        return '<span class="badge badge-warning">در انتظار تایید</span>';
                    }
                    if ($item->status == 'sending') {
                        return '<span class="badge badge-info">در حال ارسال</span>';
                    }
                    if ($item->status == 'accepted') {
                        return '<span class="badge badge-success">ارسال شده</span>';
                    }
                })
                ->addColumn('qty', function ($item) {
                    return count($item->units);
                })
                ->rawColumns(['action', 'pattern', 'status'])
                ->make(true);
        }

        $table = $builder->columns([
            ['data' => 'pattern', 'name' => 'pattern', 'title' => 'متن پیام'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاریخ ایجاد'],
            ['data' => 'count', 'name' => 'count', 'title' => 'تعداد شماره ها'],
            ['data' => 'length', 'name' => 'length', 'title' => 'طول پیام'],
            ['data' => 'status', 'name' => 'status', 'title' => 'وضعیت'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
        ]);

        return view('operator.fcmMessages', compact('table'));
    }

    public function accept(FcmMessage $fcmMessage)
    {
        $fcmMessage->update([
            'status' => 'sending'
        ]);

        $items = [];

        foreach ($fcmMessage->units as $unit_id) {
            $unit = BuildingUnit::find($unit_id);
            $items[] = new SendFcmMessage($unit, $fcmMessage->pattern, $fcmMessage->resident_type);
        }

        $batch = Bus::batch($items)
        ->allowFailures()
        ->then(function ($batch) {
            $fcmMessage = FcmMessage::where('batch_id', $batch->id)->first();
            $fcmMessage->status = 'completed';
            $fcmMessage->save();
        })->catch(function ($batch, $e) {
            $fcmMessage = FcmMessage::where('batch_id', $batch->id)->first();
            $fcmMessage->status = 'failed';
            $fcmMessage->save();
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در ارسال پیام متنی', $e->getMessage() . "\n" . $batch->id));
        })->finally(function ($batch) {
            // The batch has finished executing...
        })->dispatch();

        $fcmMessage->batch_id = $batch->id;
        $fcmMessage->save();

        session()->flash('success', 'پیام متنی با موفقیت در صف ارسال قرار گرفت.');
        return redirect()->route('operator.fcmMessages.index');
    }
}
