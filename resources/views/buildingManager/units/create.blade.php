@extends('layouts.app')

@section('title', 'افزودن ساکن جدید')

@section('content')
    <section class="section-padding rast text-right farsi bg-white">
        <div class="container">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                <div class="pl-4 col-lg-5 col-md-5 mb-5 pr-4">
                    <h5><strong>عضو جدید</strong></h5>
                    <p class="mt-2" style="text-align: justify; text-justify: inter-word;color:#000"><strong>در این قسمت نام
                            سرپرست واحدهای مجتمع را می‌توانید اضافه نمایید؛ سیستم به طور هوشمند برای واحدهایی دارای مستاجر
                            باشند و یا بیش از یک مالک داشته و در این فرم تکرار شده باشند؛ پروفایل مشترک تشکیل خواهد
                            داد.</strong></p>
                    <p style="text-align: justify; text-justify: inter-word;">شماره موبایل وارد شده برای هر واحد می تواند به
                        طور مستقل اطلاعات مالی آن واحد را مشاهده و مدیریت نماید</p>
                    <p style="text-align: justify; text-justify: inter-word;color:#a80a0a">چنانچه تمایل دارید واحدی توسط
                        افراد مختلف کنترل شود(مانند مالک و مستاجر) کافیست آن واحد را در چند نوبت با شماره موبایل های متفاوت
                        وارد نمایید. </p>
                </div>
                <div class="col-lg-6 col-md-6 pl-5 pr-5" style="text-align: justify; text-justify: inter-word;">
                    <div class="product-body wraps rast text-right">
                        <form method="POST" id="form" action="{{ route('building_manager.units.store')}}"> @csrf @method('POST') <label>شماره موبایل<span class="text-danger">*</span> </label> <input
                                type="tel" placeholder="Example=09123456789" maxlength="11" class="form-control"
                                name="mobile" pattern=".{11,}" required="" title="11 characters minimum" value="{{old('mobile')}}"
                                onkeypress="return isNumber(event)"
                                data-validation-required-message="Please enter your phone number."> <label>شماره واحد<span
                                    class="text-danger">*</span> </label> <input type="text" placeholder="ََApartment"
                                class="form-control" name="unit_number" required="" value="{{old('unit_number')}}"> <label> مبلغ شارژ<span
                                    class="text-danger">*</span> </label> <input type="text"
                                class="form-control" name="charge_fee" id="amount" required="" value="{{old('charge_fee')}}"> <label>مالکیت<span
                                    class="text-danger">*</span> </label> <select name="ownership" class="form-control rast"
                                required="">
                                <option @if (old('ownership') == 'owner') selected @endif value="owner">مالک</option>
                                <option @if (old('ownership') == 'renter') selected @endif value="renter">مستاجر</option>
                            </select> </form>
                    </div> <button type="submit" class="btn btn-success" form="form">ارسال</button>
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