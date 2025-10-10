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
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index('forum_posts_building_id_foreign');
            $table->unsignedBigInteger('user_id')->index('forum_posts_user_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('forum_posts_parent_id_foreign');
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->integer('likes')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forum_posts');
    }
};
