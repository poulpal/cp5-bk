@extends('layouts.app')

@section('title', 'صورتحساب')

@section('head')
    <style>
        @media print {
            body>*:not(#printarea *) {
                visibility: hidden;
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

        /* on mobile */
        @media screen and (max-width: 600px) {
            table {
                font-size: 0.8em;
            }

            .table-hide-more {
                display: none;
            }
        }
    </style>
@endsection


@section('content')
    <section class="blog-page" id="printarea">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-body">
                        <h1 class="farsi h3 text-center"><a href="#">{{ $business->name }}</a></h1>
                        <p class="farsi text-center">{{ $business->name_en }}</p>
                        <section class="blog-page section-padding">
                            <div class="container">
                                <div class="table-responsive"><label class="text-right float-right rast">* 20 عملیات
                                        آخر</label>
                                    <table class="table farsifd text-center" style="font-size:0.6 em">
                                        <thead style="border: 3px #6398E8 ridge; background-color:#fff">
                                            <tr>
                                                <th style="border-right: 2px #6398E8 ridge" class="table-hide-more">بالانس</th>
                                                <th style="border-right: 2px #6398E8 ridge;font-size:0.8 em">بستانکار</th>
                                                <th style="border-right: 2px #6398E8 ridge;border-right: 2px #6398E8 ridge">
                                                    بدهکار</th>
                                                <th style="border-right: 2px #6398E8 ridge" class="table-hide-more">شرح</th>
                                                <th style="border-right: 2px #6398E8 ridge">سند</th>
                                                <th style="border-right: 2px #6398E8 ridge">#</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoices as $invoice)
                                                <tr class="rast"
                                                    style="border-bottom: 2px #6398E8 ridge;font-size:0.6 em;{{ $invoice->payment_method == 'cash' ? 'color:#2d913a' : '' }}">
                                                    <td
                                                        style="border-right: 2px #6398E8 ridge;border-left: 2px #6398E8 ridge;direction: ltr" class="table-hide-more">
                                                        {{ number_format($invoice->balance * 10) }}
                                                    </td>
                                                    <td class="rast"
                                                        style="border-right: 2px #6398E8 ridge;border-left: 2px #6398E8 ridge">
                                                        {{ $invoice->amount > 0 ? number_format($invoice->amount * 10) : 0 }}
                                                    </td>
                                                    <td style="border-right: 2px #6398E8 ridge">
                                                        {{ $invoice->amount < 0 ? number_format($invoice->amount * -1 * 10) : 0 }}
                                                    </td>
                                                    <td style="border-right: 2px #6398E8 ridge" class="table-hide-more">{{ $invoice->description }}
                                                    </td>
                                                    <td style="border-right: 2px #6398E8 ridge">
                                                        {{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d') }}
                                                        <br>
                                                        {{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('H:i') }}
                                                    </td>
                                                    <td style="border-right: 2px #6398E8 ridge">
                                                        {{ $invoice->payment_method == 'cash' ? '#' : '' }}{{ $invoice->id }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                    {{-- <table class="table farsifd text-center">
                                        <thead>
                                            <tr
                                                style="border: 2px #6398E8 ridge;border-bottom: 2px #6398E8 ridge; background-color:#B7D1F9;font-size:0.6 em">
                                                <th colspan="2"
                                                    style="border-right: 2px #6398E8 ridge;border-bottom: 2px #6398E8 ridge;color:0">
                                                    12,510,000</th>
                                                <th colspan="2" class="col-md-4 rast text-left"
                                                    style="border-right: 2px #6398E8 ridge;border-bottom: 2px #6398E8 ridge;color:0">
                                                    بدهکار :</th>
                                            </tr>
                                        </thead>
                                    </table> --}}
                                    <label class="text-right float-right rast">* ردیف هایی که با علامت # مشخص شده
                                        و به رنگ سبز هستند؛ به صورت دستی از طرف مجموعه وارد شده و پول‌پل نمی تواند آن را
                                        تایید و یا رد نماید.</label>
                                    <button type="submit" class="btn btn-dark noprint"
                                        onclick="window.print();">چاپ</button>
                                    {{ $invoices->links() }}
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
