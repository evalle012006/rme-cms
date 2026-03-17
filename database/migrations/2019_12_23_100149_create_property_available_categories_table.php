<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyAvailableCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_available_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id');
            $table->foreign('property_id')
                ->on('properties')
                ->references('id')
                ->onDelete('cascade');

            $table->unsignedBigInteger('property_category_id');
            $table->foreign('property_category_id')
                ->on('property_categories')
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
        Schema::dropIfExists('property_available_categories');
    }
}
