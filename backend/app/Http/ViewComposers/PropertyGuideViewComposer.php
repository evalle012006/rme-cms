<?php

namespace App\Http\ViewComposers;

use App\Models\CmsPages;
use App\Models\CmsPageSections;
use Illuminate\Contracts\View\View;

class PropertyGuideViewComposer
{
    public function compose(View $view)
    {
        $sectionData = CmsPageSections::whereIn('cms_page_id', CmsPages::where('name', 'property_guide')->select('id'))
            ->get()
            ->groupBy('section_slug')
            ->transform(function ($item) {
                return $item[0];
            })
            ->toArray();
        $view->with('sectionData', $sectionData);
    }
}
