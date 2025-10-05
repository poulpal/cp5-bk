@if (\Auth::check() && \Auth::user()->canManageBinshopsBlogPosts())
    <a href="{{ $post->edit_url() }}" class="btn btn-outline-secondary btn-sm pull-right float-right">ویرایش مطلب</a>
@endif

@section('style')
    <style>
        p {
            overflow-wrap: break-word !important;
        }
    </style>
@endsection

<h1 class='blog_title mb-2 text-center'>{{ $post->title }}</h1>
<h5 class='blog_subtitle mb-2'>{{ $post->subtitle }}</h5>


{{-- <?= $post->image_tag('medium', false, 'd-block mx-auto rounded') ?> --}}
<img src="{{ $post->image_url('medium') }}" alt="{{ $post->title }}" title="{{ $post->title }}" class="d-block mx-auto rounded">
<p class="blog_body_content">
    {!! $post->post_body_output() !!}

    {{-- @if (config('binshopsblog.use_custom_view_files') && $post->use_view_file) --}}
    {{--                                // use a custom blade file for the output of those blog post --}}
    {{--   @include("binshopsblog::partials.use_view_file") --}}
    {{-- @else --}}
    {{--   {!! $post->post_body !!}        // unsafe, echoing the plain html/js --}}
    {{--   {{ $post->post_body }}          // for safe escaping --}}
    {{-- @endif --}}
</p>

{{-- <hr />

<strong>{{ $post->posted_at->diffForHumans() }}</strong> --}}

@includeWhen($post->author, 'binshopsblog::partials.author', ['post' => $post])
@includeWhen($post->categories, 'binshopsblog::partials.categories', ['post' => $post])
