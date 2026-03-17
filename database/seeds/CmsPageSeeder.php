<?php

use Illuminate\Database\Seeder;
use App\Models\CmsPages;
use App\Models\CmsPageSections;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cmsPages = [
            ['name' => 'home'],
            ['name' => 'about'],
            ['name' => 'property_guide'],
            ['name' => 'property_coach'],
            ['name' => 'contact'],
            ['name' => 'review_guidelines'],
            ['name' => 'terms_of_use'],
            ['name' => 'privacy_policy']
        ];

        $home = ['banner', 'review_ratio', 'why_write_review', 'recent_reviews', 'our_blogs'];
        $aboutUs = ['banner', 'get_to_know', 'our_mission', 'our_platform', 'recent_reviews'];

        // these arrays contains name on 0 and slug on 1
        $reviewGuidelines = [
            ['Banner', 'banner'],
            ['Property, Project & Builders reviews on ratemyestate', 'reviews_on_rme'],
            ['Our community guidelines', 'guidelines'],
            ['Review dos and don’ts', 'reviews_dos'],
            ['How does moderation work?', 'moderation_works'],
            ['I wrote a review but I can\'t see it on the website - why not ?', 'wrote_a_review']
        ];

        $termsOfUse = ['banner', 'general', 'copyright', 'disclaimer'];
        $privacyPolicy = [
            ['Banner', 'banner'],
            ['Our commitment to maintaining your privacy', 'your_privacy'],
            ['Definitions', 'definitions'],
            ['Collecting other information on Ratemyestate visitors', 'collecting_info']
        ];


        $cmsPageSections = array_merge(
        // for home page
            array_map(function ($item) {
                return ['cms_page_id' => 1, 'section_name' => makeHeading($item), 'section_slug' => $item];
            }, $home),
            // for about us page
            array_map(function ($item) {
                return ['cms_page_id' => 2, 'section_name' => makeHeading($item), 'section_slug' => $item];
            }, $aboutUs),
            // for property guide
            [['cms_page_id' => 3, 'section_name' => 'Banner', 'section_slug' => 'banner']],
            // for  property coach
            [['cms_page_id' => 4, 'section_name' => 'Banner', 'section_slug' => 'banner']],
            // for contact
            [['cms_page_id' => 5, 'section_name' => 'Banner', 'section_slug' => 'banner']],
            // for review guidelines
            array_map(function ($item) {
                return ['cms_page_id' => 6, 'section_name' => $item[0], 'section_slug' => $item[1]];
            }, $reviewGuidelines),
            // for terms of use
            array_map(function ($item) {
                return ['cms_page_id' => 7, 'section_name' => makeHeading($item), 'section_slug' => $item];
            }, $termsOfUse),
            // for privacy policy
            array_map(function ($item) {
                return ['cms_page_id' => 8, 'section_name' => $item[0], 'section_slug' => $item[1]];
            }, $privacyPolicy)
        );

        CmsPages::insert($cmsPages);
        CmsPageSections::insert($cmsPageSections);
    }
}
