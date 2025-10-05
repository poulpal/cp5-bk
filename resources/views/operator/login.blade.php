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
                        <form action="{{ route('operator.login.login') }}" method="POST">
                            @csrf
                            @method('post')
                            <div class="card card-body">
                                <div class="widget">
                                    <div class="section-header">
                                        <h5 class="heading-design-h5" style="font-family:tahoma; color:#043477"> ورود
                                            اپراتور
                                        </h5>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">ایمیل :</label>
                                                <input type="email" class="form-control"
                                                    name="email" required="" title="ایمیل صحیح نیست"
                                                    placeholder="ایمیل" value="{{ old('email') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">رمز ورود :</label>
                                                <input type="password" class="form-control"
                                                    name="password" required="" title="رمز ورود صحیح نیست"
                                                    placeholder="رمز ورود" value="{{ old('password') }}">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-right mb-2"> <button type="submit" class="btn btn-success btn-lg">
                                        ورود
                                    </button> </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
