<div style='max-width:500px;margin:20px auto;' class='search-form-outer'>
    <form method='get' action='{{route("binshopsblog.search")}}' class='text-center'>
        <h4>جستجو در وبلاگ:</h4>
        <input type='text' name='s' placeholder='جستجو...' class='form-control' value='{{\Request::get("s")}}'>
        <div class="w-100 d-flex justify-content-center mt-2">
            <input type='submit' value='جستجو' class='btn btn-outline-dark w-100' style="max-width: 200px">
        </div>
    </form>
</div>