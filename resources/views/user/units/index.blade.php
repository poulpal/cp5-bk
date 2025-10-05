@extends('layouts.app')

@section('title', 'پرداخت شارژ ساختمان')

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
                        <img class="rounded img-fluid mt-3" src="images/پرداخت-آنلاین.webp" alt="Card image cap">
                </div>
                <div class="col-lg-6 col-md-6 pl-5 pr-5">
                    <div class="product-body p-3">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('user.units.pay') }}">
                            @csrf
                            @method('POST')
                            <div class="form-group" style="text-align: right;">
                                <label for="unit_id">نام واحد</label>
                                <select name="building_unit_id" id="unit_id" class="form-control rast">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->unit_number }} - ({{ $unit->building->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="text-align: right;">
                                <label for="mobile">شماره موبایل</label>
                                <input type="text" class="form-control" id="mobile" name="mobile"
                                    value="{{ auth()->user()->mobile }}" readonly>
                            </div>
                            <div class="form-group
                                    @error('amount') is-invalid @enderror"
                                style="text-align: right;">
                                <label for="amount">مبلغ</label>
                                <input type="text" class="form-control" id="amount" name="amount" value=""
                                    placeholder="مبلغ را وارد کنید">
                                @error('amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>
                            <button type="submit" class="btn btn-success">پرداخت</button>
                            <div class="mb-1"></div>
                        </form>
                    </div>
                    <div class="product-footer"></div>
                </div>
            </div>
        </div>
    </section>
    <script>
        $(document).ready(function() {
            charge_fees = [];
            @foreach ($units as $unit)
                charge_fees.push("{{ number_format($unit->charge_debt > 0 ? $unit->charge_debt : 0) }}");
            @endforeach
            console.log(charge_fees);
            var index = $('#unit_id').prop('selectedIndex');
            $('#amount').val(charge_fees[index]);
            $('#unit_id').on('change', function() {
                // get selected option index
                var index = $(this).prop('selectedIndex');
                $('#amount').val(charge_fees[index]);
            });

        });
    </script>
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