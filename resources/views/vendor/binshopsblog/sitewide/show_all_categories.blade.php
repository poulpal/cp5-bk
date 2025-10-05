<h5>دسته بندی ها</h5>
<ul class="nav">
    @foreach(\BinshopsBlog\Models\BinshopsBlogCategory::orderBy("category_name")->limit(200)->get() as $category)
        <li class="nav-item">
            <a class='nav-link' href='{{ route('binshopsblog.view_category_new', ['categorySlug' => $category->slug]) }}'>{{$category->category_name}}</a>
        </li>
    @endforeach
</ul>