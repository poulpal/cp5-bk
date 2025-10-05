@extends('layouts.app')

@section('title', 'صفحه اصلی')

@section('content')
    <div class="container">
        @if (auth()->role())
            <div class="product-body pt--20 pb--20 text-center farsi" style="color:red; font-family:profile">
                <h6> <a href="{{ route(auth()->role() . '.dashboard') }}"
                        style="border:#000 2px outset;background-color:#043477;color:#fff"
                        class="h6 pb--10 farsi oneline"><strong> پروفایل کاربری </strong><i style="color:#DBD600"
                            class="mdi blink mdi-account-check mdi-36px"></i></a></h6>
            </div>
        @else
            <div class="product-body pt--20 pb--20 text-center farsi" style="color:red; font-family:profile">
                <h6> <a href="{{ route('login') }}" style="border:#000 2px outset;background-color:#043477;color:#fff"
                        class="h6 pb--10 farsi oneline"><strong> ورود/عضویت</strong> <i class="mdi mdi-login mdi-36px blink"
                            style="color:#DBD600"></i></a></h6>
            </div>
        @endif
    </div>
@endsection
