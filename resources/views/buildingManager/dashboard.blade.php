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
                            <h6 dir="auto"> <a href="{{ route('building_manager.profile') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>اطلاعات حساب</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('building_manager.units.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>ساکنین ساختمان</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('building_manager.units.create') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>افزودن ساکن جدید</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"><a href="{{ route('building_manager.invoices.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>صورتحساب</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"><a href="{{ route('building_manager.deposit_requests.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>درخواست های واریز</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"><a href="{{ route('building_manager.deposit_requests.create') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline">
                                    <strong>درخواست واریز جدید</strong></a></h6>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
