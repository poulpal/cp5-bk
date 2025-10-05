@extends('layouts.app')

@section('title', 'لیست ساکنین')

@section('head')
    <style>
        table * {
            text-align: center;
            vertical-align: middle !important;
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
    <section class="section-padding text-right" style="background-color:#edf0f4;color:#000">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 section-title farsi mb-1">
                    <h3>اعضا</h3>
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
                    <h4>
                        <a class="btn btn-primary" href="{{ route('building_manager.units.create') }}">افزودن ساکن
                            جدید</a>
                    </h4>
                    <div class="control-group form-group">
                        <div class="controls">
                            <div class="table-responsive">
                                <table class="table farsi text-center">
                                    <thead>
                                        <tr style="border: 2px #F4D1B6 ridge; background-color:#f2e1d5">
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                عملیات</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                مبلغ بدهی</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                مبلغ شارژ</th>
                                            {{-- <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                مالکیت</th> --}}
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                اطلاعات حساب</th>
                                            <th
                                                style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                شماره واحد</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($units as $unit)
                                            <tr style="border-bottom: 2px #F4D1B6 ridge;">
                                                <td
                                                    style="border-right: 2px #F4D1B6 ridge;border-left: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                    <a href="{{ route('building_manager.units.showAddInvoice', ['building_unit' => $unit->id]) }}" target="_blank"
                                                        class="btn btn-primary mb-1"><strong>ایجاد سند
                                                            حسابداری</strong></a>
                                                    <br>
                                                    {{-- <button type="submit" form="update-form-{{ $unit->id }}"
                                                        class="btn btn-success mb-1"><strong>ویرایش شارژ</strong></button> --}}
                                                    <form id="delete-form-{{ $unit->id }}"
                                                        action="{{ route('building_manager.units.destroy', ['building_unit' => $unit->id]) }}"
                                                        method="POST" class="mb-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" form="delete-form-{{ $unit->id }}"
                                                            class="btn btn-danger"><strong>حذف</strong></button>
                                                    </form>

                                                </td>
                                                <form method="POST" id="update-form-{{ $unit->id }}"
                                                    action="{{ route('building_manager.units.update', ['building_unit' => $unit->id]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <td
                                                        style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                        {{ number_format($unit->charge_debt) }}

                                                    </td>
                                                    <td
                                                        style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                        {{-- <input type="text" name="charge_fee"
                                                            form="update-form-{{ $unit->id }}"
                                                            value="{{ $unit->charge_fee }}"> --}}
                                                        {{ number_format($unit->charge_fee) }}
                                                    </td>
                                                    <td
                                                        style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                        @foreach ($unit->residents as $resident)
                                                            {{ $resident->full_name }} -
                                                            {{ $resident->mobile }} - {{ $resident->pivot->ownership == 'owner' ? 'مالک' : 'مستاجر' }}
                                                            <br>
                                                        @endforeach
                                                        <br>
                                                        <a href="{{ route('building_manager.invoices.index', ['mobile' => $unit->mobile, 'building_unit_id' => $unit->id]) }}"
                                                            class="mt-2 text-info">مشاهده
                                                            صورتحساب</a>
                                                    </td>
                                                    <td
                                                        style="border-right: 2px #F4D1B6 ridge;font-size:1.2em; font-weight: normal">
                                                        <a href="88_0"
                                                            target="_blank">{{ $unit->unit_number }}</a>
                                                    </td>
                                                </form>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="m-auto">
                                    {{ $units->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
