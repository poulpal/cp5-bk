<!DOCTYPE html>
<html dir="rtl">

@php
    function convert($str){
        $persian_pdf = new App\Helpers\PersianPdf();
        return $persian_pdf->convert($str);
    }
    
    $building = auth()->buildingManager()->building;
    
    function humanReadableStatus($status)
    {
        switch ($status) {
            case 'pending':
                return 'در انتظار تایید';
            case 'accepted':
                return 'تایید شده';
            case 'rejected':
                return 'رد شده';
            default:
                return 'نامشخص';
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

    <div class="">
        <div style="text-align: center; width: 100%; font-size: 20px;">
            {{ convert('درخواست های واریز') }}
        </div>
    </div>

    <div>
        <img src="{{ asset($building->image ?? 'images/building.png') }}"
            style="height: 120px; width: 120px; object-fit:cover">
        <br>
        <span style="font-size: 20px;">
            {{ convert($building->name) }}
        </span>

        <div style="float: left">
            ‌ {{ Morilog\Jalali\Jalalian::now()->format('Y/m/d') }} : {{ convert('تاریخ گزارش') }}
        </div>
    </div>

    <table class="export-table">
        <thead>
            <tr>
                <th>{{ convert('توضیحات') }}</th>
                <th>{{ convert('واریز به حساب') }}</th>
                <th>{{ convert('وضعیت') }}</th>
                <th>{{ convert('مبلغ') }}</th>
                <th>{{ convert('تاريخ') }}</th>
                <th>{{ convert('رديف') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($depositRequests as $depositRequest)
                <tr>
                    <td>{{ convert($depositRequest->description) }}</td>
                    <td>{{ $depositRequest->sheba }}</td>
                    <td>{{ convert(humanReadableStatus($depositRequest->status)) }}</td>
                    <td>{{ number_format($depositRequest->amount * 10) }}</td>
                    <td>{{ Morilog\Jalali\Jalalian::fromCarbon($depositRequest->created_at)->format('Y/m/d') }}</td>
                    <td>{{ $loop->iteration }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="position:fixed; bottom: 10; left: 10">
        <a href="https://chargepal.ir"> ChargePal.ir </a>
    </div>
</body>

</html>
