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
        Schema::create('binshops_blog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category_name')->nullable();
            $table->string('slug')->unique();
            $table->mediumText('category_description')->nullable();
            $table->unsignedInteger('created_by')->nullable()->index()->comment('user id');
            $table->timestamps();
            $table->integer('parent_id')->nullable()->default(0);
            $table->integer('lft')->nullable();
            $table->integer('rgt')->nullable();
            $table->integer('depth')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('binshops_blog_categories');
    }
};
