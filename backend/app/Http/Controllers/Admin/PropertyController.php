<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Imports\PropertyImport;
use App\Models\Properties;
use App\Models\PropertyAvailableCategory;
use App\Models\PropertyGallery;
use App\Models\Review;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class PropertyController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function getDatatablePropertyApprove(Request $request)
    {
        return datatables(
            Properties::with('propertyAllowedCategory')->where('status', 1)
        )->toJson();
    }

    public function getDatatablePropertyPending(Request $request)
    {
        return datatables(
            Properties::with('propertyAllowedCategory')->where('status', 0)
        )->toJson();
    }

    public function addProperty(Request $request)
    {
        try {
            $data = $this->validate($request, Properties::$rules);
            if (!$data['developer_name']) {
                $data['developer_name'] = 0;
            }
            $data['created_by'] = Auth::id();
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileLogo = $this->uploadPropertyLogo($file);
                $data = array_merge($data, $fileLogo);
            }
            $property = Properties::create($data);
            $property_cat = $this->savePropertyCategories($property->id, json_decode($data['property_categories']));
            if ($property_cat) {
                return $this->formatSuccessResponse('success', $property, 201);
            }
            return $this->formatFailureResponse('property category not saved');
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('failure', $e->errors());
        }
    }

    public function updateProperty(Request $request, $id)
    {
        try {
            $data = $this->validate($request, Properties::$rules);

            $property = Properties::find($id);
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileLogo = $this->uploadPropertyLogo($file);
                $data = array_merge($data, $fileLogo);
            }
            $property->update($data);
            $property_cat = $this->savePropertyCategories($id, json_decode($data['property_categories']));

            if ($property_cat) {
                return $this->formatSuccessResponse('success', $property, 201);
            }
            return $this->formatFailureResponse('property category not saved');
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('failure', $e->errors());
        }
    }

    public function uploadPropertyLogo($file)
    {
        $data['logo'] = $this->saveToS3($file, self::PROPERTY_LOGO_STORAGE_PATH);
        return $data;
    }

    public function deleteProperty(Request $request)
    {
        Properties::where('id', $request->id)->delete();
        Review::where('property_id', $request->id)->delete();
        return $this->formatSuccessResponse('record deleted', 'success');
    }

    public function savePropertyCategories($property_id, $categories)
    {
        PropertyAvailableCategory::where('property_id', $property_id)->delete();
        $valueToSave = [];
        foreach ($categories as $cat) {
            array_push($valueToSave, [
                'property_id' => $property_id,
                'property_category_id' => $cat
            ]);
        }
        if (PropertyAvailableCategory::insert($valueToSave)) {
            return true;
        }
        return false;
    }

    public function getProperty($id)
    {
        $property = Properties::with(['propertyAllowedCategory', 'company', 'gallery'])->find($id);

        if (!is_null($property)) {
            return $this->formatSuccessResponse('success', $property);
        }
        return $this->formatFailureResponse('property not found');
    }

    public function importProperty(Request $request)
    {
        $data = null;

        if ($request->hasFile('import_file')) {
            try {
                $data = Excel::import(new PropertyImport, $request->file('import_file'));
            } catch (\Exception $e) {
                return $this->apiFormatFailureResponse(
                    'Issue with csv format. please check',
                    ['Issue with csv format. please check']
                );
            }

            if (!empty($data)) {
                return $this->formatSuccessResponse('import successfully', $data);
            }
        }
        return $this->apiFormatFailureResponse('issue', $data);
    }

    public function savePropertyGallery($propery_id, $gallaries)
    {
        $valueToIns = [];
        foreach ($gallaries as $gal) {
            array_push($valueToIns, [
                'property_id' => $propery_id,
                'name' => $this->saveToS3($gal, self::PROPERTY_STORAGE_PATH)
            ]);
        }
        PropertyGallery::insert($valueToIns);
        return true;
    }

    public function removePropertyGallery($remove_ids)
    {
        foreach ($remove_ids as $gal_id) {
            $property_gal = PropertyGallery::find($gal_id);
            Storage::delete($property_gal->name);
            PropertyGallery::where('id', $gal_id)->forceDelete();
        }
        return true;
    }

    public function addPropertyGallery(Request $request)
    {
        if (isset($request->image_to_add)) {
            $this->savePropertyGallery($request->id, $request->image_to_add);
        }
        if (isset($request->image_to_remove)) {
            $this->removePropertyGallery($request->image_to_remove);
        }
    }

    public function getPropertyAll()
    {
        $properties = Properties::where('status', 1)->orWhere('is_from', 'frontend')->get()->toArray();
        return $this->formatSuccessResponse('success', $properties);
    }

    public function approveProperty(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], ['id' => 'required|exists:properties,id']);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Error in inputs', 400);
        } else {
            $updated = Properties::find($request->id)->update(['status' => 1]);
            if ($updated) {
                return $this->formatSuccessResponse('success', []);
            }
            return $this->formatFailureResponse('Error while processing', 500);
        }
    }
}
