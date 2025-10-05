<h5>مطالب اخیر</h5>

<div class="container">
    <div class="row">
        @foreach(\BinshopsBlog\Models\BinshopsBlogPost::orderBy("posted_at","desc")->limit(3)->get() as $post)
            @include("binshopsblog::partials.index_loop_sm")
        @endforeach
    </div>
</div>