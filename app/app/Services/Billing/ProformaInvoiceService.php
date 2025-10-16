<?php

namespace App\Services\Billing;

use App\Models\ProformaInvoice;
use App\Models\ProformaItem;
use App\Models\Package;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProformaInvoiceService
{
    /**
     * ایجاد پیش‌فاکتور از یکی از دو ورودی:
     *  - items[]  (ترجیح: ماژول‌های انتخابی)
     *  - package_slug + period
     */
    public function create(array $data, int $userId): ProformaInvoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $lines = [];

            if (!empty($data['items'])) {
                foreach ($data['items'] as $it) {
                    $qty = (int) $it['qty'];
                    $unit = (int) $it['unit_price'];
                    $lines[] = [
                        'title'       => $it['title'],
                        'description' => $it['description'] ?? null,
                        'qty'         => $qty,
                        'unit_price'  => $unit,
                        'line_total'  => $qty * $unit,
                        'meta'        => Arr::only($it, ['code','sku'])
                    ];
                }
            } else {
                // حالت پکیج
                $slug = (string) ($data['package_slug'] ?? '');
                $period = (string) ($data['period'] ?? 'monthly');
                $pkg = Package::where('slug', $slug)->firstOrFail();

                $unit = $period === 'yearly'
                    ? (int) $pkg->price_yearly
                    : (int) $pkg->price_monthly;

                $lines[] = [
                    'title'       => 'بسته ' . ($pkg->title ?? $slug) . ($period === 'yearly' ? ' (سالیانه)' : ' (ماهانه)'),
                    'description' => $pkg->subtitle ?? null,
                    'qty'         => 1,
                    'unit_price'  => $unit,
                    'line_total'  => $unit,
                    'meta'        => ['package_slug' => $slug, 'period' => $period]
                ];
            }

            // محاسبه جمع/تخفیف/مالیات/کل
            $subtotal = array_sum(array_map(fn($l) => $l['line_total'], $lines));
            $discount = (int) ($data['discount'] ?? 0);
            $taxPercent = (int) ($data['tax_percent'] ?? 0);
            $taxBase = max(0, $subtotal - $discount);
            $tax = (int) floor($taxBase * ($taxPercent / 100));
            $total = max(0, $subtotal - $discount + $tax);

            // ایجاد هدر پیش‌فاکتور
            $proforma = ProformaInvoice::create([
                'user_id'        => $userId,
                'building_id'    => $data['building_id'] ?? null,
                'package_id'     => null, // در حالت پکیج، meta حاوی slug است؛ نگه‌داشتن null بهتر است
                'proforma_number'=> ProformaInvoice::nextNumber(),
                'period'         => (string) ($data['period'] ?? 'monthly'),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'currency'       => 'IRR',
                'status'         => 'issued',
                'issued_at'      => now(),
                'expires_at'     => now()->addDays(7),
                'buyer_meta'     => Arr::get($data, 'buyer', []),
                'seller_meta'    => $this->defaultSellerMeta(),
                'meta'           => Arr::get($data, 'meta', []),
            ]);

            // ثبت آیتم‌ها
            foreach ($lines as $l) {
                ProformaItem::create([
                    'proforma_invoice_id' => $proforma->id,
                    'title'       => $l['title'],
                    'description' => $l['description'],
                    'qty'         => $l['qty'],
                    'unit_price'  => $l['unit_price'],
                    'line_total'  => $l['line_total'],
                    'meta'        => $l['meta'] ?? [],
                ]);
            }

            return $proforma;
        });
    }

    public function defaultSellerMeta(): array
    {
        return [
            'brand'   => 'ChargePal',
            'website' => 'chargepal.ir',
            'phone'   => '09981319639',
            'address' => 'تهران، ایران',
            'logo_url'=> null,
        ];
    }
}
