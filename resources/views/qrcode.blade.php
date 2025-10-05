<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>دریافت پی دی اف</title>
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($units as $unit)
        <h1>{{ $unit->unit_number }}</h1>
        <img src="data:image/png;base64, {!! base64_encode(
            QrCode::format('png')->size(500)->generate('https://poulpal.com/b' . $unit->token),
        ) !!} ">
        <div class="page-break"></div>
    @endforeach

</body>

</html>
