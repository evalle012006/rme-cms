<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Tags;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class TagsController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['auth:api'])->except(['addTag']);
    }

    public function getDatatableTags(Request $request)
    {
        return datatables(Tags::query())->toJson();
    }

    public function addTag(Request $request)
    {
        try {
            $data = $this->validate($request, Tags::$rules);
            $tags = Tags::create($data);
            return $this->formatSuccessResponse('success', $tags);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function updateTag(Request $request)
    {
        try {
            $data = $this->validate($request, Tags::$rulesUpdate);
            $tags = Tags::find($request->id);

            $tags->update($data);
            return $this->formatSuccessResponse('success', $tags);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function deleteTag(Request $request)
    {
        Tags::where('id', $request->id)->delete();
        return $this->formatSuccessResponse('delete record', 'success');
    }

    public function getTag($id)
    {
        $tags = Tags::find($id);
        if (!is_null($tags)) {
            return $this->formatSuccessResponse('success', $tags);
        }
        return $this->formatFailureResponse('tag not found');
    }
}
