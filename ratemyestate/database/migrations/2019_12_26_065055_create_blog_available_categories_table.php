<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlogAvailableCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_available_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('blog_id')->nullable();
            $table->foreign('blog_id')
                ->on('blogs')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('blog_category_id')->nullable();
            $table->foreign('blog_category_id')
                ->on('blog_categories')
                ->references('id')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_available_categories');
    }
}
