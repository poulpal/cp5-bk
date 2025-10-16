<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\SupportTicketResource;
use App\Mail\CustomMail;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            // if (auth()->user()->mobile == '09125052364') {
            //     return $next($request);
            // }
            if (auth()->user()->role !== 'building_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
                ], 403);
            }
            if (auth()->user()->role == 'building_manager') {
                if ('other' == auth()->buildingManager()->building_manager_type) {
                    return response()->json([
                        'success' => false,
                        'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
                    ], 403);
                }
            }

            return $next($request);
        })->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supportTickets = auth()->buildingManager()->building->supportTickets();

        if (request()->has('sort') && request()->sort) {
            $supportTickets = $supportTickets->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $supportTickets = $supportTickets->orderBy('updated_at', 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $supportTickets = $supportTickets->paginate(request()->perPage ?? 20);
        } else {
            $supportTickets = $supportTickets->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($supportTickets, SupportTicketResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => SupportTicketResource::collection($supportTickets),
                ]
            ]);
        }
    }

    public function store(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'section' => ['required', 'string', 'in:support,tech,finance'],
            'message' => ['required', 'string', 'max:500'],
            'attachments' => ['array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $supportTicket = $building->supportTickets()->create([
            'subject' => $request->subject,
            'status' => 'open',
            'section' => $request->section,
        ]);

        $supportTicket->replies()->create([
            'from' => 'user',
            'user_id' => auth()->buildingManager()->id,
            'message' => $request->message,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $supportTicket->attachments()->create([
                    'file' => $attachment->store('attachments', 'public'),
                ]);
            }
        }

        Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(new CustomMail(
            __("تیکت پشتیبانی جدید") . " - " . str($supportTicket->subject) . " - " . str($supportTicket->id),
            "نام ساختمان : " . $building->name . "<br>" .
                "موضوع : " . $supportTicket->subject . "<br>" .
                "بخش : " . $supportTicket->section . "<br>" .
                "متن پیام : <br> " . $request->message . "<br>"
        ));

        return response()->json([
            'success' => true,
            'message' => 'تیکت پشتیبانی با موفقیت ایجاد شد.',
            'data' => [
                'supportTicket' => $supportTicket
            ]
        ], 201);
    }

    public function show(SupportTicket $supportTicket)
    {
        if ($supportTicket->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تیکت را ندارید.'
            ], 403);
        }
        $supportTicket->load('replies');

        return response()->json([
            'success' => true,
            'data' => [
                'supportTicket' => $supportTicket
            ]
        ], 200);
    }

    public function reply(SupportTicket $supportTicket, Request $request)
    {
        if ($supportTicket->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تیکت را ندارید.'
            ], 403);
        }

            $validator = Validator::make($request->all(), [
                'message' => ['required', 'string', 'max:500'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $supportTicket->replies()->create([
                'from' => 'user',
                'user_id' => auth()->buildingManager()->id,
                'message' => $request->message,
            ]);

            $supportTicket->status = 'open';
            $supportTicket->save();

            Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(new CustomMail(
                __("پاسخ به تیکت پشتیبانی") . " - " . str($supportTicket->subject) . " - " . str($supportTicket->id),
                "نام ساختمان : " . $supportTicket->building->name . "<br>" .
                    "موضوع : " . $supportTicket->subject . "<br>" .
                    "بخش : " . $supportTicket->section . "<br>" .
                    "متن پیام : <br> " . $request->message . "<br>"
            ));

            return response()->json([
                'success' => true,
                'message' => 'پاسخ شما با موفقیت ارسال شد.',
                'data' => [
                    'supportTicket' => $supportTicket
                ]
            ], 201);
    }
}
