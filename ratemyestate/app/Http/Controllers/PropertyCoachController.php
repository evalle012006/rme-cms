<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Blogs;
use Illuminate\Http\Request;

class PropertyCoachController extends Controller
{
    public function propertyCoachPage()
    {
        $recent_blogs = Blogs::with('blogCategories')->where('status', 0)->latest()->take(4)->get()->toArray();

        $categories = BlogCategory::latest()->take(4)->get()->toArray();

        $recent_finance = Blogs::with('blogCategories')->whereHas('blogCategories', function ($q) {
            $q->where('blog_category_id', 1);
        })->where(['status' => 0])->latest()->take(5)->get()->toArray();

        $recent_buying = Blogs::with('blogCategories')->whereHas('blogCategories', function ($q) {
            $q->where('blog_category_id', 2);
        })->where(['status' => 0])->orderBy('id', 'asc')->latest()->take(4)->get()->toArray();

        $recent_investment = Blogs::with('blogCategories')->whereHas('blogCategories', function ($q) {
            $q->where('blog_category_id', 3);
        })->where(['status' => 0])->latest()->take(2)->get()->toArray();

        $recent_building = Blogs::with('blogCategories')->whereHas('blogCategories', function ($q) {
            $q->where('blog_category_id', 4);
        })->where(['status' => 0])->latest()->take(3)->get()->toArray();


        return view('property-coach', compact('recent_blogs',
            'categories', 'recent_finance',
            'recent_building', 'recent_investment',
            'recent_buying'
        ));
    }

    public function blogDetails($slug)
    {
        $blog_detail = Blogs::where('slug', $slug)->first();
        if ($blog_detail) {
            $recent_blogs = Blogs::with('blogCategories')->where('status', 0)->latest()->take(4)->get()->toArray();
            return view('blog-detail', compact('blog_detail', 'recent_blogs'));
        }
        return redirect('/');
    }

    public function allBlogPosts($slug = '')
    {

        $blog_posts = Blogs::where('status', 0);
        if (!empty($slug)) {
            $blog_posts = $blog_posts->whereHas('blogCategories',
                function ($query) use ($slug) {
                    $query->where('slug', $slug);
                }
            );
        }
        $blog_posts = $blog_posts->paginate(6);
        return view('blog-posts', compact('blog_posts'));
    }
}
