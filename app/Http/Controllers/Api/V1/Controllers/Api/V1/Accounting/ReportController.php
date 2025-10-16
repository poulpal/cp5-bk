<?php

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResource;
use App\Models\Accounting\AccountingAccount;
use App\Models\Accounting\AccountingTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    public function __construct()
    {
       $this->middleware('hasModule:accounting-advanced')->only(['journal', 'ledger']);
$this->middleware('hasModule:accounting-advanced')->only(['trialBalance', 'profitAndLoss', 'balanceSheet']);
    }

    public function journal(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $transactions = $building->accountingTransactions()
            ->with([
                'document:id,document_number',
                'account:id,name,parent_id',
                'detail:id,name,code',
                'account.parent.parent.parent.parent:id'
            ])
            ->select(
                'accounting_transactions.id',
                'accounting_transactions.accounting_account_id',
                'accounting_transactions.accounting_detail_id',
                'accounting_transactions.accounting_document_id',
                'accounting_transactions.credit',
                'accounting_transactions.debit',
                'accounting_transactions.created_at',
                'accounting_transactions.description'
            );

        if ($request->has('sort') && $request->sort) {
            if ($request->sort == 'account') {
                $transactions = $transactions
                    ->join('accounting_accounts', 'accounting_accounts.id', '=', 'accounting_transactions.accounting_account_id')
                    ->orderBy('accounting_accounts.name', $request->order ?? 'desc');
            } else {
                $transactions = $transactions->orderBy('accounting_transactions.' . $request->sort, $request->order ?? 'desc');
            }
        } else {
            $transactions = $transactions->orderBy('accounting_transactions.created_at', 'desc');
        }

        if ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $transactions = $transactions->whereBetween('accounting_transactions.created_at', [$start, $end]);
        }

        if ($request->has('filters') && $request->filters) {
            $filters = json_decode($request->filters);
            foreach ($filters as $filter) {
                $key = $filter->columnName;
                $value = $filter->value;
                if ($key == 'account') {
                    $transactions = $transactions->whereHas('account', function ($query) use ($value) {
                        $query->where('name', 'like', "%$value%");
                    });
                } elseif ($key == 'document_number') {
                    $transactions = $transactions->whereHas('document', function ($query) use ($value) {
                        $query->where('document_number', 'like', "%$value%");
                    });
                } elseif ($key == 'credit' || $key == 'debit') {
                    $transactions = $transactions->where('accounting_transactions.' . $key, floatval($value));
                } elseif ($key == 'created_at') {
                    $date_start = Carbon::parse($value)->startOfDay();
                    $date_end = Carbon::parse($value)->endOfDay();
                    $transactions = $transactions->whereBetween('accounting_transactions.created_at', [$date_start, $date_end]);
                } else {
                    $transactions = $transactions->where('accounting_transactions.' . $key, 'like', "%$value%");
                }
            }
        }

        if ($request->has('paginate') && $request->paginate) {
            $transactions = $transactions->paginate($request->perPage ?? 20);
            return response()->paginate($transactions, DefaultResource::class);
        } else {
            $transactions = $transactions->get();
            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions,
                ]
            ]);
        }
    }

    public function ledger(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $transactions = $building->accountingTransactions()
            ->with([
                'document:id,document_number',
                'account:id,name,parent_id',
                'account.parent.parent.parent.parent:id',
                'detail:id,name,code',
            ])
            ->select(
                'accounting_transactions.id',
                'accounting_transactions.accounting_account_id',
                'accounting_transactions.accounting_detail_id',
                'accounting_transactions.accounting_document_id',
                'accounting_transactions.credit',
                'accounting_transactions.debit',
                'accounting_transactions.created_at',
                'accounting_transactions.description'
            )
            ->orderBy('accounting_transactions.created_at', 'asc');


        if ($request->has('accounts') && $request->accounts) {
            $codes = $request->accounts;
            $accounts = $building->accountingAccounts()->whereIn('code', $codes)->get(['id', 'code', 'parent_id', 'building_id']);
            $ids = collect();
            foreach ($accounts as $account) {
                $ids = $ids->merge($this->getAllChildrenIds($account));
            }
            $ids = $ids->unique();
            $transactions = $transactions->whereIn('accounting_account_id', $ids);
            if ($request->has('details') && $request->details) {
                $detailIds = $building->accountingDetails()->whereIn('code', $request->details)->pluck('id');
                $transactions = $transactions->whereIn('accounting_transactions.accounting_detail_id', $detailIds);
            }
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => [],
                ]
            ]);
        }

        $remaining_transactions = null;
        if ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $remaining_transactions = (clone $transactions)->where('accounting_transactions.created_at', '<', $start)->get(['debit', 'credit']);
            $transactions = $transactions->whereBetween('accounting_transactions.created_at', [$start, $end]);
        }

        $transactions = $transactions->get();
        $balance = 0;
        if ($remaining_transactions) {
            $debit = $remaining_transactions->sum('debit');
            $credit = $remaining_transactions->sum('credit');
            $balance = $credit - $debit;
            if ($debit > 0 || $credit > 0) {
                $transaction = new AccountingTransaction();
                $transaction->balance = $balance;
                $transaction->credit = $credit;
                $transaction->debit = $debit;
                $transaction->created_at = isset($start) ? $start->copy()->addDays(-1) : null;
                $transaction->description = __("مانده از قبل");
                $transactions->prepend($transaction);
            }
        }
        if (isset($balance)) {
            foreach ($transactions as $transaction) {
                if (isset($transaction->balance)) {
                    continue;
                }
                $balance += $transaction->credit - $transaction->debit;
                $transaction->balance = $balance;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
            ]
        ]);
    }

    public function trialBalance(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $accounts = $building->accountingAccounts()->with('children.children.children.children')->get();

        if (!($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'accounts' => [],
                ]
            ]);
        }

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        // Helper to recursively collect all descendant IDs from the loaded account tree
        $collectIds = function($account) use (&$collectIds) {
            $ids = [$account->id];
            if ($account->relationLoaded('children')) {
                foreach ($account->children as $child) {
                    $ids = array_merge($ids, $collectIds($child));
                }
            }
            return $ids;
        };

        $accountIdsMap = [];
        foreach ($accounts as $account) {
            $accountIdsMap[$account->id] = $collectIds($account);
        }
        $allAccountIds = array_unique(array_merge(...array_values($accountIdsMap)));

        $transactionsInRange = AccountingTransaction::whereIn('accounting_account_id', $allAccountIds)
            ->whereBetween('accounting_transactions.created_at', [$start, $end])
            ->select('accounting_account_id', 'debit', 'credit')
            ->get();
        $transactionsBefore = AccountingTransaction::whereIn('accounting_account_id', $allAccountIds)
            ->where('accounting_transactions.created_at', '<', $start)
            ->select('accounting_account_id', 'debit', 'credit')
            ->get();

        $inRangeSums = $transactionsInRange->groupBy('accounting_account_id')->map(function($group) {
            return [
                'debit' => $group->sum('debit'),
                'credit' => $group->sum('credit'),
            ];
        });
        $beforeSums = $transactionsBefore->groupBy('accounting_account_id')->map(function($group) {
            return [
                'debit' => $group->sum('debit'),
                'credit' => $group->sum('credit'),
            ];
        });

        foreach ($accounts as $account) {
            $ids = $accountIdsMap[$account->id];
            $account->debit = 0;
            $account->credit = 0;
            $account->remaining_debit = 0;
            $account->remaining_credit = 0;
            foreach ($ids as $id) {
                $account->debit += $inRangeSums[$id]['debit'] ?? 0;
                $account->credit += $inRangeSums[$id]['credit'] ?? 0;
                $account->remaining_debit += $beforeSums[$id]['debit'] ?? 0;
                $account->remaining_credit += $beforeSums[$id]['credit'] ?? 0;
            }
            $account->balance = $account->credit - $account->debit;
            $account->debit_balance = $account->balance < 0 ? abs($account->balance) : 0;
            $account->credit_balance = $account->balance > 0 ? abs($account->balance) : 0;
            $account->hasItems = $account->children->count() > 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
            ]
        ]);
    }

    public function profitAndLoss(Request $request)
    {
        $building = auth()->buildingManager()->building;
        if ($building->name_en == 'atishahr') {
            $accounts = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '6%')
                        ->orWhere('code', 'like', '8%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'desc')
                ->get();
        } else {
            $accounts = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '6%')
                        ->orWhere('code', 'like', '7%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'desc')
                ->get();
        }

        if (!($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date)) {
            foreach ($accounts as $account) {
                $account->debit = 0;
                $account->credit = 0;
                $account->remaining_debit = 0;
                $account->remaining_credit = 0;
                $account->balance = 0;
                $account->debit_balance = 0;
                $account->credit_balance = 0;
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'accounts' => $accounts,
                    'pnl' => 0
                ]
            ]);
        }

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        // Helper to recursively collect all descendant IDs from the loaded account tree
        $collectIds = function($account) use (&$collectIds) {
            $ids = [$account->id];
            if ($account->relationLoaded('children')) {
                foreach ($account->children as $child) {
                    $ids = array_merge($ids, $collectIds($child));
                }
            }
            return $ids;
        };

        $accountIdsMap = [];
        foreach ($accounts as $account) {
            $accountIdsMap[$account->id] = $collectIds($account);
        }
        $allAccountIds = array_unique(array_merge(...array_values($accountIdsMap)));

        $transactionsInRange = AccountingTransaction::whereIn('accounting_account_id', $allAccountIds)
            ->whereBetween('accounting_transactions.created_at', [$start, $end])
            ->select('accounting_account_id', 'debit', 'credit')
            ->get();
        $transactionsBefore = AccountingTransaction::whereIn('accounting_account_id', $allAccountIds)
            ->where('accounting_transactions.created_at', '<', $start)
            ->select('accounting_account_id', 'debit', 'credit')
            ->get();

        $inRangeSums = $transactionsInRange->groupBy('accounting_account_id')->map(function($group) {
            return [
                'debit' => $group->sum('debit'),
                'credit' => $group->sum('credit'),
            ];
        });
        $beforeSums = $transactionsBefore->groupBy('accounting_account_id')->map(function($group) {
            return [
                'debit' => $group->sum('debit'),
                'credit' => $group->sum('credit'),
            ];
        });

        foreach ($accounts as $account) {
            $ids = $accountIdsMap[$account->id];
            $account->debit = 0;
            $account->credit = 0;
            $account->remaining_debit = 0;
            $account->remaining_credit = 0;
            foreach ($ids as $id) {
                $account->debit += $inRangeSums[$id]['debit'] ?? 0;
                $account->credit += $inRangeSums[$id]['credit'] ?? 0;
                $account->remaining_debit += $beforeSums[$id]['debit'] ?? 0;
                $account->remaining_credit += $beforeSums[$id]['credit'] ?? 0;
            }
            $account->balance = $account->credit - $account->debit;
            $account->debit_balance = $account->balance < 0 ? abs($account->balance) : 0;
            $account->credit_balance = $account->balance > 0 ? abs($account->balance) : 0;
        }

        $pnl =  $accounts->sum(function ($account) {
            return $account->parent_id == null ? $account->balance : 0;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $accounts,
                'pnl' => $pnl
            ]
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $building = auth()->buildingManager()->building;
        // Eager load children for recursive id collection
        $accountWithChildren = fn($query) => $query->with('children.children.children.children');
        $asset_accounts = $building->accountingAccounts()
            ->where(function ($query) {
                $query->where('code', 'like', '1%')
                    ->orWhere('code', 'like', '2%');
            })
            ->with('children.children.children.children')
            ->orderBy('code', 'asc')
            ->get();
        if ($building->name_en == 'atishahr') {
            $liability_accounts = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '3%')
                        ->orWhere('code', 'like', '4%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'asc')
                ->get();
        } else {
            $liability_accounts = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '4%')
                        ->orWhere('code', 'like', '5%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'asc')
                ->get();
        }
        if ($building->name_en == 'atishahr') {
            $equity_accounts = $building->accountingAccounts()
                ->where('code', 'like', '5%')
                ->with('children.children.children.children')
                ->orderBy('code', 'asc')
                ->get();
        } else {
            $equity_accounts = $building->accountingAccounts()
                ->where('code', 'like', '3%')
                ->with('children.children.children.children')
                ->orderBy('code', 'asc')
                ->get();
        }
        if ($building->name_en == 'atishahr') {
            $pnls = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '8%')
                        ->orWhere('code', 'like', '6%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'desc')
                ->get();
        } else {
            $pnls = $building->accountingAccounts()
                ->where(function ($query) {
                    $query->where('code', 'like', '7%')
                        ->orWhere('code', 'like', '6%');
                })
                ->with('children.children.children.children')
                ->orderBy('code', 'desc')
                ->get();
        }

        // Helper to recursively collect all descendant IDs from the loaded account tree
        $collectIds = function($account) use (&$collectIds) {
            $ids = [$account->id];
            if ($account->relationLoaded('children')) {
                foreach ($account->children as $child) {
                    $ids = array_merge($ids, $collectIds($child));
                }
            }
            return $ids;
        };

        $allGroups = [
            'asset_accounts' => $asset_accounts,
            'liability_accounts' => $liability_accounts,
            'equity_accounts' => $equity_accounts,
            'pnls' => $pnls,
        ];
        $allAccountIds = [];
        $accountIdsMap = [];
        foreach ($allGroups as $groupName => $accounts) {
            foreach ($accounts as $account) {
                $ids = $collectIds($account);
                $accountIdsMap[$groupName][$account->id] = $ids;
                $allAccountIds = array_merge($allAccountIds, $ids);
            }
        }
        $allAccountIds = array_unique($allAccountIds);

        if (!($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date)) {
            foreach (['asset_accounts', 'liability_accounts', 'equity_accounts', 'pnls'] as $groupName) {
                foreach ($$groupName as $account) {
                    $account->debit = 0;
                    $account->credit = 0;
                    $account->balance = 0;
                }
            }
            $pnl = 0;
        } else {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $transactions = AccountingTransaction::whereIn('accounting_account_id', $allAccountIds)
                ->whereBetween('accounting_transactions.created_at', [$start, $end])
                ->select('accounting_account_id', 'debit', 'credit')
                ->get();
            $sums = $transactions->groupBy('accounting_account_id')->map(function($group) {
                return [
                    'debit' => $group->sum('debit'),
                    'credit' => $group->sum('credit'),
                ];
            });
            foreach ($allGroups as $groupName => $accounts) {
                foreach ($accounts as $account) {
                    $ids = $accountIdsMap[$groupName][$account->id];
                    $account->debit = 0;
                    $account->credit = 0;
                    foreach ($ids as $id) {
                        $account->debit += $sums[$id]['debit'] ?? 0;
                        $account->credit += $sums[$id]['credit'] ?? 0;
                    }
                    $account->balance = $account->credit - $account->debit;
                }
            }
            $pnl =  $pnls->sum(function ($account) {
                return $account->parent_id == null ? $account->balance : 0;
            });
        }

        if ($building->name_en == 'atishahr') {
            $pnl_account = new AccountingAccount();
            $pnl_account->name = __("سود یا زیان");
            $pnl_account->code = '5x';
            $pnl_account->parent_id = $building->accountingAccounts()->where('code', '5')->first()->id;
            $pnl_account->id = -1;
            $pnl_account->balance = $pnl;
        } else {
            $pnl_account = new AccountingAccount();
            $pnl_account->name = __("سود یا زیان");
            $pnl_account->code = '3x';
            $pnl_account->parent_id = $building->accountingAccounts()->where('code', '3')->first()->id;
            $pnl_account->id = -1;
            $pnl_account->balance = $pnl;
        }
        $equity_accounts->push($pnl_account);

        if ($building->name_en == 'atishahr') {
            $equity_accounts = $equity_accounts->map(function ($account) use ($pnl) {
                if ($account['code'] == '5') {
                    $account['balance'] = $account['balance'] + $pnl;
                }
                return $account;
            });
        } else {
            $equity_accounts = $equity_accounts->map(function ($account) use ($pnl) {
                if ($account['code'] == '3') {
                    $account['balance'] = $account['balance'] + $pnl;
                }
                return $account;
            });
        }

        $asset_accounts = $asset_accounts->map(function ($account) {
            $account['balance'] = $account['balance'] * -1;
            return $account;
        });

        $asset_total = $asset_accounts->sum(function ($account) {
            return $account->parent_id == null ? $account->balance : 0;
        });
        $liability_total = $liability_accounts->sum(function ($account) {
            return $account->parent_id == null ? $account->balance : 0;
        });
        $equity_total = $equity_accounts->sum(function ($account) {
            return $account->parent_id == null ? $account->balance : 0;
        });



        return response()->json([
            'success' => true,
            'data' => [
                'asset_accounts' => $asset_accounts,
                'liability_accounts' => $liability_accounts,
                'equity_accounts' => $equity_accounts,
                'asset_total' => $asset_total,
                'liability_equity_total' => $liability_total + $equity_total,
            ]
        ]);
    }

    private function getAllChildrenIds($account)
    {
        return AccountingAccount::where('building_id', $account->building_id)
            ->where('code', 'like', $account->code . '%')
            ->pluck('id')
            ->toArray();
        $ids = [$account->id];
        foreach ($account->children as $child) {
            $ids[] = $child->id;
            foreach ($child->children as $grandchild) {
                $ids[] = $grandchild->id;
                foreach ($grandchild->children as $greatgrandchild) {
                    $ids[] = $greatgrandchild->id;
                }
            }
        }
        return $ids;
    }

    private function addBalance($accounts)
    {
        foreach ($accounts as $account) {
            $transactions = AccountingTransaction::query();
            if (request()->has('start_date') && request()->start_date && request()->has('end_date') && request()->end_date) {
                $start = Carbon::parse(request()->start_date)->startOfDay();
                $end = Carbon::parse(request()->end_date)->endOfDay();
                $transactions = $transactions->whereBetween('accounting_transactions.created_at', [$start, $end]);
                $ids = $this->getAllChildrenIds($account);
                $transactions = $transactions->whereIn('accounting_account_id', $ids);
                $debit_and_credit = $transactions->selectRaw('sum(debit) as debit, sum(credit) as credit')->first();
                $account->debit = $debit_and_credit->debit ?? 0;
                $account->credit = $debit_and_credit->credit ?? 0;
                $account->balance = $account->credit - $account->debit;
            }
        }
    }
}
