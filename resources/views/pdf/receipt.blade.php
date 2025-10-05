<!DOCTYPE html>
<html dir="rtl">

@php
    function convert($str)
    {
        // return $str;
        $persian_pdf = new App\Helpers\PersianPdf();
        return $persian_pdf->convert($str);
    }

    $unit = $invoice->unit;
    $building = $unit->building;

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
            margin: 5px;
        }

        * {
            font-family: "IranSans" !important;
            font-size: 11px;
        }

        body {
            font-size: 12px;
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
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td colspan="1">
                <div style="border: 1px solid black; padding: 5px; margin: 10px;">
                    <div style="text-align: center; width: 100%; font-size: 12px;">
                        {{ convert('رسید دریافت') }}
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td>
                                ‌ {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }} :
                                {{ convert('تاریخ چاپ رسید') }}
                            </td>
                            <td style="text-align: right">
                                <table style="direction: rtl;">
                                    <tr style="text-align: right">
                                        <img src="{{ asset($building->image ?? 'images/building.png') }}"
                                            style="height: 40px; width: 40px; object-fit:cover">
                                    </tr>
                                    <tr style="text-align: right">
                                        <span style="font-size: 12px;">
                                            {{ convert($building->name) }}
                                        </span>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <hr>
                    @php
                        $resident_when_paid = $unit->residentsByDate($invoice->created_at);
                        if ($invoice->resident_type == 'owner') {
                            $resident_when_paid = $resident_when_paid->where('type', 'owner');
                        }
                        $resident_when_paid = $resident_when_paid->first();
                    @endphp
                    <div style="text-align: right">
                        <span style="text-align: right">
                            {{ convert($resident_when_paid?->full_name ?? ' ') }}
                            {{ convert('از آقای / خانم') }}
                            {{ convert('ریال') }}
                            {{ number_format($invoice->amount * 10) }}
                            {{ convert('مبلغ :') }}
                        </span>
                        <br>
                        <span style="text-align: right">
                            {{ convert('دریافت گردید.') }}
                            {{ convert(humanReadablePaymentMethod($invoice->payment_method)) }}
                            {{ convert('به صورت') }} {{ convert($unit->unit_number) }}
                            {{ convert('مربوط به واحد ') }}
                        </span>
                        <br>
                        @if ($unit->debt($invoice->resident_type) > 0)
                            <span style="text-align: right">
                                {{ convert('ریال است.') }}
                                {{ number_format($unit->debt($invoice->resident_type) * 10) }}
                                {{ convert('مبلغ') }}
                                {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}
                                {{ convert('مانده بدهی تا تاریخ') }}
                            </span>
                        @else
                        @endif
                        <br>
                        <span style="text-align: right">
                            {{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}
                            {{ convert('تاریخ دریافت وجه :') }}
                        </span>
                        <br>
                        <span style="text-align: right">
                            {{ convert('شرح پرداخت :') }}
                        </span>
                        <br>
                        @if ($invoice->paid_data && count($invoice->paid_data) > 0)
                            @foreach ($invoice->paid_data as $paid_data)
                                @php
                                    $debt = App\Models\Invoice::find($paid_data->debt_id);
                                @endphp
                                <span style="text-align: right">
                                    {{ convert($debt->description) }}
                                    {{ convert('-') }}
                                    {{ convert('ریال') }}
                                    {{ number_format($paid_data->amount * 10) }}
                                </span>
                                <br>
                            @endforeach
                        @endif
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div style="height: 50px; position: relative; bottom: 0; left: 0">
                        <span style="text-align: right">
                            {{ convert('مهر و امضا') }}
                        </span>
                        <div style="position:relative; bottom: 0; left: 0">
                            <a href="https://chargepal.ir"> ChargePal.ir </a>
                        </div>
                    </div>
            </td>
            <td colspan="1">
                <div style="border: 1px solid black; padding: 5px; margin: 10px;">
                    <div style="text-align: center; width: 100%; font-size: 12px;">
                        {{ convert('رسید دریافت') }}
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td>
                                ‌ {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }} :
                                {{ convert('تاریخ چاپ رسید') }}
                            </td>
                            <td style="text-align: right">
                                <table style="direction: rtl;">
                                    <tr style="text-align: right">
                                        <img src="{{ asset($building->image ?? 'images/building.png') }}"
                                            style="height: 40px; width: 40px; object-fit:cover">
                                    </tr>
                                    <tr style="text-align: right">
                                        <span style="font-size: 12px;">
                                            {{ convert($building->name) }}
                                        </span>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <hr>
                    @php
                        $resident_when_paid = $unit->residentsByDate($invoice->created_at);
                        if ($invoice->resident_type == 'owner') {
                            $resident_when_paid = $resident_when_paid->where('type', 'owner');
                        }
                        $resident_when_paid = $resident_when_paid->first();
                    @endphp
                    <div style="text-align: right">
                        <span style="text-align: right">
                            {{ convert($resident_when_paid?->full_name ?? ' ') }}
                            {{ convert('از آقای / خانم') }}
                            {{ convert('ریال') }}
                            {{ number_format($invoice->amount * 10) }}
                            {{ convert('مبلغ :') }}
                        </span>
                        <br>
                        <span style="text-align: right">
                            {{ convert('دریافت گردید.') }}
                            {{ convert(humanReadablePaymentMethod($invoice->payment_method)) }}
                            {{ convert('به صورت') }} {{ convert($unit->unit_number) }}
                            {{ convert('مربوط به واحد ') }}
                        </span>
                        <br>
                        @if ($unit->debt($invoice->resident_type) > 0)
                            <span style="text-align: right">
                                {{ convert('ریال است.') }}
                                {{ number_format($unit->debt($invoice->resident_type) * 10) }}
                                {{ convert('مبلغ') }}
                                {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}
                                {{ convert('مانده بدهی تا تاریخ') }}
                            </span>
                        @else
                        @endif
                        <br>
                        <span style="text-align: right">
                            {{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}
                            {{ convert('تاریخ دریافت وجه :') }}
                        </span>
                        <br>
                        <span style="text-align: right">
                            {{ convert('شرح پرداخت :') }}
                        </span>
                        <br>
                        @if ($invoice->paid_data && count($invoice->paid_data) > 0)
                            @foreach ($invoice->paid_data as $paid_data)
                                @php
                                    $debt = App\Models\Invoice::find($paid_data->debt_id);
                                @endphp
                                <span style="text-align: right">
                                    {{ convert($debt->description) }}
                                    {{ convert('-') }}
                                    {{ convert('ریال') }}
                                    {{ number_format($paid_data->amount * 10) }}
                                </span>
                                <br>
                            @endforeach
                        @endif
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div style="height: 50px; position: relative; bottom: 0; left: 0">
                        <span style="text-align: right">
                            {{ convert('مهر و امضا') }}
                        </span>
                        <div style="position:relative; bottom: 0; left: 0">
                            <a href="https://chargepal.ir"> ChargePal.ir </a>
                        </div>
                    </div>
            </td>
        </tr>
    </table>
</body>

</html>
