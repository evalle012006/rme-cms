<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('developer_name');
            $table->string('brand')->nullable();
            $table->string('project_name')->nullable();
            $table->string('slug', 500);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->longText('address')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('post_code')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('year_of_completion')->nullable();
            $table->string('buyer_type')->nullable();
            $table->string('website_url')->nullable();
            $table->longText('description')->nullable();
            $table->string('is_from')->default('backend')->nullable();
            $table->boolean('status')->default(0)->nullable();
            $table->integer('created_by')->nullable();


            $table->unsignedBigInteger('owner')->nullable();
            $table->foreign('owner')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
