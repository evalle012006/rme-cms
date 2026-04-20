<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsPageSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_page_sections', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('cms_page_id');
            $table->foreign('cms_page_id')
                ->on('cms_pages')
                ->references('id')
                ->onDelete('cascade');

            $table->text('section_name');
            $table->string('section_slug');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->text('body')->nullable();
            $table->text('other_params')->nullable(); // variable in json
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
        Schema::dropIfExists('cms_page_sections');
    }
}
