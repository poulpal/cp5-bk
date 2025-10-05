@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')
    {{-- <div class="container">
        <div class="container-fluid pt--20 text-center">
            <section class="">
                <div class="row">
                    <div class="col-sm-12"> <img src="{{ asset('images/profile.jpg') }}" alt="فروشگاه اینترنتی فوری"
                            title="فروشگاه اینترنتی فوری"></div>
                </div>
            </section>
        </div>
    </div> --}}
    <div class="container pt-3">
        <section class="section-padding">
            <div class="container-fluid pt--20 text-center">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.logout') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>خروج</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.users.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>اطلاعات کاربران</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.voiceMessages.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>پیام های صوتی</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.fcmMessages.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>نوتیفیکیشن ها</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.depositRequests.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>واریز ها</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.buildings.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>ساختمان ها</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.smsMessages.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>پیام های متنی</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.blog_admin') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>مدیریت وبلاگ</strong></a></h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="product-body text-center">
                            <h6 dir="auto"> <a href="{{ route('operator.supportTickets.index') }}"
                                    style="border:#000 2px outset;background-color:#226ccc;color:#fff"
                                    class="h5 farsi oneline"><strong>تیکت ها</strong></a></h6>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
