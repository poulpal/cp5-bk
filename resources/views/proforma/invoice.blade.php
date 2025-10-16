{{-- ===== resources/views/proforma/invoice.blade.php ===== --}}
@php
  $rtl = true;
  function nf($n){ return number_format((int)$n); }
  $title = 'پیش‌فاکتور';
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>{{ $title }}</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Vazirmatn, Tahoma, sans-serif; margin: 24px; color: #222; }
    .wrap { max-width: 900px; margin: 0 auto; }
    .h1 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .muted { color: #666; font-size: 12px; }
    .row { display: flex; gap: 16px; align-items: flex-start; }
    .col { flex: 1; }
    .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin: 12px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #e5e5e5; padding: 8px 10px; font-size: 13px; }
    th { background: #fafafa; text-align: center; }
    td { vertical-align: top; }
    .tr { text-align: right; } .tc { text-align: center; } .tl { text-align: left; }
    .total { font-weight: 700; }
    .mb8 { margin-bottom: 8px; } .mb16{ margin-bottom: 16px; } .mb24{ margin-bottom: 24px; }
    .badge { display:inline-block; background:#eef; border:1px solid #ccd; padding:2px 8px; border-radius:999px; font-size:12px;}
    .footer { margin-top: 24px; font-size: 12px; color:#666;}
  </style>
</head>
<body>
<div class="wrap">
  <div class="row mb16">
    <div class="col">
      <div class="h1">پیش‌فاکتور</div>
      <div class="muted">
        شماره: {{ $pi['id'] ?? '—' }} |
        تاریخ: {{ \Carbon\Carbon::parse($pi['created_at'] ?? now())->format('Y/m/d H:i') }}
      </div>
      @if(($pi['period'] ?? null))
        <div class="badge">دوره: {{ $pi['period'] === 'monthly' ? 'ماهانه' : ($pi['period']==='quarterly'?'سه‌ماهه':'سالانه') }}</div>
      @endif
    </div>
    <div class="col tr">
      <div class="mb8"><strong>{{ config('app.name', 'ChargePal') }}</strong></div>
      @if(!empty($business))
        <div class="muted">کسب‌وکار: {{ $business['name'] ?? '' }}</div>
      @endif
    </div>
  </div>

  <div class="card">
    <table>
      <thead>
      <tr>
        <th>شرح</th>
        <th>تعداد</th>
        <th>قیمت واحد ({{ $pi['currency'] ?? 'IRR' }})</th>
        <th>مبلغ ({{ $pi['currency'] ?? 'IRR' }})</th>
      </tr>
      </thead>
      <tbody>
      @foreach($items as $it)
        <tr>
          <td class="tr">{{ $it['title'] }}</td>
          <td class="tc">{{ nf($it['qty']) }}</td>
          <td class="tc">{{ nf($it['unit_price']) }}</td>
          <td class="tc">{{ nf($it['line_total']) }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot>
      <tr>
        <td colspan="3" class="tr">جمع جزء</td>
        <td class="tc">{{ nf($pi['subtotal']) }}</td>
      </tr>
      <tr>
        <td colspan="3" class="tr">تخفیف</td>
        <td class="tc">{{ nf($pi['discount']) }}</td>
      </tr>
      <tr>
        <td colspan="3" class="tr">مالیات ({{ (float)($pi['tax_percent']) }}٪)</td>
        <td class="tc">{{ nf($pi['tax']) }}</td>
      </tr>
      <tr>
        <td colspan="3" class="tr total">قابل پرداخت</td>
        <td class="tc total">{{ nf($pi['total']) }}</td>
      </tr>
      </tfoot>
    </table>
  </div>

  @if(!empty($pi['meta']))
    <div class="card">
      <div class="h1">توضیحات</div>
      <div class="muted">{{ is_string($pi['meta']) ? $pi['meta'] : json_encode($pi['meta'], JSON_UNESCAPED_UNICODE) }}</div>
    </div>
  @endif

  <div class="footer">
    این سند صرفاً پیش‌فاکتور است. پرداخت نهایی طبق شرایط سرویس محاسبه می‌شود.
  </div>
</div>
</body>
</html>
