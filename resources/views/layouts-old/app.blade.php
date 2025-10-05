<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSRF Token -->
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('default-images/ico/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('default-images/ico/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('default-images/ico/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('default-images/ico/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('default-images/ico/safari-pinned-tab.svg') }}" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="poulpal.com| poulpal.com">
    <meta name="description"
        content="تسهیل فرآیند دریافت درگاه پرداخت، ارائه خدمات در حوزه فین‌تک به کسب و کارها مانند لینک پرداخت، کیف پول، فاکتور الکترونیک،منوی دیجیتال و کارتخوان مجازی و ...">
    <meta name="theme-color" content="#ffffff">
    <meta property="business:contact_data:website" content="https://poulpal.com">
    <meta property="og:title" content=" درگاه پرداخت اینترنتی با تسویه آنی | (IPG) |PoulPal - پول‌پل">
    <meta property="og:description"
        content="تسهیل فرآیند دریافت درگاه پرداخت، ارائه خدمات در حوزه فین‌تک به کسب و کارها مانند لینک پرداخت، کیف پول، فاکتور الکترونیک،منوی دیجیتال و کارتخوان مجازی و ...">
    <meta property="og:url" content="">
    <meta property="og:image" content="{{ asset('/default-images/logo.png') }}">
    <meta name="dcterms.subject" content="راهکارهای تجارت الکترونیک">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="https://poulpal.com">
    <meta property="og:site_name" content="درگاه پرداخت اینترنتی poulpal.com">
    <meta property="twitter:card" content="service">
    <meta property="twitter:site" content="@poulpalcom">
    <meta property="twitter:app:id:googleplay" content="poulpalcom">
    <meta property="twitter:creator" content="poulpalcom">
    <meta property="twitter:title" content=" درگاه پرداخت اینترنتی با تسویه آنی | (IPG) |PoulPal - پول‌پل">
    <meta property="twitter:label1" content="PoulPal" (پول‌پل)'="">
    <meta property="twitter:description"
        content="تسهیل فرآیند دریافت درگاه پرداخت، ارائه خدمات در حوزه فین‌تک به کسب و کارها مانند لینک پرداخت، کیف پول، فاکتور الکترونیک،منوی دیجیتال و کارتخوان مجازی و ...">
    <meta property="og:type" content="product">
    <meta property="twitter:site" content="poulpal.com">
    <meta property="twitter:creator" content="poulpal.com">
    <meta property="article:published_time" content="2022-03-12 21:40:182022-06-30">
    <meta property="article:modified_time" content="2022-12-31 13:55:19">
    <meta property="og:updated_time" content="2022-12-31">
    <link href="{{ asset('css/materialdesignicons.css') }}" media="all" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/poulpal.min.css') }}" rel="stylesheet">
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
    <script src="{{ asset('js/jquery.min.js') }}"></script>
</head>

