<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('property_id')->nullable();
            $table->foreign('property_id')
                ->on('properties')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->on('users')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                ->on('property_categories')
                ->references('id')
                ->onDelete('cascade');

            $table->integer('parent_id')->nullable();
            $table->boolean('recommendation')->nullable();
            $table->string('title')->nullable();
            $table->longText('comment')->nullable();
            $table->string('slug');
            $table->string('review_image')->nullable();
            $table->string('review_docs')->nullable();
            $table->string('buyer_type')->nullable();
            $table->integer('state')->nullable();
            $table->string('post_code')->nullable();
            $table->string('suburb')->nullable();
            $table->string('year_of_completion')->nullable();
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('reviews');
    }
}
