@extends('layouts.app')

@section('title', 'درخواست واریز')

@section('content')
    <section class="section-padding rast text-right farsi bg-white">
        <div class="container">
            <div class="row">
                <div class="pl-4 col-lg-5 col-md-5 mb-5 pr-4">
                    <h5><strong>پرداخت آنلاین </strong></h5>
                    <p style="text-align: justify; text-justify: inter-word;">پرداخت بر اساس تمایل و یا توافق قبلی شما با
                        مجموعه انتخابی و ازطریق درگاه واسط پول‌پل صورت می گیرد. </p>
                    <p style="text-align: justify; text-justify: inter-word;">پول‌پل هیچگونه مسئولیتی در قبال تعهد مجتمع ها
                        به پرداخت کننده ندارد. وجوه واریزی توسط این درگاه پس از کسر کارمزد توافق شده بلافاصله به حساب مجموعه
                        انتخابی واریز خواهد شد؛ و امکان برگشت وجه از طریق پول‌پل وجود ندارد.</p>
                    <p style="text-align: justify; text-justify: inter-word;color:#a80a0a">لطفا توجه داشته باشید که در صورتی
                        که مالک شماره موبایل و کارت بانکی با نام واریز کننده، همخوانی نداشته باشد؛ در صورت بروز خطا وجه به
                        پرداخت کننده مسترد نخواهد شد.</p> <img class="rounded img-fluid"
                        src="{{ asset('images/پرداخت-آنلاین.webp') }}" alt="Card image cap">
                </div>
                <div class="col-lg-6 col-md-6" style="text-align: justify; text-justify: inter-word;">
                    <div class="product-body text-right">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('building_manager.deposit_requests.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>*موجودی کیف پول :<span
                                        class="text-danger farsifd">{{ number_format($balance) }}</span>تومان </label>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="deposit_to" id=""
                                        value="me" checked>
                                    <label class="form-check-label mr-3">
                                        واریز به حساب خودم
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="deposit_to" id=""
                                        value="other">
                                    <label class="form-check-label mr-3">
                                        واریز به حساب دیگری
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>مبلغ درخواستی(تومان)<span class="text-danger">*</span> </label>
                                <input placeholder="حداقل 50،000 " class="form-control" name="amount" id="amount"
                                    maxlength="8" pattern=".{5,}" required="" title="5 characters minimum"
                                    max="1000000" min="50000" onkeypress="return isNumber(event)"
                                    data-validation-required-message="Please enter your value.">
                            </div>
                            <div class="form-group">
                                <label>شماره شبا<span class="text-danger">*</span> </label>
                                <input placeholder="" class="form-control" name="sheba" id="shaba" maxlength="24"
                                    pattern=".{24,}" required="" title="26 characters minimum"
                                    data-validation-required-message="Please enter your value.">
                            </div>
                            <div class="form-group">
                                <label>توضیحات<span class="text-danger">*</span> </label>
                                <input placeholder="" class="form-control" name="description" id="description"
                                    required="" data-validation-required-message="Please enter your value.">
                            </div>
                            <button type="submit" class="btn text-white btn-success">ارسال</button>
                            <a href="{{ route('building_manager.dashboard') }}" class="btn text-white btn-danger">کنسل</a>
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
    <script>
        $(document).ready(function() {
            if ($('input[name=deposit_to]').val() == 'me') {
                $('#shaba').val('{{ $sheba }}');
                $('#shaba').prop('readonly', true);
            } else {
                $('#shaba').val('');
                $('#shaba').prop('readonly', false);
            }
            $('input[name=deposit_to]').change(function() {
                if ($(this).val() == 'me') {
                    $('#shaba').val('{{ $sheba }}');
                    $('#shaba').prop('readonly', true);
                } else {
                    $('#shaba').val('');
                    $('#shaba').prop('readonly', false);
                }
            });
        });
    </script>
@endsection