@yield('head')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        <nav class="navbar navbar-light navbar-expand-lg bg-dark bg-faded osahan-menu"
            style="border-bottom:#FDBD03 6px ridge">
            <div class="container-fluid"> <a class="navbar-brand" href="{{ route('home') }}"><img
                        src={{ asset('default-images/logo.png') }}>
                </a><button class="navbar-toggler navbar-toggler-white" type="button" data-toggle="collapse"
                    data-target="#navbarText" aria-controls="navbarText" aria-expanded="false"
                    aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="navbar-collapse" id="navbarNavDropdown">
                    <div class="navbar-nav mr-auto mt-2 mt-lg-0 margin-auto top-categories-search-main">
                        <div class="top-categories-search"></div>
                    </div>
                    {{-- @if (!auth()->role())
                        <div class="my-2 my-lg-0">
                            <ul class="list-inline main-nav-right">
                                <li class="list-inline-item"></li>
                                <li class="list-inline-item cart-btn"><a href="{{ route('login') }}"
                                        class="btn btn-link border-none"><i class="mdi mdi-login"></i>ورود</a></li>
                            </ul>
                        </div>
                    @endif --}}
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <nav class="navbar navbar-expand-lg bg-faded noprint">
                <div class="container-fluid">
                    <div class="collapse navbar-collapse" id="navbarText">
                        <ul class="navbar-nav mr-auto mt-2 text-right mt-lg-0 margin-auto"
                            style="direction:rtl;font-family:tahoma">
                            {{-- <li class="nav-item"> <a href="{{ route('home') }}" class="nav-link">صفحه اول</a> </li>
                            @if (!auth()->role())
                                <li class="nav-item"> <a class="nav-link" href="{{ route('login') }}"
                                        style="color:green"><strong>ورود</strong></a> </li>
                            @else
                                <li class="nav-item"> <a class="nav-link"
                                        href="{{ route(auth()->role() . '.dashboard') }}"
                                        style="color:green"><strong>پروفایل کاربری</strong></a> </li>
                                <li class="nav-item"> <a class="nav-link" href="{{ route('logout') }}"
                                        style="color:red"><strong>خروج</strong></a> </li>
                            @endif --}}
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="mt-3"></div>
            <h1 class="h3 text-center rast farsi" style="direction:rtl;color:#043477"><strong>راهکارهای تجارت
                    الکترونیک</strong></h1>
            <h2 class="h4 text-center rast farsi" style="direction:rtl;color:#000">اتوماسیون مالی PoulPal</h2>
            <div class="mb-2"></div>
            {{-- @if (auth()->role())
                <h6 class="text-center rast farsifd pb--20"><a href=""
                        style="direction:rtl;color:#043477;font-size:0.9em">{{ auth(auth()->role())->user()->mobile }}</a>
                </h6>
            @endif --}}
            @auth('operator')
                <div class="d-flex w-100 justify-content-center">
                    <a href="{{ route('operator.dashboard') }}" class="btn btn-success btn-sm">داشبورد</a>
                </div>
            @endauth
            @if (session()->has('success'))
                <div class="alert alert-success mx-5 rtl" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger mx-5 rtl" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if (session()->has('sms') && config('app.env') !== 'production')
                <div class="alert alert-info mx-5" role="alert">
                    {{ session('sms') }}
                </div>
            @endif
            @yield('content')
        </main>

        <section class="section-padding rast text-center farsi bg-white noprint">
            <div class="container">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"> <a href="https://poulpal.com/textgateway"
                                    title="درخواست وجه پیامکی" alt="درخواست وجه پیامکی"><img class="img-fluid"
                                        src={{ asset('images/درگاه-پرداخت-پیامکی.webp') }} title="درخواست وجه پیامکی"
                                        alt="درخواست وجه پیامکی"></a></div>
                            <div class="product-body rast text-center">
                                <h5> <a href="https://poulpal.com/textgateway" title="درخواست وجه پیامکی"
                                        alt="درخواست وجه پیامکی"><strong style="color:#043477">درگاه پرداخت
                                            پیامکی</strong></a></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"><a href="https://poulpal.com/qrgateway"
                                    alt="درگاه پرداخت کیوآر" text="درگاه پرداخت کیوآر"><img class="img-fluid"
                                        src={{ asset('images/درگاه-پرداخت-کیوآر.webp') }} alt="درگاه پرداخت کیوآر"
                                        text="درگاه پرداخت کیوآر"></a></div>
                            <div class="product-body rast text-center">
                                <h5><a href="https://poulpal.com/qrgateway" alt="درگاه پرداخت کیوآر"
                                        text="درگاه پرداخت کیوآر"></a>
                                    <h5><a href="https://poulpal.com/qrgateway" alt="درگاه پرداخت کیوآر"
                                            text="درگاه پرداخت کیوآر"><strong style="color:#043477">درگاه پرداخت
                                                کیوآر(QR)</strong></a></h5>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"><a href="https://poulpal.com/gateway"
                                    alt="درگاه پرداخت اینترنتی" text="درگاه پرداخت اینترنتی"><img class="img-fluid"
                                        src={{ asset('images/درگاه-پرداخت-هوشمند.webp') }} alt="درگاه پرداخت اینترنتی"
                                        text="درگاه پرداخت اینترنتی"></a></div>
                            <div class="product-body rast text-center">
                                <h5><a href="https://poulpal.com/gateway" alt="درگاه پرداخت اینترنتی"
                                        text="درگاه پرداخت اینترنتی">
                                        <h5><strong style="color:#043477">درگاه پرداخت هوشمند</strong></h5>
                                    </a></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"><a href="https://poulpal.com/digitalmenu" alt="منو دیجیتال"
                                    text="منو دیجیتال"><img class="img-fluid"
                                        src={{ asset('images/sms-gateway.webp') }} alt="منو دیجیتال"
                                        text="منو دیجیتال"></a></div>
                            <div class="product-body rast text-center"><a href="digitalmenu" alt="منو دیجیتال"
                                    text="منو دیجیتال">
                                    <h5><strong>منو دیجیتال</strong></h5>
                                </a></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"><a href="https://poulpal.com/textgateway"
                                    title="درخواست وجه پیامکی" alt="درخواست وجه پیامکی"> </a><a href="payday"
                                    alt="سیستم اقساط، شارژ و اجاره" text="سیستم اقساط، شارژ و اجاره"><img
                                        class="img-fluid" src={{ asset('images/شارژ-اجاره.webp') }}
                                        alt="سیستم اقساط، شارژ و اجاره" text="سیستم اقساط، شارژ و اجاره"></a></div>
                            <div class="product-body rast text-center" style="padding-right:20px">
                                <h5> <a href="https://poulpal.com/payday" alt="سیستم اقساط، شارژ و اجاره"
                                        text="سیستم اقساط، شارژ و اجاره"><strong style="color:#043477">سیستم اقساط،
                                            شارژ و اجاره</strong></a></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product">
                            <div class="product-header"> <a href="https://poulpal.com/einvoice"
                                    title="صدور فاکتور الکترونیکی" alt="صدور فاکتور الکترونیکی"><img
                                        src={{ asset('images/صدور-فاکتور-الکترونیکی.webp') }}
                                        title="صدور فاکتور الکترونیکی" alt="صدور فاکتور الکترونیکی"></a></div>
                            <div class="product-body rast text-center" style="padding-right:20px">
                                <h5><a href="https://poulpal.com/einvoice" title="صدور فاکتور الکترونیکی"
                                        alt="صدور فاکتور الکترونیکی"><strong style="color:#043477">صدور فاکتور
                                            الکترونیکی</strong></a></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <hr style="border:#FDBD03 2px ridge noprint">
        <section class="section-padding farsi noprint" style="color:#fff">
            <div class="container section-padding">
                <div class="row text-right" style="direction:rtl; text-align:left;">
                    <div class="col-lg-4 col-sm-6" style="color:#000">
                        <div class="feature-box"> <i class="mdi mdi-truck-fast float-right"></i>
                            <h6 style="padding-right:80px">فرصت‌های استثنایی و بن‌های تخفیف</h6>
                            <p>امتیازهای ویژه باشگاه مشتریان</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="feature-box"> <i class="mdi mdi-basket float-right"></i>
                            <h6 style="padding-right:80px">واریز مستقیم درآمدها</h6>
                            <p>تضمین بازگشت وجه</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6">
                        <div class="feature-box"> <i class="mdi mdi-tag-heart float-right"></i>
                            <h6 style="padding-right:80px">حسابداری و گزارش دقیق</h6>
                            <p>در فرمت‌های مختلف</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-padding noprint" style="border-top:#FDBD03 1px groove;background-color:#043477">
            <div class="container">
                <div class="row" style="direction:rtl; text-align:right;">
                    <div class="col-lg-3 col-md-3">
                        {{-- <div class="farsi text-white text-center"><a referrerpolicy="origin" target="_blank"
                                href="https://trustseal.enamad.ir/?id=308589&amp;Code=c1T6UNFIZOWjZI4rLYF9"><img
                                    referrerpolicy="origin"
                                    src="https://Trustseal.eNamad.ir/logo.aspx?id=308589&amp;Code=c1T6UNFIZOWjZI4rLYF9"
                                    alt="" style="cursor:pointer" id="c1T6UNFIZOWjZI4rLYF9"></a>
                            <ul>
                                <ul> </ul>
                            </ul>
                        </div> --}}
                    </div>
                    <div class="col-lg-3 col-md-3"> <a href="terms.php" class="text-white h6 text-left farsi">قوانین
                            و مقررات</a> </div>
                    <div class="col-lg-2 col-md-2 farsifd text-white">
                        <h6 class="mb-4 text-white">تماس با ما:</h6>
                        <ul>
                            <li>
                                <p class="mb-0 farsifd text-white">تهران -خیابان گلزار جنوبی -خیابان لادن شرقی - خیابان
                                    ۱۲ متری ولیعصر - بن بست قائم - پلاک ۷</p>
                            </li>
                            <li>
                                <p class="mb-0 farsifd text-white">تلفن تماس: 22924146-021</p>
                            </li>
                            <li>
                                <p class="mb-0 farsifd text-white">26412042-021</p>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-3 text-right"> &lt;<h6 class="mb-3 mt-4 text-left text-white">GET IN
                            TOUCH</h6>
                        <div class="footer-social text-left"> <a class="btn-facebook"
                                href="https://facebook.com/arcencielco" rel="nofollow" target="_blank"><i
                                    class="mdi mdi-facebook"></i></a> <a class="btn-twitter"
                                href="https://twitter.com/arcenciel_corp" rel="nofollow" target="_blank"><i
                                    class="mdi mdi-twitter"></i></a> <a class="btn-instagram"
                                href="https://instagram.com/arcenciel_corp" rel="nofollow" target="_blank"><i
                                    class="mdi mdi-instagram"></i></a> <a class="btn-whatsapp"
                                href="https://wa.me/+989999541888" rel="nofollow" target="_blank"><i
                                    class="mdi mdi-whatsapp"></i></a> <a class="btn-messenger"
                                href="https://m.me/arcenciel.corp" rel="nofollow" target="_blank"><i
                                    class="mdi mdi-facebook-messenger"></i></a> </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="pt-4 pb-4 footer-bottom text-left noprint" style="bottom:10px;width: 100%;background-color: #000">
            <div class="container">
                <div class="row no-gutters">
                    <div class="col-lg-12 col-sm-12">
                        <p class="mt-1 mb-0">© Copyright 2022 <strong class="text-white">Poulpal.com</strong>. All
                            Rights Reserved<br> <small class="mt-0 mb-0">Made with <i
                                    class="mdi mdi-heart text-danger"></i> by <a href="https://Poulpal.com/"
                                    target="_blank" class="text-primary text-white">Arcenciel</a> </small> </p>
                    </div>
                    <div class="col-lg-6 col-sm-6 text-right"> </div>
                </div>
            </div>
        </section>
        <a href="https://t.me/poulpalcom" class="float noprint" style="right:40px;position:fixed;bottom:40px;"
            target="_blank"><i class="mdi mdi-telegram mdi-48px" style="color:#043477"> </i></a>
        <a href="https://wa.me/+982191031869" class="float noprint"
            style="color:green;right:80px;position:fixed;bottom:40px;" target="_blank"><i
                class="mdi mdi-whatsapp mdi-48px"> </i></a>
    </div>
    <script src="{{ asset('css/bootstrap.bundle.min.js') }}"></script>
    @yield('script')
</body>

</html>
