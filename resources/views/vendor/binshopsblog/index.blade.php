@extends('layouts.app', ['title' => $title])

@section('title', 'بلاگ')

@section('blog-custom-css')
    <link type="text/css" href="{{ asset('binshops-blog.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class='col-sm-12 BinshopsBlog_container pt-3'>
        @if (\Auth::check() && \Auth::user()->canManageBinshopsBlogPosts())
            <div class="text-center">
                <p class='mb-1'>
                    <br>
                    <a href='{{ route('binshopsblog.admin.index') }}' class='btn border  btn-outline-primary btn-sm '>
                        <i class="fa fa-cogs" aria-hidden="true"></i>
                        ورود به پنل ادمین</a>
                </p>
            </div>
        @endif

        <div class="row">
            <div class="col-md-9 m-auto">
                <div class="row">

                    <div class="col-md-4 pb-4">
                        @if (config('binshopsblog.search.search_enabled'))
                            @include('binshopsblog::sitewide.search_form')
                        @endif
                        @if ($categories->count() > 0)
                            <h6>کلیدواژه ها</h6>
                            <div class="d-flex flex-wrap">

                                @forelse($categories as $category)
                                    <a href="{{ route('binshopsblog.view_category_new', ['categorySlug' => $category->slug]) }}" class="mx-3">
                                        <h6>{{ $category->category_name }}</h6>
                                    </a>
                                @empty
                                    {{-- <a href="#">
                                    <h6>دسته بندی یافت نشد</h6>
                                </a> --}}
                                @endforelse
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">

                        {{-- @if ($category_chain)
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        @forelse($category_chain as $cat)
                                            / <a href="{{ $cat->url() }}">
                                                <span class="cat1">{{ $cat->category_name }}</span>
                                            </a>
                                        @empty
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif --}}

                        @if (isset($BinshopsBlog_category) && $BinshopsBlog_category)
                            <h1 class='text-center mt-2'> {{ $BinshopsBlog_category->category_name }}</h1>

                            @if ($BinshopsBlog_category->category_description)
                                <p class='text-center'>{{ $BinshopsBlog_category->category_description }}</p>
                            @endif

                        @else
                            <h1 class='text-center mt-2'>وبلاگ شارژپل</h1>
                        @endif

                        <div class="container">
                            <div class="row">
                                @forelse($posts as $post)
                                    @include('binshopsblog::partials.index_loop')
                                @empty
                                    <div class="col-md-12">
                                        <div class='alert alert-danger'>موردی یافت نشد</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div class='text-center  col-sm-4 mx-auto d-flex justify-content-center'>
                            {{ $posts->appends([])->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>

@endsection
