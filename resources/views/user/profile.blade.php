@extends('layouts.app')

@section('title', 'درخواست پذیرندگی')

@section('content')
    <section class="section-padding text-right" style="background-color:#edf0f4;color:#000">
        <div class="container">
            <div class="row rast">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="col-lg-12 col-md-12 farsifd" action="{{ route('user.profile.update') }}"
                    enctype="multipart/form-data" name="myForm" method="POST">
                    @csrf
                    @method('put')
                    <div class="control-group form-group">
                        <div class="controls"> <label>نام <span class="text-danger">*</span></label> <input type="text"
                                class="form-control" name="first_name" value="{{ $user->first_name }}" required=""
                                data-validation-required-message="لطفا نام خود را وارد کنید.">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls"> <label>نام خانوادگی<span class="text-danger">*</span></label> <input
                                type="text" class="form-control" name="last_name" value="{{ $user->last_name }}"
                                required="" data-validation-required-message="لطفا نام خانوادگی خود را وارد کنید">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>شماره موبایل<span
                                    class="text-danger">*</span> </label>
                            <div class="controls"> <input type="tel" class="form-control" name="mobile" required="" readonly=""
                                    data-validation-required-message="لطفا شماره موبایل خود را وارد کنید."
                                    value="{{ $user->mobile }}"> </div>
                        </div>
                    </div>
                    <hr> <label><a href="https://poulpal.com/terms" class="farsifd" target="_blank">شرایط و قوانین
                            سایت</a>
                    </label> <button type="submit" class="btn farsifd btn-success">ثبت اطلاعات</button>
                </form>
            </div>
        </div>
    </section>
@endsection
