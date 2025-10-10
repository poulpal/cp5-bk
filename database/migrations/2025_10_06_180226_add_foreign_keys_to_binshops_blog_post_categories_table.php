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
        Schema::table('binshops_blog_post_categories', function (Blueprint $table) {
            $table->foreign(['binshops_blog_post_id'])->references(['id'])->on('binshops_blog_posts')->onDelete('CASCADE');
            $table->foreign(['binshops_blog_category_id'])->references(['id'])->on('binshops_blog_categories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('binshops_blog_post_categories', function (Blueprint $table) {
            $table->dropForeign('binshops_blog_post_categories_binshops_blog_post_id_foreign');
            $table->dropForeign('binshops_blog_post_categories_binshops_blog_category_id_foreign');
        });
    }
};
