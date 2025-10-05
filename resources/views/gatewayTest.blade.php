@extends('layouts.app')

@section('title', 'صفحه اصلی')

@section('content')
    <div class="container pt-3">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                <ul>
                    <li>{{ session('error') }}</li>
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('gateWayTest') }}">
            @csrf

            <div class="form-group">
                <label for="amount">مبلغ</label>
                <input type="number" class="form-control" id="amount" name="amount" required value="20000">
            </div>

            <div class="form-group">
                <label for="driver">درگاه</label>
                <select class="form-control" id="driver" name="driver" required>
                    <option value="zarinpal">Zarinpal</option>
                    <option value="sep">SEP</option>
                    {{-- <option value="saman">Saman</option> --}}
                    <option value="sepehr">Sepehr</option>
                    <option value="pasargad">Pasargad</option>
                </select>
            </div>

            {{-- <div class="form-group">
                <label for="merchantId">شماره پذیرنده</label>
                <input type="text" class="form-control" id="terminalId" name="terminalId">
            </div>

            <div class="form-group">
                <label for="terminalId">شماره ترمینال</label>
                <input type="text" class="form-control" id="terminalId" name="terminalId">
            </div> --}}

            <button type="submit" class="btn btn-primary">تایید</button>
        </form>
    </div>
@endsection
