<?php

namespace App\Http\Controllers;

class PropertyGuideController extends Controller
{
    public function propertyGuidePage()
    {
        $mapData = app('App\Http\Controllers\HomeController')->getMapData();
        $groupedReviews = $mapData['grouped_reviews'];
        $suburbsLocations = $mapData['suburbs'];
        return view('property-guide', compact('groupedReviews', 'suburbsLocations'));
    }

}
