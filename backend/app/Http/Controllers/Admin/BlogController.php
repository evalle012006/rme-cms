<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\BlogAvailableCategory;
use App\Models\BlogCategory;
use App\Models\Blogs;
use App\Models\User;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends BaseController
{
    public function addBlogCategory(Request $request)
    {
        try {
            $data = $this->validate($request, BlogCategory::$rules);
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $data['image'] = $this->saveToS3($file, BlogCategory::$blog_category_storage_path);
//                $data['feature_image_thumb'] = $this->saveThumbToS3($data['feature_image']);
            }
            $blogCategory = BlogCategory::create($data);
            return $this->formatSuccessResponse('success', $blogCategory);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function addBlog(Request $request)
    {
        try {
            $data = $this->validate($request, Blogs::$rules);
            $data['blog_date'] = Carbon::parse($data['blog_date']);

            if ($request->hasFile('feature_image')) {
                $file = $request->file('feature_image');

                $data['feature_image'] = $this->saveToS3($file, self::FEATURE_STORAGE_PATH);
                $data['feature_thumb_image'] = $this->saveThumbToS3($data['feature_image']);
            }
            $data['created_by'] = $request->created_by;

            $blog = Blogs::create($data);
            $saveBlogCategory = $this->saveBlogCategory($blog->id, $data['category_id']);
            if($saveBlogCategory) {
                return $this->formatSuccessResponse('success', $blog);
            }
            return $this->formatFailureResponse('blog categories not inserted');


        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function updateBlogCategory(Request $request)
    {
        try {
            $data = $this->validate($request, BlogCategory::$rules);

            if (!$request->has('image')) {
                $data['image'] = null;
            }
            $blogCategory = BlogCategory::find($request->id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->saveToS3($request->file('image'), BlogCategory::$blog_category_storage_path);

                if (!empty($blogCategory->image)) {
                    Storage::delete($blogCategory->image);
                }
            }
            $blogCategory->update($data);
            return $this->formatSuccessResponse('success', $blogCategory);
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function updateBlog(Request $request)
    {
        try {
            $data = $this->validate($request, Blogs::$rules);
            $data['blog_date'] = Carbon::parse($data['blog_date']);
            if (!$request->has('feature_image')) {
                $data['feature_image'] = null;
            }
            $blog = Blogs::find($request->id);
            if ($request->hasFile('feature_image')) {
                $data['feature_image'] = $this->saveToS3($request->file('feature_image'), self::FEATURE_STORAGE_PATH);

                if (!empty($blog->feature_image)) {
                    Storage::delete($blog->feature_image);
                    Storage::delete($blog->feature_thumb_image);
                }
            }
            $blog->update($data);
            $saveBlogCategory = $this->saveBlogCategory($request->id, $data['category_id']);
            if($saveBlogCategory) {
                return $this->formatSuccessResponse('success', $blog);
            }
            return $this->formatFailureResponse('blog categories not inserted');
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        }
    }

    public function getBlogCategory($id)
    {
        $blogCategory = BlogCategory::find($id);
        if (!is_null($blogCategory)) {
            return $this->formatSuccessResponse('success', $blogCategory);
        }
        return $this->formatFailureResponse('BlogCategory not found');
    }

    public function getBlog($id)
    {
        $blog = Blogs::with('blogCategories')->find($id);
        if (!is_null($blog)) {
            return $this->formatSuccessResponse('success', $blog);
        }
        return $this->formatFailureResponse('Blog not found');
    }

    public function getDatatableBlogCategory(Request $request)
    {
        return datatables(BlogCategory::query())->toJson();
    }

    public function getDatatableBlog(Request $request)
    {
        return datatables(Blogs::with('blogCategories'))->toJson();
    }

    public function getAllBlogCategory()
    {
        $blog_category = BlogCategory::get()->toArray();
        return $this->formatSuccessResponse('success', $blog_category);
    }

    public function saveBlogCategory($blog_id, $categories)
    {
        BlogAvailableCategory::where('blog_id', $blog_id)->delete();

        $valueToSave = [];
//        foreach ($categories as $category)
//        {
        array_push($valueToSave, [
            'blog_id' => $blog_id,
            'blog_category_id' => $categories
        ]);
//        }
        if (BlogAvailableCategory::insert($valueToSave)) {
            return true;
        }
        return false;
    }

    public function deleteBlog(Request $request)
    {
        Blogs::where('id', $request->id)->delete();
        return $this->formatSuccessResponse('delete record', 'success');
    }




}
