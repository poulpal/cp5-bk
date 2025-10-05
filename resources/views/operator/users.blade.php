@extends('layouts.app')

@section('title', 'اطلاعات کاربران')

@section('head')

    <link href="{{ asset('DataTables/datatables.min.css') }}" rel="stylesheet" />

    <style>
        table * {
            text-align: center !important;
        }
    </style>
@endsection

@section('content')
    <section class="account-page farsi text-right section-padding" style="direction:rtl">
        <div class="container">
            <div class="card">
                <div class="card-body">

                    {!! $table->table() !!}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('DataTables/datatables.min.js') }}"></script>
    {!! $table->scripts() !!}
@endsection
