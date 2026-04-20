<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PropertyCategory;
use App\Models\PropertyAvailableRating;
use Illuminate\Http\Request;


class PropertyRateController extends BaseController
{
    public function getPropertyWithAllowedRatings()
    {
        $data = PropertyCategory::with(['propertyAllowedRating'])->get()->toArray();
        foreach ($data as $key => $singleCat) {
            usort($singleCat['property_allowed_rating'], function ($a, $b) {
                return $a['pivot']['order'] > $b['pivot']['order'] ? 1 : -1;
            });
            $data[$key] = $singleCat;
        }
        return $this->formatSuccessResponse('success', $data);
    }

    public function setPropertyAllowedRatings(Request $request)
    {
        $propertyRatings = $request->all();

        PropertyAvailableRating::where('property_category_id', $propertyRatings['property_id'])->delete();

        $insertArr = [];
        foreach ($propertyRatings['rating_ids'] as $key => $rating_ids) {
            array_push($insertArr, [
                'property_category_id' => $propertyRatings['property_id'],
                'rating_conf_id' => $rating_ids,
                'order' => $key + 1
            ]);
        }


        PropertyAvailableRating::insert($insertArr);
        return $this->jSonFormatSuccessResponse('success', 201);

    }


}
