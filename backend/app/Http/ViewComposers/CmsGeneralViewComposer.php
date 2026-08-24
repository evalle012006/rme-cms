<?php

namespace App\Http\ViewComposers;

use App\Models\CmsPages;
use App\Models\CmsPageSections;
use Illuminate\Contracts\View\View;

class CmsGeneralViewComposer
{
    public function compose(View $view)
    {
        $viewToPage = [
            'welcome' => 'home',
            'about' => 'about',
            'property-guide' => 'property_guide',
            'property-coach' => 'property_coach',
            'contact' => 'contact',
            'property-listing' => 'reviews_listing',
            'review-guideline' => 'review_guidelines',
            'term-of-use' => 'terms_of_use',
            'privacy-policy' => 'privacy_policy'
        ];
        $sectionData = CmsPageSections::whereIn('cms_page_id', CmsPages::where('name', $viewToPage[$view->name()])
            ->select('id'))
            ->get()
            ->groupBy('section_slug')
            ->transform(function ($item) {
                return $item[0];
            })
            ->toArray();
        $view->with('sectionData', $sectionData);
    }
}
