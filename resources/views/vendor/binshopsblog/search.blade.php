@extends('layouts.app', ['title' => $title])
@section('content')

    <div class='row pt-4 px-4'>
        <div class='col-sm-12'>
            <div class="row">
                <div class="col-md-9 m-auto">
                    <h2 class="mb-3">نتایج جستجو برای: {{ $query }}</h2>
                    <div class="row">
                        @php $search_count = 0;@endphp
                        @forelse($search_results as $result)
                            @if (isset($result->indexable))
                                @php $search_count += $search_count + 1; @endphp
                                <?php $post = $result->indexable; ?>
                                @if ($post && is_a($post, \BinshopsBlog\Models\BinshopsBlogPost::class))
                                    {{-- <h2>#{{$search_count}}</h2> --}}
                                    @include('binshopsblog::partials.index_loop')
                                @else
                                    {{-- <div class='alert alert-danger'></div> --}}
                                @endif
                            @endif
                        @empty
                            <div class='alert alert-danger'>موردی یافت نشد!</div>
                        @endforelse
                    </div>
                </div>
                {{-- <div class="col-md-3">
                    <h6>دسته بندی ها</h6>
                    @forelse($categories as $category)
                        <a href="{{$category->url()}}">
                            <h6>{{$category->category_name}}</h6>
                        </a>
                    @empty
                        <a href="#">
                            <h6>No Categories</h6>
                        </a>
                    @endforelse
                </div> --}}
            </div>

            @if (config('binshopsblog.search.search_enabled'))
                @include('binshopsblog::sitewide.search_form')
            @endif

        </div>
    </div>


@endsection
