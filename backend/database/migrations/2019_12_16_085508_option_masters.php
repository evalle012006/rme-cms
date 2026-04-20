<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptionMasters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('option_masters', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('select_id');
            $table->foreign('select_id')
                ->references('id')
                ->on('select_masters')
                ->onDelete('cascade');

            $table->string('key_text');
            $table->string('value_text');

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
        Schema::dropIfExists('option_masters');
    }
}
