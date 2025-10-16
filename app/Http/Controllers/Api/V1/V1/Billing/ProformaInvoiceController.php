<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proforma\StoreProformaRequest;
use App\Models\ProformaInvoice;
use App\Services\Billing\BillingPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProformaInvoiceController extends Controller
{
    // POST /v1/proforma
    public function store(StoreProformaRequest $request)
    {
        $user    = $request->user();
        $payload = $request->validated();

        $period  = BillingPeriod::normalize($payload['period'] ?? 'monthly');
        $tax     = (int) ($payload['tax_percent'] ?? 0);

        // اطمینان از وجود آیتم‌ها
        $items = array_map(function ($i) {
            return [
                'title'       => $i['title'],
                'description' => $i['description'] ?? null,
                'qty'         => (int) ($i['qty'] ?? 1),
                'unit_price'  => (int) ($i['unit_price'] ?? 0),
            ];
        }, $payload['items']);

        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += $it['qty'] * $it['unit_price'];
        }

        // تخفیف سمت سرور (اگر لازم است می‌شود از meta/کد تخفیف نیز اعمال کرد)
        $discount = 0;

        $taxable  = max($subtotal - $discount, 0);
        $taxAmt   = (int) round($taxable * ($tax / 100));
        $total    = $taxable + $taxAmt;

        // ذخیره پروفرما
        $pf = new ProformaInvoice();
        $pf->user_id   = $user->id;
        $pf->building_id = $payload['building_id'] ?? null;
        $pf->package_id  = $payload['package_id'] ?? null;
        $pf->proforma_number = now()->format('Ymd-His') . '-' . $user->id;
        $pf->period    = $period;
        $pf->subtotal  = $subtotal;
        $pf->discount  = $discount;
        $pf->tax       = $taxAmt;
        $pf->total     = $total;
        $pf->currency  = 'IRR';
        $pf->status    = 'issued';
        $pf->issued_at = now();
        $pf->expires_at= now()->addDays(7);
        $pf->buyer_meta  = $payload['buyer_meta'] ?? null;
        $pf->seller_meta = $payload['seller_meta'] ?? null;
        $pf->meta        = $payload['meta'] ?? null;
        $pf->save();

        // ذخیرهٔ آیتم‌ها (اگر مدل/ریلیشن items داری، همینجا بساز)
        if (method_exists($pf, 'items')) {
            $pf->items()->createMany(array_map(function ($it) {
                return [
                    'title'       => $it['title'],
                    'description' => $it['description'],
                    'qty'         => $it['qty'],
                    'unit_price'  => $it['unit_price'],
                ];
            }, $items));
        }

        return response()->json([
            'data' => [
                'id'              => $pf->id,
                'proforma_number' => $pf->proforma_number,
                'total'           => $pf->total,
                'currency'        => $pf->currency,
                'period'          => $pf->period,
                'expires_at'      => $pf->expires_at,
            ]
        ], 201);
    }

    // GET /v1/proforma/{id}
    public function show($id)
    {
        $pf = ProformaInvoice::with('items')->findOrFail($id);
        $this->authorizeView($pf);
        return response()->json(['data' => $pf]);
    }

    // GET /v1/proforma/{id}/html
    public function html($id)
    {
        $pf = ProformaInvoice::with('items')->findOrFail($id);
        $this->authorizeView($pf);
        return response()->view('proforma.show', compact('pf'));
    }

    // GET /v1/proforma/{id}/pdf
    public function pdf($id)
    {
        $pf = ProformaInvoice::with('items')->findOrFail($id);
        $this->authorizeView($pf);
        $pdf = Pdf::loadView('proforma.show', ['pf' => $pf])->setPaper('a4', 'portrait');
        return $pdf->stream('Proforma-' . $pf->proforma_number . '.pdf');
    }

    protected function authorizeView(ProformaInvoice $pf): void
    {
        if (Auth::check()) {
            $u = Auth::user();
            if ($u->id === $pf->user_id || (method_exists($u, 'can') && $u->can('admin'))) return;
        }
        abort(403);
    }
}
