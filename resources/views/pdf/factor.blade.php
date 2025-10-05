<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title></title>

    <style>
        footer_text
        /* *****************************************************

WHMCS Printable Invoice CSS Stylesheet
Created: 1st September 2011
Last Updated: 12th 08 2021
Version: 1.3

This file is part of the WHMCS Billing Software
  http://www.one3erver.com/

***************************************************** */

        @font-face {
            font-family: 'Mirza';
            src: url('fonts/Mirza.eot?#') format('eot'), url('fonts/Mirza.woff') format('woff'), url('fonts/Mirza.ttf') format('truetype');
            font-size: 10px;
        }

        @font-face {
            font-family: iransans;
            src: url("../fonts/iran-sans/IRANSans.eot?#iefix") format("embedded-opentype"), url("../fonts/iran-sans/IRANSans.woff") format("woff"), url("../fonts/iran-sans/IRANSans.ttf") format("truetype"), url("../fonts/iran-sans/IRANSans.svg#iransans") format("svg");
            /*font-style: normal;*/
            /*font-weight: 400;*/
        }

        body {
            margin: 30px;
            background-color: #eaeaea;
            direction: rtl;
            font-family: "iransans", iransans, "Arial", sans-serif !important;
        }

        body,
        td,
        input,
        select {
            font-family: iransans, "Mirza", "Arial", sans-serif;
            font-size: 12px !important;
            color: #000000;
            line-height: 1.4em;
        }

        .clear {
            clear: both;
        }

        form {
            margin: 0px;
        }

        a {
            font-size: 11px;
            color: #1E598A;
            padding: 10px;
        }

        a:hover {
            text-decoration: none;
        }

        .textcenter {
            text-align: center;
        }

        .textright {
            text-align: right;
        }

        .wrapper {
            margin: 0 auto 30px;
            padding: 30px;
            width: 640px;
            border: #666 solid 1px;
            background-color: #fff;
            border: 1px solid #ccc;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
            -moz-box-shadow: 0 0 2px rgba(0, 0, 0, .1);
            -webkit-box-shadow: 0 0 2px rgba(0, 0, 0, .1);
            box-shadow: 0 0 2px rgba(0, 0, 0, .1);
        }

        @media print {
            body {
                width: 100% !important;
                margin: 0;
                padding: 0;
                font-size: 10px !important;
            }

            .samfony_invoice_wrapper {
                width: 94% !important;
                border: none;
            }

            td {
                font-size: 9px !important;
            }

            .title {}

            table.items tr.title td {
                padding: 2px 5px !important;
            }
        }

        .date,
        .duedate {
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            background-color: #fff;
            -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .11), inset 0 -2px 2px rgba(0, 0, 0, .05);
            -moz-box-shadow: 0 1px 3px rgba(0, 0, 0, .11), inset 0 -2px 2px rgba(0, 0, 0, .05);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .11), inset 0 -2px 2px rgba(0, 0, 0, .05);
            border: solid 1px #cfcfcf;
            float: right;
            font-size: 100%;
            padding: 7px 12px;
            margin-left: 15px;
        }

        .date strong {
            color: #007cb6;
        }

        .duedate strong {
            color: #af0000;
        }

        .header {
            margin: 0 0 15px 0;
            width: 100%;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
        }

        table.address {
            background: #eaeaea;
            width: 100%;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
        }

        table.address td {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
            vertical-align: top;
        }

        table.items {
            width: 100%;
            background-color: #ccc;
            border-spacing: 0;
            border-collapse: separate;
            border-left: 1px solid #ccc;
        }

        table.items tr.title td {
            margin: 0;
            padding: 2px 12px;
            line-height: 16px;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
            color: #333;
            font-size: 11px;
            font-weight: bold;
            background-color: #fafafa;
            background-image: -webkit-linear-gradient(bottom, rgba(1, 2, 2, .03), rgba(255, 255, 255, .03));
            background-image: -moz-linear-gradient(bottom, rgba(1, 2, 2, .03), rgba(255, 255, 255, .03));
            background-image: -o-linear-gradient(bottom, rgba(1, 2, 2, .03), rgba(255, 255, 255, .03));
            background-image: -ms-linear-gradient(bottom, rgba(1, 2, 2, .03), rgba(255, 255, 255, .03));
            background-image: linear-gradient(to top, rgba(1, 2, 2, .03), rgba(255, 255, 255, .03));
        }

        table.items td {
            margin: 0;
            padding: 2px 8px;
            line-height: 15px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
        }

        table.items tr:last-child td {
            border-bottom: 1px solid #ccc;
        }

        .row {
            margin: 25px 0;
        }

        .title {
            font-size: 16px;
            /*    font-weight: bold;*/
            line-height: 35px;
        }

        .subtitle {
            font-size: 14px;
            font-weight: bold;
        }

        .unpaid {
            font-size: 16px;
            color: #cc0000;
            font-weight: bold;
        }

        .paid {
            font-size: 16px;
            color: #779500;
            font-weight: bold;
        }

        .refunded {
            font-size: 16px;
            color: #224488;
            font-weight: bold;
        }

        .cancelled {
            font-size: 16px;
            color: #cccccc;
            font-weight: bold;
        }

        .collections {
            font-size: 16px;
            color: #ffcc00;
            font-weight: bold;
        }

        .creditbox {
            margin: 0 auto 15px auto;
            padding: 10px;
            border: 1px dashed #cc0000;
            font-weight: bold;
            background-color: #FBEEEB;
            text-align: center;
            width: 95%;
            color: #cc0000;
            direction: rtl;
        }

        .btn,
        i {
            background: #ffffff;
            border: 1px solid #cdd1d5;
            color: #515151;
            cursor: pointer;
            display: inline-block;
            font-size: 105%;
            -webkit-box-shadow: inset 0px -1px 0px 0px rgba(250, 250, 250, 1);
            -moz-box-shadow: inset 0px -1px 0px 0px rgba(250, 250, 250, 1);
            box-shadow: inset 0px -1px 0px 0px rgba(250, 250, 250, 1);
            line-height: 21px;
            margin-bottom: 0;
            padding: 6px 13px;
            text-align: center;
            text-decoration: none;
            zoom: 1;
            border-radius: 3px;
            moz-border-radius: 3px;
            -webkit-border-radius: 3px;
        }

        .btn-primary,
        input[type=submit] {
            border-radius: 3px;
            moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            padding: 6px 13px;
            background: #008db5;
            background: -moz-linear-gradient(top, #008db5 0%, #008db5 50%, #008db5 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #008db5), color-stop(50%, #008db5), color-stop(100%, #008db5));
            background: -webkit-linear-gradient(top, #008db5 0%, #008db5 50%, #008db5 100%);
            background: -o-linear-gradient(top, #008db5 0%, #008db5 50%, #008db5 100%);
            background: -ms-linear-gradient(top, #008db5 0%, #008db5 50%, #008db5 100%);
            background: linear-gradient(to bottom, #008db5 0%, #008db5 50%, #008db5 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#008db5', endColorstr='#008db5', GradientType=0);
            -webkit-box-shadow: inset 0px 1px 1px 0px rgba(0, 150, 187, 1), inset 0px -1px 1px 0px rgba(0, 150, 187, 1);
            ;
            -moz-box-shadow: inset 0px 1px 1px 0px rgba(0, 150, 187, 1), inset 0px -1px 1px 0px rgba(0, 150, 187, 1);
            ;
            box-shadow: inset 0px 1px 1px 0px rgba(0, 150, 187, 1), inset 0px -1px 1px 0px rgba(0, 150, 187, 1);
            ;
            color: #fff;
            border: 1px solid #007697;
        }

        .btn-success {
            background: #28af62;
            background: -moz-linear-gradient(top, #28af62 0%, #27ae60 50%, #27ae60 100%, #27ae60 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #28af62), color-stop(50%, #27ae60), color-stop(100%, #27ae60), color-stop(100%, #27ae60));
            background: -webkit-linear-gradient(top, #28af62 0%, #27ae60 50%, #27ae60 100%, #27ae60 100%);
            background: -o-linear-gradient(top, #28af62 0%, #27ae60 50%, #27ae60 100%, #27ae60 100%);
            background: -ms-linear-gradient(top, #28af62 0%, #27ae60 50%, #27ae60 100%, #27ae60 100%);
            background: linear-gradient(to bottom, #28af62 0%, #27ae60 50%, #27ae60 100%, #27ae60 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#28af62', endColorstr='#27ae60', GradientType=0);
            -webkit-box-shadow: inset 0px 1px 1px 0px rgba(42, 181, 105, 1), inset 0px -1px 1px 0px rgba(37, 165, 91, 1);
            ;
            -moz-box-shadow: inset 0px 1px 1px 0px rgba(42, 181, 105, 1), inset 0px -1px 1px 0px rgba(37, 165, 91, 1);
            ;
            box-shadow: inset 0px 1px 1px 0px rgba(42, 181, 105, 1), inset 0px -1px 1px 0px rgba(37, 165, 91, 1);
            border: 1px solid #25a55b;
            color: #fff;
        }

        .btn:hover {
            background: #eee;
        }

        .btn-primary:hover {
            background: #00719f;
        }

        .btn-success:hover {
            background: #5ba000;
        }

        input[type=text],
        input[type=password],
        select,
        textarea,
        input[type=email],
        input[type=file] {
            background-color: #fff;
            box-shadow: 0 2px 2px rgba(255, 255, 255, .1), inset 1px 2px 0 rgba(207, 211, 218, .15);
            border: 1px solid #ccc;
            -moz-box-shadow: 0 2px 2px rgba(255, 255, 255, .1), inset 1px 2px 0 rgba(207, 211, 218, .15);
            border: 1px solid #ccc;
            -webkit-box-shadow: 0 2px 2px rgba(255, 255, 255, .1), inset 1px 2px 0 rgba(207, 211, 218, .15);
            border: 1px solid #ccc;
            padding: 6px;
            border: 1px solid #bbcad1;
            -moz-transition: -moz-box-shadow 0.3s;
            -webkit-transition: -webkit-box-shadow 0.3s;
            transition: box-shadow 0.3s;
            color: #666;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border: solid 1px #0f90c4;
            -webkit-box-shadow: 0 0 6px rgba(6, 152, 255, .35);
            -moz-box-shadow: 0 0 6px rgba(6, 152, 255, .35);
            box-shadow: 0 0 6px rgba(6, 152, 255, .35);
            outline: none;
        }

        input:disabled,
        select:disabled,
        textarea::disabled {
            filter: Alpha(opacity=60);
            opacity: 0.6;
        }

        input[type="file"],
        input[type="file"]:focus {
            background: 0;
            border: 0;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            padding: 0;
        }

        input[type="checkbox"] {
            background: none;
            border: 0;
            -webkit-border-radius: 0;
            -moz-border-radius: 0;
            border-radius: 0;
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            padding: 0;
            width: auto !important;
        }

        .footer_text span {
            color: black;
            font-size: 11px;
            font-weight: 600;

        }

        .invoice_part_title {
            width: 100%;
            height: 20px;
            border: #666 solid 1px;
            border-radius: 3px;
            margin: 10px 0;
            text-align: center;
        }

        .invoice_part_title span {
            line-height: 18px;
            font-size: 15px;
            font-weight: bold;
        }

        .invoice_seller_title {
            /*font-weight: bold;*/
            font-size: 12px;
        }

        .samfony_invoice_wrapper {
            width: 940px;
        }

        .samfony_mobile_show {
            width: 100% !important;
            padding: 0 !important;
            margin-top: 10px;
            display: none;
        }

        @media only screen and (max-device-width: 500px) {
            #noninvoicenum {
                width: 93%;
                text-align: center;
            }

            #bg-noninvoicenum {
                font-size: 60px !important;
                margin-top: -5px;
            }

            .samfony_mobile_hidden {
                display: none !important;
            }

            .samfony_invoice_wrapper {
                width: unset;
            }

            .samfony_mobile_show {
                width: 100% !important;
                padding: 0 !important;
                margin-top: 10px;
                display: block;
            }

            .samfony_invoice_header td {
                line-height: 2em;
                padding: 10px;
            }

            .invoice_gateway_title_mobile {
                padding: 10px 0 !important;
                border: #666 solid 1px;
                border-radius: 3px;
                margin: 10px 0;
                text-align: center;
            }
        }
    </style>

</head>

<body>
    <div class="wrapper samfony_invoice_wrapper">


        <div class="invoice_header">
            <table width="100%" cellpadding="0" cellspacing="0" class="safony_mobile_hidden">
                <tr>
                    <td width="35%"></td>
                    <td width="20%" align="center">
                        <font
                            class="
                        @if ($factor->status == 'paid') paid
                        @else
                            unpaid @endif
                        ">
                            {{ $factor->status == 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</font>
                        <br />
                        <br />
                    </td>
                    <td width="20%" align="center">
                    </td>
                    <td width="25%">
                        شماره فاکتور: {{ $factor->token }}
                        </br />
                        تاریخ فاکتور : {{ Morilog\Jalali\Jalalian::forge($factor->created_at)->format('Y/m/d') }}

                    </td>
                </tr>
            </table>
        </div>
        <br />

        <!--   GHASEDAK BSS START - DONT SHOW VIRTUAL CREDIT -->
        <div class="invoice_part_title ">
            <span>مشخصات فروشنده</span>
        </div>
        <div class="invoice_seller ">
            <table width="100%" cellpadding="0" cellspacing="0">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="20%">
                            <span class="invoice_seller_title">
                                نام شخص حقیقی / حقوقی :
                            </span>
                        </td>
                        <td width="20%">
                            <span>
                                آرکانسیل
                            </span>
                        </td>
                        <td width="15%">
                            <span class="invoice_seller_title">شناسه ملی :</span>
                        </td>
                        <td width="15%">
                            <span>10103586118</span>
                        </td>
                        <td width="15%">
                            <span class="invoice_seller_title">شماره ثبت :</span>
                        </td>
                        <td width="15%">
                            <span>322324</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="15%">
                            <span class="invoice_seller_title">شهر :</span>
                        </td>
                        <td width="15%">
                            <span>تهران</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="20%">
                            <span class="invoice_seller_title">نشانی :</span>
                        </td>
                        <td colspan="3" width="50%">
                            <span>تهران-میدان نوبنیاد-ساختمان شماره۳ پارک فناوری پردیس</span>
                        </td>
                        <td width="15%">
                            <span class="invoice_seller_title">شماره تلفن / نمابر :</span>
                        </td>
                        <td width="15%">
                            <span>021-91031869</span>
                        </td>
                    </tr>
                </table>
            </table>
        </div>
        <div class="invoice_part_title ">
            <span>مشخصات خریدار</span>
        </div>
        <div class="invoice_customer ">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%">
                        <span class="invoice_seller_title">
                            نام شخص حقیقی / حقوقی :
                        </span>
                    </td>
                    <td width="20%">
                        <span>
                            {{ $factor->customer_name }}
                        </span>
                    </td>
                    <td width="15%">
                        <span class="invoice_seller_title">نام شرکت :</span>
                    </td>
                    <td width="15%">
                        <span></span>
                    </td>
                    <td width="15%">
                        <span class="invoice_seller_title">کدملی یا شناسه ملی :</span>
                    </td>
                    <td width="15%">
                        <span></span>
                    </td>
                </tr>
                <tr>
                    <td width="15%">
                        <span class="invoice_seller_title">شهر :</span>
                    </td>
                    <td width="15%">
                        <span>{{ $factor->city }}</span>
                    </td>
                    <td width="15%">
                        <span class="invoice_seller_title">کد اقتصادی :</span>
                    </td>
                    <td width="15%">
                        <span></span>
                    </td>
                </tr>
                <tr>
                    <td width="20%">
                        <span class="invoice_seller_title">نشانی :</span>
                    </td>
                    <td colspan="3" width="50%">
                        <span>{{ $factor->address }}</span>
                    </td>
                    <td width="15%">
                        <span class="invoice_seller_title">شماره تلفن / نمابر :</span>
                    </td>
                    <td width="15%">
                        <span>
                            @if ($factor->token == 'afb12c87-692b-40a3-95c3-b61ccec18708')
                                09125149730
                            @endif
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <br />
        <div class="invoice_part_title">
            <span>مشخصات کالا یا خدمات مورد معامله</span>
        </div>
        <div class="invoice_product ">
            <table class="items">
                <tr class="title textcenter">
                    <td width="5%">ردیف</td>
                    <td width="40%">توضیحات</td>
                    <td width="15%">تعداد</td>
                    <td width="15%">فی</td>
                    <td width="15%">مبلغ</td>
                </tr>
                @foreach ($factor->items as $item)
                    <tr>
                        <td align="center">{{ $loop->iteration }}</td>
                        <td style="text-align: right;">{{ $item->name }}</td>
                        <td align="center">
                            {{ $item->quantity }}
                        </td>
                        <td align="center">
                            {{ number_format($item->price) }}
                        </td>
                        <td class="textcenter pp">
                            {{ number_format($item->price * $item->quantity) }}
                        </td>
                    </tr>
                @endforeach
                @php
                    $subtotal = 0;
                    foreach ($factor->items as $item) {
                        $subtotal += $item->price * $item->quantity;
                    }
                @endphp
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="total-row text-right"><strong>جمع جزء</strong></td>
                    <td class="pp" align="center">{{ number_format($subtotal) }}</td>
                </tr>
                @if ($factor->has_vat)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="total-row text-right"><strong>{{ $factor->vat_percent }}% مالیات و عوارض</strong>
                        </td>
                        <td class="pp" align="center">{{ number_format(($subtotal * $factor->vat_percent) / 100) }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="total-row text-right"><strong>کل</strong></td>
                    <td class="pp" align="center">{{ number_format($factor->amount) }}</td>
                </tr>

            </table>
            <br />
        </div>
        <br />
        @if ($factor->description)
            <div style="margin:10px 0 0 0;padding:10px 5px 10px 5px; border-radius: 5px; border:1px solid #b1b1b1;text-align: center;"
                class="neon-style">
                <span style="color: #000;">توضیحات:</span>
                <span style="color: #000;">{{ $factor->description }}</span>
            </div>
            <br />
        @endif
        <div class="invoice_part_title ">
            <span>مشخصات پرداخت</span>
        </div>
        <div class="">
            <table class="items" style="direction: rtl;">
                <tr class="title textcenter">
                    <td width="30%"></td>
                    <td width="30%">تاریخ پرداخت</td>
                    <td width="25%">درگاه</td>
                    <td width="25%">شناسه پرداخت</td>
                    <td width="20%">مبلغ</td>
                </tr>

                <tr class="title">
                    <td class="textright" colspan="4">باقی مانده حساب:</td>
                    <td class="textcenter pp">{{ number_format($factor->amount) }}</td>
                </tr>
            </table>
        </div>



        <div class="invoice_part_title " style="height: 100px;">
            مهرو امضای فروشنده
            <span style="display: none">مهر و امضای فروشنده </span>
            <span style="height: 100%;text-align: right;padding-right: 5px;float: right;width: 49%;">تاریخ پرداخت
                صورتحساب:


                </br> <span style="text-align: right;float: right;width: 49%;margin-top: 20px;"></span> </span>
            <span
                style="height: 100%;text-align: right;padding-right: 5px;float: right;border-right: #666 solid 1px;width: 49%;">مهر
                و امضا خریدار</br>
                <span style="text-align: right;float: right;width: 49%;margin-top: 20px;">تاریخ امضا</span></span>

        </div>

    </div>

</body>

</html>
