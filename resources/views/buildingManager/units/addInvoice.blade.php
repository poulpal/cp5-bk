@extends('layouts.app')

@section('title', 'افزودن سند حسابداری')

@section('content')
    <section class="section-padding rast text-right farsi bg-white">
        <div class="container">
            <div class="row">
                <div class="pl-4 col-lg-5 col-md-5 mb-5 pr-4">
                    <h5><strong>ایجاد سند حسابداری </strong></h5>
                    <p style="text-align: justify; text-justify: inter-word;">در این قسمت می‌توانید عملیات حسابداری انجام شده
                        خارج از سیستم پول‌پل را وارد نمایید.</p>
                    <p style="text-align: justify; text-justify: inter-word;">این عملیات در سندهای مربوطه شما نمایش داده شده
                        و در بخش مربوط به مجموعه شما و اعضاء آن با مسئولیت خودتان محاسبه خواهد شد.</p> <img
                        class="rounded img-fluid" src="{{ asset('images/badd.webp') }}" alt="add document cap">
                </div>
                <div class="col-lg-6 col-md-6 pl-5 pr-5" style="text-align: justify; text-justify: inter-word;">
                    <div class="product-body text-right">
                        <form method="POST"
                            action={{ route('building_manager.units.addInvoice', ['building_unit' => $building_unit]) }}>
                            @csrf
                            @method('POST')
                            <div class="form-group">
                                <input type="radio" name="type" checked="checked" value="debt">بدهکار -
                                <input type="radio" name="type" value="deposit"> بستانکار (واریز کرده) <p></p>
                            </div>

                            <div class="form-group">
                                <label for="unit_number" class="">شماره واحد</label>
                                <input id="unit_number" type="text"
                                    class="form-control @error('unit_number') is-invalid @enderror" name="unit_number"
                                    value="{{ $building_unit->unit_number }}" required autocomplete="unit_number" readonly>
                                @error('unit_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="amount" class="">مبلغ</label>
                                <input id="amount" class="form-control @error('amount') is-invalid @enderror"
                                    name="amount" value="{{ number_format(old('amount')) }}" required autofocus>
                                @error('amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="description" class="">بابت</label>
                                <input id="description" type="text"
                                    class="form-control @error('description') is-invalid @enderror" name="description"
                                    value="{{ old('description') }}" required>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>



                            <button type="submit" class="btn btn-success">ثبت</button>
                            <a href="javascript:window.open('','_self').close();" class="btn text-white btn-danger">کنسل</a>
                        </form>
                    </div>
                    <div class="product-footer"></div>
                </div>
            </div>
        </div>
    </section>
@endsection


@section('script')
    <script>
        $(document).ready(function() {
            autoSeperatedDoms = ['#amount'];
            autoSeperatedDoms.forEach(item => {
                $(item).on('input', function() {
                    $(item).val($(item).val().replace(/,/g, ''));
                    $(item).val($(item).val().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                });
            });
            $('form').submit(function() {
                autoSeperatedDoms.forEach(item => {
                    $(item).val($(item).val().replace(/,/g, ''));
                });
            });
        });
    </script>
@endsection
