<div class="col-md-3">
    <div class="blog-item mb-1">

        <div class='text-center blog-image'>
            <?= $post->image_tag('medium', true, 'w-100 mb-2 rounded') ?>
        </div>
        <div class="blog-inner-item">
            <h5 class=''><a href='{{ $post->url() }}' class="text-decoration-none">{{ $post->title }}</a></h5>
            <h5 class=''>{{ $post->subtitle }}</h5>

            @if (config('binshopsblog.show_full_text_at_list'))
                <p>{!! $post->post_body_output() !!}</p>
            @else
                <p class="text-wrap text-muted">{!! mb_strimwidth($post->post_body_output(), 0, 500, '...') !!}</p>
            @endif


            <div class="d-flex flex-row justify-content-between">
                <div class="post-details-bottom">
                    <img src="{{ asset('img/icons/calendar.svg') }}" alt="تاریخ ایجاد پست" title="{{ Morilog\Jalali\Jalalian::forge($post->posted_at)->format('Y/m/d') }}">
                   <small class="text-muted"> {{ Morilog\Jalali\Jalalian::forge($post->posted_at)->ago() }} </small>
                </div>
                <div class='text-center'>
                    <a href="{{ $post->url() }}" class="btn btn-outline-primary btn-sm"
                        style="border: 1px solid #133D70;border-radius: 10px; width: 101px;height: 32px;">ادامه
                        مطلب</a>
                </div>
            </div>

        </div>
    </div>

</div>
