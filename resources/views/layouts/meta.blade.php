<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Favicon و آیکون‌ها --}}
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('icons/site.webmanifest') }}">
<link rel="mask-icon" href="{{ asset('icons/safari-pinned-tab.svg') }}" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">

{{-- متاتگ‌های اصلی --}}
<meta name="author" content="chargepal.ir">
<meta name="description" content="شارژپل - سیستم مدیریت هوشمند ساختمان و مجتمع‌های مسکونی. مدیریت شارژ، صورتحساب، پرداخت آنلاین و خدمات ساختمانی">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="website">
<meta property="og:url" content="{{ Request::url() }}">
<meta property="og:title" content="@yield('title', 'شارژپل') | مدیریت هوشمند ساختمان">
<meta property="og:description" content="شارژپل - سیستم مدیریت هوشمند ساختمان و مجتمع‌های مسکونی">
<meta property="og:image" content="{{ asset('img/coin.png') }}">
<meta property="og:site_name" content="شارژپل - ChargePal">
<meta property="business:contact_data:website" content="https://chargepal.ir">

{{-- Twitter Card --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ Request::url() }}">
<meta property="twitter:title" content="@yield('title', 'شارژپل') | مدیریت هوشمند ساختمان">
<meta property="twitter:description" content="شارژپل - سیستم مدیریت هوشمند ساختمان و مجتمع‌های مسکونی">
<meta property="twitter:image" content="{{ asset('img/coin.png') }}">
<meta property="twitter:site" content="@chargepal_ir">
<meta property="twitter:creator" content="@chargepal_ir">

{{-- SEO --}}
<meta name="robots" content="index,follow">
<link rel="canonical" href="{{ Request::url() }}">
<meta name="dcterms.subject" content="مدیریت هوشمند ساختمان - @yield('title', 'شارژپل')">

{{-- PWA --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="شارژپل">

{{-- Google Analytics --}}
<script async src="https://www.googletagmanager.com/gtag/js?id=G-76VXCG81DX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-76VXCG81DX');
</script>

{{-- Structured Data (JSON-LD) برای SEO بهتر --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "شارژپل",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IRR"
  },
  "provider": {
    "@type": "Organization",
    "name": "شارژپل",
    "url": "https://chargepal.ir"
  }
}
</script>