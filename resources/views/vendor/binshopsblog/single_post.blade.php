@extends('layouts.app', ['title' => $post->gen_seo_title()])


@section('title', $post->gen_seo_title())

@section('blog-custom-css')
    <link type="text/css" href="{{ asset('binshops-blog.css') }}" rel="stylesheet">
    <style>
        img {
            max-width: 100% !important;
        }
    </style>
@endsection

@section('content')

    @if (config('binshopsblog.reading_progress_bar'))
        <div id="scrollbar">
            <div id="scrollbar-bg"></div>
        </div>
    @endif


    <div class='container pt-3'>
        <div class="col-md-10 m-auto">
            <div class="row">

                <div class="col-md-4 pb-4 order-md-0 order-1">
                    @if (config('binshopsblog.search.search_enabled'))
                        @include('binshopsblog::sitewide.search_form')
                    @endif
                    @if ($post->categories->count() > 0)
                        <h6>کلیدواژه ها</h6>
                        <div class="d-flex flex-wrap">

                            @forelse($post->categories as $category)
                                <a href="{{ route('binshopsblog.view_category_new', ['categorySlug' => $category->slug]) }}"
                                    class="ms-3">
                                    <h6>{{ $category->category_name }}</h6>
                                </a>
                            @empty
                                {{-- <a href="#">
                                <h6>دسته بندی یافت نشد</h6>
                            </a> --}}
                            @endforelse
                        </div>
                    @endif
                    <div class="mb-3"></div>
                    @php
                        $related_posts = \BinshopsBlog\Models\BinshopsBlogPost::where('id', '!=', $post->id)
                            ->whereHas('categories', function ($query) use ($post) {
                                foreach ($post->categories as $index => $category) {
                                    if ($index == 0) {
                                        $query->where('binshops_blog_category_id', $category->id);
                                    } else {
                                        $query->orWhere('binshops_blog_category_id', $category->id);
                                    }
                                }
                            })
                            ->where('is_published', '=', 1)
                            ->where('posted_at', '<', Carbon\Carbon::now()->format('Y-m-d H:i:s'))
                            ->orderBy('posted_at', 'desc')
                            ->limit(6)
                            ->get();

                        if ($related_posts->count() < 6) {
                            $related_posts = $related_posts->merge(
                                \BinshopsBlog\Models\BinshopsBlogPost::where('id', '!=', $post->id)
                                    ->where('is_published', '=', 1)
                                    ->where('posted_at', '<', Carbon\Carbon::now()->format('Y-m-d H:i:s'))
                                    ->orderBy('posted_at', 'desc')
                                    ->limit(6 - $related_posts->count())
                                    ->get(),
                            );
                        }
                    @endphp
                    @if ($related_posts->count() > 0)
                        <h6>مطالب مرتبط</h6>
                        <div class="d-flex flex-wrap">
                            @foreach ($related_posts as $related_post)
                                @include('binshopsblog::partials.related_loop')
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-md-8 order-md-1 order-0 px-3">

                    @include('binshopsblog::partials.show_errors')
                    @include('binshopsblog::partials.full_post_details')


                    @if (config('binshopsblog.comments.type_of_comments_to_show', 'built_in') !== 'disabled')
                        <div class="" id='maincommentscontainer'>
                            <h2 class='text-center' id='BinshopsBlogcomments'>نظرات</h2>
                            @include('binshopsblog::partials.show_comments')
                        </div>
                    @else
                        {{-- Comments are disabled --}}
                    @endif

                </div>

            </div>
        </div>
    </div>

@endsection

@section('blog-custom-js')
    <script src="{{ asset('binshops-blog.js') }}"></script>
@endsection

@push('modals')
    <div class="modal fade" id="bannerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="$('#bannerModal').modal('hide');" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a href="https://cp.chargepal.ir" title="ورود به شارژپل">
                        <img src="{{ asset('img/banners/blog-banner.png') }}" alt="ورود به شارژپل" title="ورود به شارژپل" />
                    </a>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            setTimeout(() => {
                $('#bannerModal').modal('show');
            }, 20 * 1000);
        });
    </script>
@endpush
