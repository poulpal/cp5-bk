@extends('layouts.app')

@section('title', 'واریز ها')

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
            <div class="col-md-9 m-auto">
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

                <form action="{{ route('operator.depositRequests.acceptStore', ['depositRequest' => $depositRequest->id]) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="building">ساختمان</label>
                        <input name="building" id="building" class="form-control" value="{{ $depositRequest->building->name }}" readonly />
                    </div>



                    <div class="form-group">
                        <label for="amount">مبلغ (ریال)</label>
                        <input type="text" name="amount" id="amount" class="form-control"
                            value="{{ number_format($depositRequest->amount * 10) }}" readonly />
                    </div>

                    <div class="form-group">
                        <label for="sheba">شماره حساب</label>
                        <input type="text" name="sheba" id="sheba" class="form-control" required
                            value="{{ $depositRequest->sheba }}" readonly />
                    </div>
                    <div class="form-group">
                        <label for="trace_number">شماره پیگیری</label>
                        <input type="text" name="trace_number" id="trace_number" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="description">توضیحات</label>
                        <textarea name="description" id="description" class="form-control" required style="height: 200px"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#building').on('change', function() {
                var buildingId = $(this).val();
                window.location.href = "{{ route('operator.depositRequests.create') }}" + '?building=' +
                    buildingId;
            });
            $('#type').on('change', function() {
                var type = $(this).val();
                window.location.href = "{{ route('operator.depositRequests.create') }}" + '?building=' +
                    $('#building').val() + '&type=' + type;
            });

            const setDescription = () => {
                let traceNumber = $('#trace_number').val();
                let description = "{{ $depositRequest->description }} \n";
                let units = [];
                if (traceNumber) {
                    description += "شماره پیگیری : " + traceNumber + "\n";
                }
                $('input[name="pending_deposits[]"]:checked').each(function() {
                    // units += $(this).data('unit') + ",";
                    units.push($(this).data('unit'));
                });
                // if (units.length > 0) {
                //     description += "واحد ها : " + units.join("-") + "\n";
                // }
                $('#description').val(description);
            }

            $('#trace_number').on('change input', function() {
                setDescription();
            });

            $('input[name="pending_deposits[]"]').on('change', function() {
                let amount = 0;
                $('input[name="pending_deposits[]"]:checked').each(function() {
                    amount += parseInt($(this).data('amount'));
                });
                $('#amount').val(amount);
                setDescription();
            });

            setDescription();


        });
    </script>

@endsection
