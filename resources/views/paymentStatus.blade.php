<html lang="fa">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="default-images/ico/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="default-images/ico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="default-images/ico/favicon-16x16.png">
    <link rel="mask-icon" href="default-images/ico/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="manifest" href="manifest.json">
    <link rel="theme-color" content="#1e88e5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>نتیجه تراکنش- POULPAL</title>
    <link rel="icon" type="image/png" href="img/favicon2.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="css/a888.min.css" rel="stylesheet">
</head>

<body cz-shortcut-listen="true" style="background-color:#0c0c0c" data-new-gr-c-s-check-loaded="8.904.0"
    data-gr-ext-installed="">
    <nav class="navbar navbar-light navbar-expand-lg bg-dark bg-faded osahan-menu">
        <div class="container-fluid" style="background-color:#000">
            <a class="navbar-brand" href="../"><img src="../default-images/logo.png"> </a>
            <button class="navbar-toggler navbar-toggler-white" type="button" data-toggle="collapse"
                data-target="#navbarText" aria-controls="navbarText" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <style>
        .farsi {
            font-family: vazirbold;
        }

        .farsifd {
            font-family: vazirfd;
        }

        .wraps {
            white-space: pre-wrap;
            white-space: -moz-pre-wrap;
            white-space: -pre-wrap;
            white-space: -o-pre-wrap;
            word-wrap: break-word;
            width: 100%;
        }

        .oneline {
            display: block;
            width: auto;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            text-align: center
        }

        .as1 {
            height: width
        }

        .pbox {
            position: relative;
            width: 100%;
            margin: 5px
        }

        .pbox:before {
            content: "";
            display: block;
            padding-top: 100%
        }

        .content1 {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: #333;
            color: #fff;
            line-height: 100%;
            height: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .rtl {
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override
        }

        .ltr {
            direction: ltr;
            text-align: left;
            unicode-bidi: bidi-override
        }

        .not-active {
            pointer-events: none;
            cursor: default;
            opacity: .4
        }

        #myBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 30px;
            z-index: 99;
            font-size: 18px;
            border: none;
            outline: 0;
            background-color: red;
            color: #fff;
            cursor: pointer;
            padding: 15px;
            border-radius: 4px
        }

        #myBtn:hover {
            background-color: #555;
        }

        .regular-price1 {
            color: #fff !important;
            font-size: 11px;
            font-weight: 500;
            line-height: 15px;
            text-decoration: line-through;
        }

        .twol {
            line-height: 1.5em;
            height: 3em;
            overflow: hidden;
        }

        @font-face {
            font-family: vazirbold;
            src: url({{ asset('fnt/Vazir-Medium.ttf') }}), url({{ asset('fnt/Vazir-Medium.eot') }}), url({{ asset('fnt/Vazir-Medium.woff') }})
        }

        @font-face {
            font-family: vazirfd;
            src: url({{ asset('fnt/Vazir-Bold-FD.ttf') }}), url({{ asset('fnt/Vazir-Bold-FD.eot') }}), url({{ asset('fnt/Vazir-Bold-FD.woff') }})
        }

        .farsi {
            font-family: vazirbold;
        }

        .farsifd {
            font-family: vazirfd;
        }
    </style>
    <div class="container">
        <div class="row rtl-direction" style="justify-content: center;">
            <div class="col-12 col-lg-6 fit-width p-0 mt-4 d-flex flex-column text-justify"
                style="box-shadow: 0 6px 15px rgba(0, 0, 0, 0.26)">
                <div class="pl-3 farsifd rast pr-3 pb-1 text-right"
                    style="color:#fff; background:#ada9ac; border-radius:5px 5px 0 0; margin-top:-3px; padding:9px; ">
                    نتیجه
                </div>
                @if (session('error'))
                    <div class="farsifd rast" style="border:1px solid #ada9ac; border-radius:0 0 5px 5px; padding:10px">
                        <div class="row m-0 col-12 bg-white">
                            <div class="mt-2 mb-2 col-12 text-center" style="color:#2b2829">متاسفانه از سمت شما و یا
                                بانک
                                شما خطایی رخ داده است؛<br>
                                چنانچه از کارت بانکی شما مبلغی کسر شده باشد حداکثر ظرف 72 ساعت به حساب شما برگشت داده
                                خواهد
                                شد. <br>
                                در غیر اینصورت با بانک صادر کننده کارت خود تماس بگیرید
                                <br>
                                <meta http-equiv="refresh" content="103;url=../">
                                شرح خطا : {{ session('error') }}
                                <div class="controls farsifd mt-5">
                                </div>
                            </div>
                            <p></p>
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="farsifd rast" style="border:1px solid #ada9ac; border-radius:0 0 5px 5px; padding:10px">
                        <div class="row m-0 col-12 bg-white">
                            <div class="mt-2 mb-2 col-12 text-center" style="color:#2b2829">
                                {{ session('success') }}
                                <br>
                                مبلغ پرداختی : {{ session('amount') }}
                                <br>
                                شماره پیگیری : {{ session('tracenumber') }}
                                <br>
                                <meta http-equiv="refresh" content="103;url=../">
                                <div class="controls farsifd mt-5">
                                </div>
                            </div>
                            <p></p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>



</body>

</html>
