@extends('layouts.app')

@section('title', 'صورتحساب')

@section('content')
    <section class="blog-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card-body">
                        <section class="blog-page section-padding">
                            <div class="container">
                                <div class="table-responsive">
                                    {{-- <a href="excel.php" style="color:green" class="tahoma"> خروجی
                                        اکسل <i class="mdi mdi-file-excel mdi-48px"></i></a> --}}
                                    <table class="table farsifd rast text-center">
                                        <thead style="border: 2px #043477 ridge; background-color:#C3D9F4">
                                            <tr style="border-bottom: 2px #043477 ridge; background-color:#C3D9F4">
                                                <th
                                                    style="border-right: 2px #043477 ridge;border-bottom: 2px #043477 ridge;font-size:1.2em; font-weight: normal">
                                                    #</th>
                                                <th
                                                    style="border-right: 2px #043477 ridge;border-bottom: 2px #043477 ridge;font-size:1.2em; font-weight: normal">
                                                    فاکتور/رفرنس</th>
                                                <th
                                                    style="border-right: 2px #043477 ridge;border-bottom: 2px #043477 ridge;font-size:1.2em; font-weight: normal">
                                                    حساب</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoices as $index => $invoice)
                                                <tr style="border-bottom: 2px #043477 ridge;;background-color:; ">
                                                    <th
                                                        style="border-right: 2px #043477 ridge;font-size:0.8em; font-weight: normal">
                                                        <a href="#">{{ $index + 1 }}
                                                            <hr style="border:#ccc 1px solid"><span
                                                                style="color:blue;font-size:1.2em">{{ Morilog\Jalali\Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d H:i') }}</span>
                                                        </a>
                                                    </th>
                                                    <th
                                                        style="border-right: 2px #043477 ridge;font-size:1.2em; font-weight: normal">
                                                        <a href="#">{{ $invoice->id }}
                                                            <hr style="border:#ccc 1px solid"><span
                                                                style="color:blue;font-size:0.8em">{{ $invoice->payment_tracenumber }}</span>
                                                        </a>
                                                    </th>
                                                    <th
                                                        style="border-right: 2px #043477 ridge;border-left: 2px #043477 ridge;font-size:1.2em; font-weight: normal;direction:ltr">
                                                        <a href="#">{{ number_format($invoice->amount) }}</a>
                                                        <br>
                                                        ({{ $invoice->description }})
                                                    </th>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
