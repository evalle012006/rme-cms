<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Validator;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Storage;
class CategoryController extends BaseController
{
    public function addPropertyCategory(Request $request)
    {


        try {
            $data = $this->validate($request, PropertyCategory::$rules);

            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $data['image'] = $this->saveToS3($file, self::PROPERTYCAT_STORAGE_PATH);
//                $data['feature_image_thumb'] = $this->saveThumbToS3($data['feature_image']);
            }

            $blog = PropertyCategory::create($data);

            return $this->formatSuccessResponse('success', $blog);


        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function updatePropertyCategory(Request $request)
    {
        try {
            $data = $this->validate($request, PropertyCategory::$rules);

            if (!$request->has('image')) {
                $data['image'] = null;
            }
            $propCat = PropertyCategory::find($request->id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->saveToS3($request->file('image'), self::PROPERTYCAT_STORAGE_PATH);

                if (!empty($propCat->image)) {
                    Storage::delete($propCat->image);
                }
            }
            $propCat->update($data);
            return $this->formatSuccessResponse('success', $propCat);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function deletePropertyCategory(Request $request)
    {
        PropertyCategory::where('id', $request->id)->delete();
        return $this->formatSuccessResponse('delete record', 'success');
    }

    public function getDatatableProperty(Request $request)
    {
        return datatables(PropertyCategory::query())->toJson();
    }

    public function getPropertyCategory(Request $request)
    {
        $property_category = PropertyCategory::where('id', $request->id)->first();
        return response()->json(['success' => true, 'data' => $property_category]);
    }

    public function getAllPropertyCategory()
    {
        $property_category = PropertyCategory::get()->toArray();
        return $this->formatSuccessResponse('success', $property_category);
    }
}
