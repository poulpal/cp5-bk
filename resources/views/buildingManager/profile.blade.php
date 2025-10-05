@extends('layouts.app')

@section('title', 'درخواست پذیرندگی')

@section('content')
    <section class="section-padding text-right" style="background-color:#edf0f4;color:#000">
        <div class="container">
            <div class="row rast">
                <div class="col-lg-12 col-md-12 section-title farsifd mb-4">
                    <h3> درخواست پذیرندگی</h3><span>فرم درخواست زیر ویژه خدمات این سایت به غیر از <a href="gateway"
                            title="درگاه پرداخت هوشمند (اینترنتی)" alt="درگاه پرداخت هوشمند (اینترنتی)" target="_blanc"
                            style="color:blue">درگاه پرداخت هوشمند (اینترنتی) </a> می باشد؛ برای استفاده و دریافت درگاه لطفا
                        <a href="gateway" title="درگاه پرداخت هوشمند (اینترنتی)" alt="درگاه پرداخت هوشمند (اینترنتی)"
                            target="_blanc" style="color:blue">اینجا </a> را کلیک کنید.</span><br> <span
                        style="color:red">لطفا توجه داشته باشید که این فرم پس از ثبت امکان ویرایش و تغییر ندارد.</span><br>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="col-lg-12 col-md-12 farsifd"
                    enctype="multipart/form-data" name="myForm">
                    @csrf
                    @method('post')
                    <div class="control-group form-group">
                        <div class="controls"> <label>نام <span class="text-danger">*</span></label> <input type="text"
                                class="form-control" name="first_name" value="{{ $building_manager->first_name }}" required="" readonly=""
                                data-validation-required-message="لطفا نام خود را وارد کنید.">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="control-group form-group">
                        <div class="controls"> <label>نام خانوادگی<span class="text-danger">*</span></label> <input
                                type="text" class="form-control" name="last_name" value="{{ $building_manager->last_name }}"
                                required="" readonly="" data-validation-required-message="لطفا نام خانوادگی خود را وارد کنید">
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>شماره موبایل<span
                                    class="text-danger">*</span> </label>
                            <div class="controls"> <input type="tel" class="form-control" name="mobile" required="" readonly=""
                                    data-validation-required-message="لطفا شماره موبایل خود را وارد کنید."
                                    value="{{ $building_manager->mobile }}"> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>تلفن ثابت <span class="text-danger">*</span>
                            </label>
                            <div class="controls"> <input type="tel" placeholder="Example=02123456789" maxlength="11"
                                    minlength="5" class="form-control" name="phone_number" pattern=".{11,}" required="" readonly=""
                                    title="11 characters minimum" value="{{ $building_manager->business->phone_number }}"
                                    data-validation-required-message="Please enter Value."> </div>
                            <label><span class="text-danger">کد تایید از طریق تلفن ثابت به شما اعلام خواهد شد</span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>کدملی<span class="text-danger">*</span>
                            </label>
                            <div class="controls"> <input type="tel" maxlength="10" class="form-control"
                                    name="national_id" pattern=".{10,}" required="" readonly="" title="11 characters minimum"
                                    value="{{ $building_manager->business->national_id }}"
                                    data-validation-required-message="Please enter Value.">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>نوع مجموعه<span class="text-danger">*</span> </label> <select
                                    name="type" class="form-control rast" required="" readonly="">
                                    <option value="building_manager">مدیریت ساختمان</option>
                                </select> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>نام مجتمع به فارسی<span class="text-danger">*</span> </label> <input
                                    type="text" class="form-control" name="building_name" value="{{ $building_manager->business->name }}" required="" readonly="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>نام مجتمع به انگلیسی<span class="text-danger">*</span> </label> <input
                                    type="text" class="form-control" name="building_name_en" value="{{ $building_manager->business->name_en }}" required="" readonly="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>استان<span class="text-danger">*</span> </label> <input
                                    type="text" class="form-control" name="province" value="{{ $building_manager->business->province }}"
                                    required="" readonly="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>شهر<span class="text-danger">*</span> </label> <input
                                    type="text" class="form-control" name="city" value="{{ $building_manager->business->city }}"
                                    required="" readonly="">
                            </div>
                        </div>
                    </div>
                    <div class="row rast">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>محله<span class="text-danger">*</span> </label><input
                                    type="text" class="form-control" name="district" value="{{ $building_manager->business->district }}"
                                    required="" readonly="">
                                </select> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>آدرس دقیق <span class="text-danger">*</span> </label>
                                <textarea rows="3" cols="100" class="form-control rast" name="address" required="" readonly=""
                                    data-validation-required-message="Please enter Value" maxlength="400">{{ $building_manager->business->address }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>کدپستی 10 رقمی<span
                                    class="text-danger">*</span> </label>
                            <div class="controls"> <input type="text" maxlength="10" class="form-control"
                                    name="postal_code" pattern=".{10,}" title="10 characters minimum" required="" readonly=""
                                    value="{{ $building_manager->business->postal_code }}"
                                    data-validation-required-message="Please enter Value."> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12">
                            <div class="controls"> <label>آدرس ایمیل</label> <input type="email" class="form-control"
                                    name="email" value="{{ $building_manager->business->email }}" required readonly
                                    data-validation-required-message="Please enter Value."> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group form-group col-md-12"> <label>شماره شبا بانکی (بدون IR)<span
                                    class="text-danger">*</span> </label>
                            <div class="controls"> <input type="text" maxlength="24" class="form-control"
                                    name="sheba_number" pattern=".{24,}" title="24 characters minimum"
                                    onkeypress="return isNumber(event)" required="" readonly="" value="{{ $building_manager->business->sheba_number }}"
                                    data-validation-required-message="Please enter Value."> </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="control-group farsifd form-group col-md-12"> <label>شماره کارت<span
                                    class="text-danger">*</span> </label>
                            <div class="controls"> <input type="txt" maxlength="16" class="form-control"
                                    name="card_number" pattern=".{16,}" title="16 characters minimum"
                                    value="{{ $building_manager->business->card_number }}" required="" readonly=""
                                    data-validation-required-message="Please enter Value.">
                            </div>
                        </div>
                    </div>
                    <div class="control-group farsifd form-group col-md-12">
                        <div class="controls"> <label>تصویر روی کارت ملی <span class="text-danger">*</span> </label>
                            <input class="form-control farsifd rast" name="national_card_image" id="file1"
                                required="" readonly="" accept=".jpg,.png" type="file">
                        </div>
                        <img src="{{asset($building_manager->business->national_card_image)}}" alt="" style="height: 250px">
                    </div>
                    <hr> <label><a href="https://poulpal.com/terms" class="farsifd" target="_blank">شرایط و قوانین
                            سایت</a>
                    {{-- </label> <button type="submit" class="btn farsifd btn-success">قبول شرایط و ثبت</button> --}}
                </form>
            </div>
        </div>
    </section>
@endsection
