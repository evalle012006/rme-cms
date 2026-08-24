<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReviewsListingCmsPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $existing = DB::table('cms_pages')->where('name', 'reviews_listing')->first();
        if ($existing) {
            $pageId = $existing->id;
        } else {
            $pageId = DB::table('cms_pages')->insertGetId([
                'name' => 'reviews_listing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $sectionExists = DB::table('cms_page_sections')
            ->where('cms_page_id', $pageId)
            ->where('section_slug', 'banner')
            ->exists();

        if (!$sectionExists) {
            DB::table('cms_page_sections')->insert([
                'cms_page_id' => $pageId,
                'section_name' => 'Banner',
                'section_slug' => 'banner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $page = DB::table('cms_pages')->where('name', 'reviews_listing')->first();
        if (!$page) {
            return;
        }

        DB::table('cms_page_sections')->where('cms_page_id', $page->id)->delete();
        DB::table('cms_pages')->where('id', $page->id)->delete();
    }
}
