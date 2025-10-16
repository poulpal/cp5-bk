<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>پیش‌فاکتور {{ $pf->proforma_number }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin: 12px 0; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f3f3f3; }
    .totals td { font-weight: bold; }
    .muted { color: #666; }
  </style>
</head>
<body>
  <h2>پیش‌فاکتور {{ $pf->proforma_number }}</h2>
  <p class="muted">دوره: {{ ['monthly'=>'ماهانه','quarterly'=>'۳ماهه','yearly'=>'سالانه'][$pf->period] ?? $pf->period }}</p>

  @if(method_exists($pf, 'items') && $pf->relationLoaded('items'))
  <table>
    <thead>
      <tr>
        <th>شرح</th>
        <th>تعداد</th>
        <th>مبلغ واحد</th>
        <th>مبلغ کل</th>
      </tr>
    </thead>
    <tbody>
      @foreach($pf->items as $it)
      <tr>
        <td>{{ $it->title }}</td>
        <td style="text-align:center">{{ $it->qty }}</td>
        <td style="text-align:left">{{ number_format($it->unit_price) }}</td>
        <td style="text-align:left">{{ number_format($it->qty * $it->unit_price) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <table>
    <tbody>
      <tr class="totals"><td>جمع جزء</td><td style="text-align:left">{{ number_format($pf->subtotal) }}</td></tr>
      <tr class="totals"><td>تخفیف</td><td style="text-align:left">{{ number_format($pf->discount) }}</td></tr>
      <tr class="totals"><td>مالیات</td><td style="text-align:left">{{ number_format($pf->tax) }}</td></tr>
      <tr class="totals"><td>مبلغ نهایی</td><td style="text-align:left">{{ number_format($pf->total) }} {{ $pf->currency }}</td></tr>
    </tbody>
  </table>

  <p class="muted">تاریخ صدور: {{ $pf->issued_at }}</p>
  <p class="muted">اعتبار تا: {{ $pf->expires_at }}</p>
</body>
</html>
