<!DOCTYPE html>
<html dir="rtl">

@php
    function convert($str){
        $persian_pdf = new App\Helpers\PersianPdf();
        return $persian_pdf->convert($str);
    }

    $building = auth()->buildingManager()->building;

    function humanReadablePaymentMethod($payment_method)
    {
        switch ($payment_method) {
            case 'Shetabit\\Multipay\\Drivers\\Payir\\Payir':
                return 'پرداخت آنلاین';
                break;
            case 'Shetabit\\Multipay\\Drivers\\Sepehr\\Sepehr':
                return 'پرداخت آنلاین';
                break;
            case 'wallet':
                return 'پرداخت آنلاین';
                break;
            case 'cash':
                return 'پرداخت نقدی';
                break;
            default:
                return 'پرداخت آنلاین';
                break;
        }
    }
@endphp

<head>
    <title>ChargePal.ir</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @font-face {
            font-family: 'IranSans';
            font-style: normal;
            font-weight: normal;
            src: url({{ asset('fonts/IRANSans.ttf') }}) format('truetype');
        }

        html {
            margin: 20px;
        }

        * {
            font-family: "IranSans" !important;
        }

        body {
            font-size: 16px;
            padding: 10px;
            margin: 10px;

            color: #000;
            direction: rtl;
        }

        .export-table * {
            text-align: center;
            font-family: "IranSans" !important;
            font-weight: normal;
            direction: rtl;
            vertical-align: middle;
        }

        .export-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .export-table,
        .export-table th,
        .export-table td {
            border: 2px solid black;
            text-align: center;
            padding: 0px;
        }

        .export-table th {
            background-color: #c0bebe;
        }

        .export-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body style="margin-top: 0px; padding-bottom: 50px;">
    @if ($unit)
        <div class="">
            <div style="text-align: center; width: 100%; font-size: 20px;">
                {{ convert($unit->unit_number) }} {{ convert('صورت حساب واحد') }}
            </div>
        </div>
    @else
        @if (isset($request['type']) && $request['type'] == 'debt')
            <div class="">
                <div style="text-align: center; width: 100%; font-size: 20px;">
                    {{ convert('گزارش بدهی ها') }}
                </div>
            </div>
        @endif
        @if (isset($request['type']) && $request['type'] == 'deposit')
            <div class="">
                <div style="text-align: center; width: 100%; font-size: 20px;">
                    {{ convert('گزارش دریافتی ها') }}
                </div>
            </div>
        @endif
        @if (isset($request['type']) && $request['type'] == 'cost')
            <div class="">
                <div style="text-align: center; width: 100%; font-size: 20px;">
                    {{ convert('گزارش هزینه ها') }}
                </div>
            </div>
        @endif
        @if (isset($request['type']) && $request['type'] == 'income')
            <div class="">
                <div style="text-align: center; width: 100%; font-size: 20px;">
                    {{ convert('گزارش درآمد ها') }}
                </div>
            </div>
        @endif
    @endif

    <div>
        <img src="{{ asset($building->image ?? 'images/building.png') }}"
            style="height: 120px; width: 120px; object-fit:cover">
        <br>
        <span style="font-size: 20px;">
            {{ convert($building->name) }}
        </span>

        @if ($start_date && $end_date)
            <div style="float: left">
                ‌ {{ $end_date }} : {{ convert('تا') }}
            </div>
            <div style="float: left">
                ‌ {{ $start_date }} : {{ convert('از') }}
            </div>
        @else
            <div style="float: left">
                ‌ {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }} : {{ convert('تاریخ گزارش') }}
            </div>
        @endif
    </div>

    @if ($unit)
        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ convert('مانده') }}</th>
                    <th>{{ convert('بستانکار') }}</th>
                    <th>{{ convert('بدهکار') }}</th>
                    <th>{{ convert('تاريخ') }}</th>
                    <th>{{ convert('توضيحات') }}</th>
                    <th>{{ convert('رديف') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ number_format($invoice->balance * 10) }}</td>
                        <td>{{ $invoice->amount >= 0 ? number_format($invoice->amount * 10) : '' }}</td>
                        <td>{{ $invoice->amount < 0 ? number_format(-1 * $invoice->amount * 10) : '' }}</td>
                        <td>{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}</td>
                        <td>{{ convert($invoice->description) }}</td>
                        <td>{{ $loop->iteration }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($request['type']) && $request['type'] == 'debt')
        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ convert('تاريخ') }}</th>
                    <th>{{ convert('توضیحات') }}</th>
                    <th>{{ convert('مبلغ') }}</th>
                    <th>{{ convert('شماره واحد') }}</th>
                    <th>{{ convert('رديف') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}</td>
                        <td>{{ convert($invoice->description) }}</td>
                        <td>{{ $invoice->amount < 0 ? number_format(-1 * $invoice->amount * 10) : number_format($invoice->amount * 10) }}
                        </td>
                        <td>{{ convert($invoice->service()->withTrashed()->first()?->unit_number) }}</td>
                        <td>{{ $loop->iteration }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($request['type']) && $request['type'] == 'deposit')
        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ convert('وضعیت') }}</th>
                    <th>{{ convert('تاريخ') }}</th>
                    <th>{{ convert('توضیحات') }}</th>
                    <th>{{ convert('نحوه پرداخت') }}</th>
                    <th>{{ convert('مبلغ') }}</th>
                    <th>{{ convert('شماره واحد') }}</th>
                    <th>{{ convert('رديف') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ convert($invoice->is_verified ? 'تایید شده' : 'تایید نشده') }}</td>
                        <td>{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}</td>
                        <td>{{ convert($invoice->description) }}</td>
                        <td>{{ convert(humanReadablePaymentMethod($invoice->payment_method)) }}</td>
                        <td>{{ $invoice->amount < 0 ? number_format(-1 * $invoice->amount * 10) : number_format($invoice->amount * 10) }}
                        </td>
                        <td>{{ convert($invoice->service()->withTrashed()->first()?->unit_number) }}</td>
                        <td>{{ $loop->iteration }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($request['type']) && $request['type'] == 'cost')
        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ convert('تاريخ') }}</th>
                    <th>{{ convert('توضیحات') }}</th>
                    <th>{{ convert('مبلغ') }}</th>
                    <th>{{ convert('رديف') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}</td>
                        <td>{{ convert($invoice->description) }}</td>
                        <td>{{ $invoice->amount < 0 ? number_format(-1 * $invoice->amount * 10) : number_format($invoice->amount * 10) }}
                        </td>
                        <td>{{ $loop->iteration }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($request['type']) && $request['type'] == 'income')
        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ convert('تاريخ') }}</th>
                    <th>{{ convert('توضیحات') }}</th>
                    <th>{{ convert('مبلغ') }}</th>
                    <th>{{ convert('رديف') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}</td>
                        <td>{{ convert($invoice->description) }}
                            @if ($invoice->unit)
                                - {{ convert($invoice->unit->unit_number) }} {{ convert('واحد') }}
                            @endif
                        </td>
                        <td>{{ $invoice->amount < 0 ? number_format($invoice->amount * 10) : number_format($invoice->amount * 10) }}
                        </td>
                        <td>{{ $loop->iteration }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="position:fixed; bottom: 10; left: 10">
        <a href="https://chargepal.ir"> ChargePal.ir </a>
    </div>
</body>

</html>
