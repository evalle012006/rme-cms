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
        // Each page maps to its sections as [section_name, section_slug] pairs.
        // Sections are linked to the page's real id (looked up by name) rather
        // than a hard-coded id, so seeding is safe regardless of existing rows
        // or auto-increment state, and can be re-run without duplicating data.
        $pages = [
            'home' => $this->headings(['banner', 'review_ratio', 'why_write_review', 'recent_reviews', 'our_blogs']),
            'about' => $this->headings(['banner', 'get_to_know', 'our_mission', 'our_platform', 'recent_reviews']),
            'property_guide' => [['Banner', 'banner']],
            'property_coach' => [['Banner', 'banner']],
            'contact' => [['Banner', 'banner']],
            'review_guidelines' => [
                ['Banner', 'banner'],
                ['Property, Project & Builders reviews on ratemyestate', 'reviews_on_rme'],
                ['Our community guidelines', 'guidelines'],
                ['Review dos and don’ts', 'reviews_dos'],
                ['How does moderation work?', 'moderation_works'],
                ['I wrote a review but I can\'t see it on the website - why not ?', 'wrote_a_review'],
            ],
            'terms_of_use' => $this->headings(['banner', 'general', 'copyright', 'disclaimer']),
            'privacy_policy' => [
                ['Banner', 'banner'],
                ['Our commitment to maintaining your privacy', 'your_privacy'],
                ['Definitions', 'definitions'],
                ['Collecting other information on Ratemyestate visitors', 'collecting_info'],
            ],
            'reviews_listing' => [['Banner', 'banner']],
        ];

        foreach ($pages as $name => $sections) {
            $page = CmsPages::firstOrCreate(['name' => $name]);

            foreach ($sections as [$sectionName, $slug]) {
                CmsPageSections::firstOrCreate(
                    ['cms_page_id' => $page->id, 'section_slug' => $slug],
                    ['section_name' => $sectionName]
                );
            }
        }
    }

    /**
     * Build [section_name, section_slug] pairs from a list of slugs, deriving
     * the display name from the slug (matches the original seeder behaviour).
     *
     * @param array $slugs
     * @return array
     */
    private function headings(array $slugs)
    {
        return array_map(function ($slug) {
            return [makeHeading($slug), $slug];
        }, $slugs);
    }
}
