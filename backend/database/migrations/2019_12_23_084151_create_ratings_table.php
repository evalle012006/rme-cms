<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('review_id');
            $table->foreign('review_id')
                ->on('reviews')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('rating_conf_id');
            $table->foreign('rating_conf_id')
                ->on('rating_configurations')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedInteger('rate')->nullable();
            $table->integer('parent_id')->nullable();
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
        Schema::dropIfExists('ratings');
    }
}
