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
        Schema::create('binshops_blog_post_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('binshops_blog_post_id')->index();
            $table->unsignedInteger('binshops_blog_category_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('binshops_blog_post_categories');
    }
};
