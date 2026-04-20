<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAvailableRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_available_ratings', function (Blueprint $table) {
            $table->unsignedBigInteger('property_category_id');
            $table->unsignedBigInteger('rating_conf_id');

            $table->foreign('property_category_id')
                ->references('id')
                ->on('property_categories')
                ->onDelete('cascade');

            $table->foreign('rating_conf_id')
                ->references('id')
                ->on('rating_configurations')
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
        Schema::dropIfExists('property_available_ratings');
    }
}
