<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\RatingConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RatingController extends BaseController
{
    public function addRating(Request $request)
    {
        $rules = [
            'name'        => 'unique:rating_configurations|required',
            'rating_icon' => 'required|file|image',
            'description' => 'required',
        ];

        try {
            $data = $this->validate($request, $rules);
            if ($request->hasFile('rating_icon')) {
                $data['rating_icon'] = $this->saveToS3($request->file('rating_icon'), self::RATINGCONF_STORAGE_PATH);
            }
            $rating_conf = RatingConfiguration::create($data);
            return $this->formatSuccessResponse('success', $rating_conf);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        } catch (\Exception $e) {
            return $this->formatFailureResponse('Error saving rating', 500);
        }
    }

    public function updateRating(Request $request)
    {
        $rules = [
            'name'        => 'required|unique:rating_configurations,name,' . $request->id,
            'rating_icon' => 'sometimes|file|image',
            'description' => 'required',
        ];

        try {
            $data = $this->validate($request, $rules);
            $rating_conf = RatingConfiguration::find($request->id);

            if ($request->hasFile('rating_icon')) {
                if (!empty($rating_conf->rating_icon)) {
                    Storage::delete($rating_conf->rating_icon);
                }
                $data['rating_icon'] = $this->saveToS3($request->file('rating_icon'), self::RATINGCONF_STORAGE_PATH);
            } else {
                unset($data['rating_icon']);
            }

            $rating_conf->update($data);
            return $this->formatSuccessResponse('success', $rating_conf);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        } catch (\Exception $e) {
            return $this->formatFailureResponse('Error updating rating', 500);
        }
    }

    public function deleteRating(Request $request)
    {
        $config = RatingConfiguration::find($request->id);

        if (!$config) {
            return $this->formatFailureResponse('Rating category not found', 404);
        }

        if ($config->ratings()->exists()) {
            return $this->formatFailureResponse('Cannot delete a rating category with existing reviews attached.', 422);
        }

        $config->delete();
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
