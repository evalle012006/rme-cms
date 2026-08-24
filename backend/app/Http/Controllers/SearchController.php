<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use App\Models\Properties;
use App\Models\Review;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // pt = property take, rt = review take, bt = blog take

        $q = $request->get('q', null);
        $pt = !is_numeric($request->get('pt')) ? 5 : $request->get('pt');
        $rt = !is_numeric($request->get('rt')) ? 5 : $request->get('rt');
        $bt = !is_numeric($request->get('bt')) ? 5 : $request->get('bt');

        $blogs = [];
        $reviews = [];
        $properties = [];
        $totalBlogs = 0;
        $totalReviews = 0;
        $totalProperties = 0;

        if ($q !== null) {

            $searchTerm = '%' . $q . '%';

            $blogs = Blogs::where('status', 0)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', $searchTerm);
                    $query->orwhere('description', 'like', $searchTerm);
                    $query->orwhere('seo_title', 'like', $searchTerm);
                    $query->orwhere('seo_description', 'like', $searchTerm);
                })

                ->orderBy('id','DESC')
                ->take($bt ?? 5)
                ->get();
            $totalBlogs = Blogs::where('status', 0)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', $searchTerm);
                    $query->orwhere('description', 'like', $searchTerm);
                    $query->orwhere('seo_title', 'like', $searchTerm);
                    $query->orwhere('seo_description', 'like', $searchTerm);
                })
                ->count();

            $reviews = Review::with('reviewTags:id,name', 'ratings', 'property:id,project_name,slug,developer_name',
                'property.company:id,company_name', 'users:id,display_name')
                ->where('status', 1)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', $searchTerm);
                    $query->orwhere('comment', 'like', $searchTerm);
                })
                ->orderBy('id','DESC')
                ->take($rt ?? 5)
                ->get()
                ->transform(function ($item) {
                    $sum = 0;
                    foreach ($item->ratings as $v) {
                        $sum += $v->rate;
                    }
                    $item->avg_rating = roundOffRating($sum / count($item->ratings));
                    return $item;
                });
            $totalReviews = Review::where('status', 1)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', $searchTerm);
                    $query->orwhere('comment', 'like', $searchTerm);
                })
                ->count();

            $properties = Properties::with('company')->withCount('reviews')
                ->where('status', 1)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('developer_name', 'like', $searchTerm);
                    $query->orwhere('brand', 'like', $searchTerm);
                    $query->orwhere('project_name', 'like', $searchTerm);
                    $query->orwhere('description', 'like', $searchTerm);
                })

                ->orderBy('id','DESC')
                ->take($pt ?? 5)
                ->get();

            $totalProperties = Properties::where('status', 1)
                ->where(function ($query) use ($searchTerm) {
                    $query->where('developer_name', 'like', $searchTerm);
                    $query->orwhere('brand', 'like', $searchTerm);
                    $query->orwhere('project_name', 'like', $searchTerm);
                    $query->orwhere('description', 'like', $searchTerm);
                })
                ->count();
        }

        if ($request->ajax()) {
            $section = $request->get('section');
            if ($section === 'properties') {
                return view('partials.search-properties', compact('properties', 'totalProperties'));
            }
            if ($section === 'reviews') {
                return view('partials.search-reviews', compact('reviews', 'totalReviews'));
            }
            if ($section === 'blogs') {
                return view('partials.search-blogs', compact('blogs', 'totalBlogs'));
            }
        }

        return view('search.search-result', compact('q', 'blogs', 'reviews', 'properties', 'totalBlogs', 'totalProperties', 'totalReviews'));
    }
}
