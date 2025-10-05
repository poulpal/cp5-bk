@extends('layouts.app')

@section('title', 'ورود به سایت')

@section('content')
    <section class="account-page farsi text-right section-padding" style="direction:rtl">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-12">
                        <form action="{{ Session::has('mobile') ? route('login') : route('sendOtp') }}" method="POST">
                            @csrf
                            @method('post')
                            <div class="card card-body">
                                <div class="widget">
                                    <div class="section-header">
                                        <h5 class="heading-design-h5" style="font-family:tahoma; color:#043477"> ورود/ثبت
                                            نام
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group"> <label class="form-label">فرمت شماره موبایل :
                                                    09121234567 </label> <input type="text" class="form-control"
                                                    name="mobile" maxlength="11"
                                                    pattern="09()-?[0-9]{2}-?[0-9]{3}-?[0-9]{4}" required=""
                                                    title="شماره موبایل صحیح نیست" placeholder="شماره موبایل"
                                                    value="{{ Session::has('mobile') ? Session::get('mobile') : old('mobile') }}"
                                                    @if (Session::has('mobile')) disabled @endif
                                                    data-validation-required-message="Please enter your phone number.">
                                            </div>
                                        </div>
                                    </div>
                                    @if (Session::has('mobile'))
                                        <input type="hidden" name="mobile" value="{{ Session::get('mobile') }}">
                                        <div class="form-group">
                                            <label for="otp" class="form-label">کد یکبار مصرف</label>
                                            <input type="text" inputmode="numeric"
                                                class="form-control @error('otp') is-invalid @enderror" id="otp"
                                                name="otp" placeholder="کد یکبار مصرف را وارد کنید" autofocus
                                                autocomplete="off" value="{{ old('otp') }}" />
                                            @error('otp')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-right"> <button type="submit" class="btn btn-success btn-lg">
                                        ارسال
                                    </button> </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
