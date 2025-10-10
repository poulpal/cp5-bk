<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('binshops_blog_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('slug')->unique();
            $table->string('title')->nullable()->default('New blog post');
            $table->string('subtitle')->nullable()->default('');
            $table->text('meta_desc')->nullable();
            $table->mediumText('post_body')->nullable();
            $table->string('use_view_file')->nullable()->comment('should refer to a blade file in /views/');
            $table->dateTime('posted_at')->nullable()->index()->comment('Public posted at time, if this is in future then it wont appear yet');
            $table->boolean('is_published')->default(true);
            $table->string('image_large')->nullable();
            $table->string('image_medium')->nullable();
            $table->string('image_thumbnail')->nullable();
            $table->timestamps();
            $table->text('short_description')->nullable();
            $table->string('seo_title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('binshops_blog_posts');
    }
};
