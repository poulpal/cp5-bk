@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')
    <div class="container">
        <div class="container-fluid pt--20 text-center">
            <section class="">
                <div class="row">
                    <div class="col-sm-12"> <img src="{{ asset('images/profile.jpg') }}" alt="فروشگاه اینترنتی فوری"
                            title="فروشگاه اینترنتی فوری"></div>
                </div>
            </section>
        </div>
    </div>
    <div class="container">
        <section class="section-padding">
            <div class="container-fluid pt--20 text-center">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('user.profile') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>اطلاعات حساب</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('user.invoices.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>صورتحساب</strong></a></h6>
                        </div>
                    </div>
                    @if($units)
                        <div class="col-sm-6">
                            <div class="product-body text-center">
                                <h6 dir="auto"> <a href="{{ route('user.units.index') }}"
                                        style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                        class="h5 farsi oneline"><strong>پرداخت شارژ ساختمان</strong></a></h6>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
