@extends('layouts.app')

@section('title', 'صورتحساب')

@section('head')
    <style>
        @media print {
            body > *:not(#printarea) {
                visibility: hidden;
                height: 100px;
            }

            #printarea,
            #printarea * {
                visibility: visible;
            }

            #printarea {
                position: absolute;
                left: 0;
                top: 0;
            }
            .noprint {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    <section class="account-page section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto">
                    <div class="row no-gutters">
                        <div class="col-md-12">
                            <div class="">
                                <div class="widget">
                                    <div class="section-header"></div>
                                    <div class="order-list-tabel-main table-responsive" id="printarea">
                                        <table
                                            class="datatabel table table-striped table-bordered text-center farsifd order-list-tabel"
                                            style="font-size: 1.1em" width="100%" cellspacing="0">
                                            <tbody class="rast farsifd">
                                                <tr>
                                                    <td colspan="2">{{ $invoice->id }}</td>
                                                    <td>شماره فاکتور:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">{{ $invoice->user->full_name }}</td>
                                                    <td>مشتری:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">{{ $invoice->user->mobile }}</td>
                                                    <td>تلفن:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">---</td>
                                                    <td>آدرس ارسال:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        {{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at) }}</td>
                                                    <td>زمان سفارش:</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <table
                                            class="datatabel table table-striped table-bordered text-center farsifd order-list-tabel"
                                            style="font-size: 1.1em" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>قیمت کل</th>
                                                    <th>تعداد</th>
                                                    <th>قیمت</th>
                                                    <th>محصول</th>
                                                </tr>
                                            </thead>
                                            <tbody class="rast farsifd">
                                                <tr>
                                                    <td>{{ number_format($invoice->amount) }}<span
                                                            style="font-size:0.8em;color:red"> (%)</span></td>
                                                    <td>1</td>
                                                    <td>{{ number_format($invoice->amount) }}</td>
                                                    <td><span class="oneline"></span></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">{{ number_format($invoice->amount) }}</td>
                                                    <td colspan="3">قیمت کل:</td>
                                                </tr>
                                                <tr style="color:red">
                                                    <td colspan="2">0</td>
                                                    <td colspan="3">تخفیف:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">0</td>
                                                    <td colspan="3">ارسال:</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">{{ number_format($invoice->amount) }}</td>
                                                    <td colspan="3">قابل پرداخت:</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="noprint">
                                            <a href="javascript:window.open('','_self').close();"
                                                class="btn btn-danger farsifd text-white">برگشت</a>
                                            <button type="submit" class="btn btn-dark" onclick="window.print();">چاپ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
