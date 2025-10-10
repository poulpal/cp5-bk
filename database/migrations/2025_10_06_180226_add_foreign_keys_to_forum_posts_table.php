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
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign(['parent_id'])->references(['id'])->on('forum_posts')->onDelete('CASCADE');
            $table->foreign(['building_id'])->references(['id'])->on('buildings')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign('forum_posts_parent_id_foreign');
            $table->dropForeign('forum_posts_building_id_foreign');
            $table->dropForeign('forum_posts_user_id_foreign');
        });
    }
};
