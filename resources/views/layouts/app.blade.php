<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    {{-- <title>شارژپل | @yield('title')</title>
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
    <meta name="apple-mobile-web-app-capable" content="yes" />

    <meta charset="utf-8" />
    <meta name="description"
        content="شارژپل، نرم افزار مدیریت ساختمان،  به شما این امکان را میدهد تا  امور مختلف ساختمان مانند شارژ ساختمان، حسابداری ساختمان، مالیات و اطلاعیه های امور ساختمان را  به راحتی مدیریت  کنید." />
    <meta name="keywords" content="بیمه و مالیات شارژپل, شارژ, ساختمان, مدیریت, هوشمند, شارژ ساختمان, شارژپل" />
    <meta name="author" content="ChargePal" />
    <meta name="robots" content="index, follow" />


    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:title" content="شارژپل | سامانه مدیریت شارژ ساختمان" />
    <meta property="og:description"
        content="شارژپل، نرم افزار مدیریت ساختمان،  به شما این امکان را میدهد تا  امور مختلف ساختمان مانند شارژ ساختمان، حسابداری ساختمان، مالیات و اطلاعیه های امور ساختمان را  به راحتی مدیریت  کنید.." />
    <meta property="og:image" content="https://chargepal.ir/img/logo.png" /> --}}


    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    {!! SEOMeta::generate(true) !!}
    {!! OpenGraph::generate(true) !!}
    {!! Twitter::generate(true) !!}
    {!! JsonLd::generate(true) !!}

    <link href="{{ asset('bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('globals.css') }}?ver=26" rel="stylesheet">
    <link href="{{ asset('style.css') }}?ver=26" rel="stylesheet">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-4G2X8NHLC2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag("js", new Date());
        gtag("config", "G-4G2X8NHLC2");
    </script>

    <script type="text/javascript">
        window.$crisp = [];
        window.CRISP_WEBSITE_ID = "29afeb5a-8fa4-4b96-b645-23660ea9f4a8";
        (function() {
            d = document;
            s = d.createElement("script");
            s.src = "https://client.crisp.chat/l.js";
            s.async = 1;
            d.getElementsByTagName("head")[0].appendChild(s);
        })();
    </script>

    @yield('head')
    @stack('styles')
    @yield('blog-custom-css')
</head>

<body>
    @stack('modals')
    {{-- @include('layouts.components.login-modal') --}}
    @include('layouts.header')
    <main>
        @yield('content')
    </main>
    {{-- @include('layouts.footer') --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap.min.js') }}"></script>
    {{-- <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}

    @yield('script')
    @stack('scripts')
</body>

</html>
