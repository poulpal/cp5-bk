<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Jobs\SendVoiceMessage;
use App\Mail\CustomMail;
use Illuminate\Http\Request;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\item;
use App\Models\VoiceMessage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Morilog\Jalali\Jalalian;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Str;


class VoiceMessageController extends Controller
{
    public function index(Builder $builder)
    {
        if (request()->ajax()) {
            return DataTables::of(
                VoiceMessage::query()->orderBy('status', 'asc')->orderBy('created_at', 'desc')
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
                    return '<a href="' . route('operator.voiceMessages.accept', $item->id) . '" class="btn btn-sm btn-success">تایید و ارسال</a>';
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
            ['data' => 'qty', 'name' => 'qty', 'title' => 'تعداد واحد های ارسالی'],
            ['data' => 'status', 'name' => 'status', 'title' => 'وضعیت'],
            ['data' => 'action', 'name' => 'action', 'title' => 'عملیات', 'orderable' => false, 'searchable' => false],
        ])->parameters([
            'dom' => '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-1 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            'buttons' => ['csv', 'excel', 'print', 'copy'],
            'language' => [
                'url' => url('DataTables/Persian.json')
            ],
        ]);

        return view('operator.voiceMessages', compact('table'));
    }

    public function accept(VoiceMessage $voiceMessage)
    {
        $voiceMessage->update([
            'status' => 'sending'
        ]);

        $items = [];

        foreach ($voiceMessage->units as $unit_id) {
            $unit = BuildingUnit::find($unit_id);
            $items[] = new SendVoiceMessage($unit, $voiceMessage->pattern);
        }

        $batch = Bus::batch($items)
        ->allowFailures()
        ->then(function ($batch) {
            $voiceMessage = VoiceMessage::where('batch_id', $batch->id)->first();
            $voiceMessage->status = 'completed';
            $voiceMessage->save();
        })->catch(function ($batch, $e) {
            $voiceMessage = VoiceMessage::where('batch_id', $batch->id)->first();
            $voiceMessage->status = 'failed';
            $voiceMessage->save();
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در ارسال پیام صوتی', $e->getMessage() . "\n" . $batch->id));
        })->finally(function ($batch) {
            // The batch has finished executing...
        })->dispatch();

        $voiceMessage->batch_id = $batch->id;
        $voiceMessage->save();

        session()->flash('success', 'پیام صوتی با موفقیت در صف ارسال قرار گرفت.');
        return redirect()->route('operator.voiceMessages.index');
    }
}
