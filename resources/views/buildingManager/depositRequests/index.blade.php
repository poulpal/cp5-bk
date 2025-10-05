@extends('layouts.app')

@section('title', 'لیست درخواست های واریز')

@section('content')
    <section class="section-padding text-right" style="background-color:#edf0f4;color:#000">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 section-title farsi mb-1">
                    <h3>لیست درخواست های واریز</h3>
                </div>
                @if ($errors->any())
                    <div class="col-lg-12 col-md-12">
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="col-lg-12 col-md-12">
                    <div class="control-group form-group">
                        <div class="controls">
                            <div class="table-responsive">
                                <table class="table farsi text-center">
                                    <thead>
                                        <tr style="border: 2px #F4D1B6 ridge; background-color:#f2e1d5">
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                توضیحات
                                            </th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                واریز به حساب</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                مبلغ درخواستی</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                وضعیت</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                تاریخ درخواست</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($depositRequests as $depositRequest)
                                            <tr style="border-bottom: 2px #F4D1B6 ridge;">
                                                <td style="border-right: 2px #F4D1B6 ridge; border-left: 2px #F4D1B6 ridge">
                                                    {{ $depositRequest->description }}
                                                </td>
                                                <td style="border-right: 2px #F4D1B6 ridge; border-left">
                                                    {{ $depositRequest->sheba }} {{ $depositRequest->deposit_to == 'me' ? '(حساب شما)' : ""}}
                                                </td>
                                                <td style="border-right: 2px #F4D1B6 ridge">
                                                    {{ number_format($depositRequest->amount) }} تومان
                                                </td>
                                                <td style="border-right: 2px #F4D1B6 ridge">
                                                    @if ($depositRequest->status == 'pending')
                                                        در انتظار پرداخت
                                                    @elseif($depositRequest->status == 'accepted')
                                                        پرداخت شده
                                                    @elseif($depositRequest->status == 'rejected')
                                                        رد شده
                                                    @endif
                                                </td>
                                                <td style="border-right: 2px #F4D1B6 ridge">
                                                    {{ jdate($depositRequest->created_at)->format('%Y/%m/%d %H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            {{ $depositRequests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
