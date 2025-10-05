@extends('layouts.app')

@section('title', 'پاسخ به تیکت')

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
            <div class="col-md-9 m-auto mt-3">
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
                <div class="replies" style="max-height: 500px; overflow-y: auto;">
                    @foreach ($supportTicket->replies as $reply)
                        <div class="card mb-1">
                            <div class="card-header">
                                @if ($reply->from == 'user')
                                    {{ $reply->user->full_name }}
                                @else
                                    پشتیبانی
                                @endif

                            </div>
                            <div class="card-body">
                                {{ $reply->message }}
                                <br>
                                <small>
                                    {{ Morilog\Jalali\Jalalian::forge($reply->created_at)->format('%A %d %B %Y - %H:%M') }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('operator.supportTickets.reply', ['supportTicket' => $supportTicket->id]) }}" method="POST">
                    @csrf
                    <textarea name="message" class="form-control" rows="3"></textarea>
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        // scroll to bottom of replies
        var replies = document.querySelector('.replies');
        replies.scrollTop = replies.scrollHeight;

    </script>
@endsection
