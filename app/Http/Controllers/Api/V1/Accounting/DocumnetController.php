<?php

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResource;
use App\Models\Accounting\AccountingDocument;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DocumnetController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show', 'getNewDocumentNumber']);
        $this->middleware('hasModule:accounting-basic')->only(['index', 'show', 'getNewDocumentNumber']);
        $this->middleware('hasModule:accounting-advanced-1')->except(['index', 'show', 'getNewDocumentNumber']);
    }

    public function getNewDocumentNumber(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $documentNumber = $building->accountingDocuments()
            ->max('document_number');
        $documentNumber = $documentNumber ? $documentNumber + 1 : 1;
        return response()->json([
            'success' => true,
            'data' => [
                'document_number' => $documentNumber
            ]
        ]);
    }

    public function index()
    {
        $building = auth()->buildingManager()->building;
        $documents = $building->accountingDocuments();

        if (request()->has('sort') && request()->sort) {
            $documents = $documents->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $documents = $documents->orderBy('created_at', 'desc');
        }

        $documents = $documents->orderBy('id', 'desc');

        if (request()->has('start_date') && request()->start_date && request()->has('end_date') && request()->end_date) {
            $start = Carbon::parse(request()->start_date)->startOfDay();
            $end = Carbon::parse(request()->end_date)->endOfDay();
            $documents = $documents->where('accounting_documents.created_at', '>=', $start)->where('accounting_documents.created_at', '<=', $end);
        }

        if (request()->has('filters') && request()->filters) {
            $filters = json_decode(request()->filters);
            foreach ($filters as $filter) {
                $key = $filter->columnName;
                $value = $filter->value;
                if ($key == 'amount') {
                    $documents = $documents->where($key, floatval($value));
                } elseif ($key == 'created_at') {
                    $date_start = Carbon::parse($value)->startOfDay();
                    $date_end = Carbon::parse($value)->endOfDay();
                    $documents = $documents->where('accounting_documents.created_at', '>=', $date_start)->where('accounting_documents.created_at', '<=', $date_end);
                } else {
                    $documents = $documents->where('accounting_documents.' . $key, 'like', '%' . $value . '%');
                }
            }
        }

        if (request()->has('paginate') && request()->paginate) {
            $documents = $documents->paginate(request()->perPage ?? 20);
        } else {
            $documents = $documents->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($documents, DefaultResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'documents' => $documents,
                ]
            ]);
        }
    }

    public function show($document_number)
    {
        $building = auth()->buildingManager()->building;
        $document = $building->accountingDocuments()->where('document_number', $document_number)
            ->with('transactions')->with('transactions.account')->with('transactions.detail')
            ->firstOrFail();
        return response()->json([
            'success' => true,
            'data' => [
                'document' => $document
            ]
        ]);
    }

    public function store(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $validator = Validator::make(
            $request->all(),
            [
                'description' => 'required|string|max:255',
                'document_number' => [
                    'required', 'integer',
                    Rule::unique('accounting_documents', 'document_number')->where(function ($query) use ($building) {
                        return $query->where('building_id', $building->id)->whereNull('deleted_at');
                    })
                ],
                'date' => 'required|date',
                'transactions' => 'required|array|min:2',
                'transactions.*.account' => [
                    'required', 'string', 'max:255',
                    Rule::exists('accounting_accounts', 'code')->where(function ($query) use ($building) {
                        return $query->where('building_id', $building->id)->whereNull('deleted_at');
                    })
                ],
                'transactions.*.detail' => [
                    'nullable', 'string', 'max:255',
                    Rule::exists('accounting_details', 'code')->where(function ($query) use ($building) {
                        return $query->where('building_id', $building->id)->whereNull('deleted_at');
                    })
                ],
                'transactions.*.description' => 'required|string|max:255',
                'transactions.*.credit' => 'required|numeric|min:0',
                'transactions.*.debit' => 'required|numeric|min:0',
            ],
            $messages = [
                'transactions.*.description.required' => 'شرح ردیف :position  الزامی است',
                'transactions.*.credit.required' => 'مبلغ بستانکار ردیف :position  الزامی است',
                'transactions.*.debit.required' => 'مبلغ بدهکار ردیف :position  الزامی است',
                'transactions.*.account.required' => 'حساب ردیف :position  الزامی است',
                'transactions.*.account.exists' => 'حساب ردیف :position  یافت نشد',
                'transactions.*.detail.required' => 'تفضیل ردیف :position  الزامی است',
                'transactions.*.detail.exists' => 'تفضیل ردیف :position  یافت نشد',
                'transactions.*.credit.numeric' => 'مبلغ بستانکار ردیف :position  باید عدد باشد',
                'transactions.*.debit.numeric' => 'مبلغ بدهکار ردیف :position  باید عدد باشد',
                'transactions.*.credit.min' => 'مبلغ بستانکار ردیف :position  نمی تواند منفی باشد',
                'transactions.*.debit.min' => 'مبلغ بدهکار ردیف :position  نمی تواند منفی باشد',
                'document_number.required' => __("شماره سند الزامی است"),
                'document_number.integer' => __("شماره سند باید عدد باشد"),
                'document_number.unique' => __("شماره سند تکراری است"),
                'date.required' => __("تاریخ الزامی است"),
                'date.date' => __("تاریخ باید تاریخ معتبر باشد"),
                'description.required' => __("شرح سند الزامی است"),
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $building) {
            $document = $building->accountingDocuments()->create([
                'description' => $request->description,
                'document_number' => $request->document_number,
                'amount' => array_sum(array_column($request->transactions, 'debit')),
                'created_at' => $request->date,
            ]);

            foreach ($request->transactions as $index => $transaction) {
                $account = $building->accountingAccounts()
                    ->where('code', $transaction['account'])
                    ->first();
                $detail = $building->accountingDetails()
                    ->where('code', $transaction['detail'])
                    ->first();
                if ($transaction['debit'] == 0 && $transaction['credit'] == 0) {
                    throw new \Exception(__("مبلغ بدهکار و بستانکار ردیف") . $index + 1 . __(" نمی تواند هر دو صفر باشد"));
                }
                if ($transaction['debit'] > 0 && $transaction['credit'] > 0) {
                    throw new \Exception(__("مبلغ بدهکار و بستانکار ردیف") . $index + 1 . __(" نمی تواند هر دو مقدار داشته باشد"));
                }
                $document->transactions()->create([
                    'accounting_account_id' => $account->id,
                    'accounting_detail_id' => $detail ? $detail->id : null,
                    'description' => $transaction['description'],
                    'debit' => $transaction['debit'],
                    'credit' => $transaction['credit'],
                    'created_at' => $request->date,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => __("سند با موفقیت ثبت شد"),
        ]);
    }

    public function destroy($document_number)
    {
        $building = auth()->buildingManager()->building;
        $document = $building->accountingDocuments()->where('document_number', $document_number)->firstOrFail();
        $document->transactions()->delete();
        $document->delete();
        return response()->json([
            'success' => true,
            'message' => __("سند با موفقیت حذف شد"),
        ]);
    }
}
