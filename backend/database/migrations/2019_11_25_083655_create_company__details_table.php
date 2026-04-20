<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name');
            $table->string('slug', 500);
            $table->string('brand_type')->nullable();
            $table->string('bio')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_thumb')->nullable();
            $table->string('location')->nullable();
            $table->string('is_from')->default('backend');
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('company_details');
    }
}
