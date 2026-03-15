<?php

namespace App\Http\ViewComposers;

use App\Models\CmsPages;
use App\Models\CmsPageSections;
use Illuminate\Contracts\View\View;

class PropertyCoachViewComposer
{
    public function compose(View $view)
    {
        $sectionData = CmsPageSections::whereCmsPageId(CmsPages::whereName('property_coach')->pluck('id'))
            ->get()
            ->groupBy('section_slug')
            ->transform(function ($item) {
                return $item[0];
            })
            ->toArray();
        $view->with('sectionData', $sectionData);
    }
}
