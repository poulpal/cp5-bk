<?php

namespace App\Jobs\Blog;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Intervention\Image\Facades\Image;

class ProcessUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $image_filename,
        public $BinshopsBlogPost,
        public string $source
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = public_path(config('binshopsblog.blog_upload_dir') . "/" . $this->image_filename);
        $image = Image::make($path);
        // save webp version
        $new_filename = dirname($path) . "/" . pathinfo($path, PATHINFO_FILENAME) . ".webp";
        $image->save($new_filename, 80, 'webp');
        if ($this->BinshopsBlogPost) {
            if ($this->BinshopsBlogPost->image_large == $this->image_filename) {
                $this->BinshopsBlogPost->image_large = pathinfo($this->image_filename, PATHINFO_FILENAME) . ".webp";
            }
            if ($this->BinshopsBlogPost->image_medium == $this->image_filename) {
                $this->BinshopsBlogPost->image_medium = pathinfo($this->image_filename, PATHINFO_FILENAME) . ".webp";
            }
            if ($this->BinshopsBlogPost->image_thumbnail == $this->image_filename) {
                $this->BinshopsBlogPost->image_thumbnail = pathinfo($this->image_filename, PATHINFO_FILENAME) . ".webp";
            }
            $this->BinshopsBlogPost->save();
        }
    }
}
