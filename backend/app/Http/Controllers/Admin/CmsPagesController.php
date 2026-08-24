<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\CmsPages;
use App\Models\CmsPageSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CmsPagesController extends BaseController
{
    public function getCmsPageData(Request $request)
    {
        $page = $request->page;
        try {
            $pageData = CmsPageSections::whereIn('cms_page_id', CmsPages::where('name', $page)->select('id'))
                ->get()
                ->groupBy('section_slug')
                ->transform(function ($item) {
                    return $item[0];
                })
                ->toArray();
            return $this->formatSuccessResponse('success', $pageData);
        } catch (\Exception $e) {
            return $this->formatFailureResponse('Error while processing', 500);
        }
    }

    public function savePageData(Request $request)
    {
        return $request->all();
    }

    public function saveCmsPageData(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], ['id' => 'required|exists:cms_page_sections']);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Error in inputs', 500);
        } else {
            DB::beginTransaction();

            try {
                $data = $request->all();
                if ($request->hasFile('image')) {
                    $file = $data['image'];
                    $data['image'] = $this->saveToS3($file, self::CMS_PAGE_PATH);
                } else {
                    if (!$request->has('image')) {
                        unset($data['image']);
                    }
                }

                $updatable = [
                    'id',
                    'section_name',
                    'title',
                    'subtitle',
                    'image',
                    'body',
                    'other_params'
                ];

                foreach ($data as $key => $item) {
                    if (array_search($key, $updatable) === false) {
                        unset($data[$key]);
                    }
                }

                $updated = CmsPageSections::where('id', $data['id'])->update($data);
                if ($updated) {
                    DB::commit();
                    return $this->formatSuccessResponse('success', []);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->formatFailureResponse('Error while processing', 500);
            }
        }
    }
}
