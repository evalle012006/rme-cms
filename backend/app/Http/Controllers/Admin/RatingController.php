<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\RatingConfiguration;
use Illuminate\Http\Request;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Storage;

class RatingController extends BaseController
{
    public function addRating(Request $request)
    {
        $rules = [
            'name' => 'unique:rating_configurations|required',
            'rating_icon' => 'required',
            'description' => 'required'
        ];

        try {
            $data = $this->validate($request, $rules);
            if (isset($data['rating_icon'])) {
                $data['rating_icon'] = $this->saveToS3($data['rating_icon'], self::RATINGCONF_STORAGE_PATH);
            }
            $rating_conf = RatingConfiguration::create($data);
            return $this->formatSuccessResponse('success', $rating_conf);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function updateRating(Request $request)
    {
        $rules = [
            'name' => 'required|unique:rating_configurations,name,' . $request->id,
            'rating_icon' => 'required',
            'description' => 'required'
        ];
        try {
            $data = $this->validate($request, $rules);
            $rating_conf = RatingConfiguration::find($request->id);
            if (isset($data['rating_icon']) && is_file($data['rating_icon'])) {
                $data['rating_icon'] = $this->saveToS3($data['rating_icon'], self::RATINGCONF_STORAGE_PATH);
                if (!empty($rating_conf->rating_icon)) {
                    Storage::delete($rating_conf->rating_icon);
                }
            }

            $rating_conf->update($data);
            return $this->formatSuccessResponse('success', $rating_conf);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function deleteRating(Request $request)
    {
        RatingConfiguration::where('id', $request->id)->delete();
        return $this->formatSuccessResponse('delete record', 'success');
    }

    public function getDatatableRating(Request $request)
    {
        return datatables(RatingConfiguration::query())->toJson();
    }

    public function getRating(Request $request)
    {
        $rating = RatingConfiguration::where('id', $request->id)->first();
        return response()->json(['success' => true, 'data' => $rating]);
    }

    public function getAllRatingConfiguration()
    {
        $data = RatingConfiguration::get()->toArray();
        return $this->formatSuccessResponse('success', $data);
    }
}
