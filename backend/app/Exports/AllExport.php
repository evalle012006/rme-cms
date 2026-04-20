<?php

namespace App\Exports;

use App\Models\RatingConfiguration;
use App\Models\Review;
use Illuminate\Contracts\View\View;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class AllExport implements FromView
{
    public function view(): View
    {
        $propertyCategories = RatingConfiguration::pluck('name')->toArray();

        $reviews = Review::select()->with(['property' => function ($qry) {
            $qry->select('id', 'developer_name', 'project_name', 'state', 'city', 'post_code','suburb')
                ->with(['company' => function ($q) {
                    $q->select('id', 'company_name');
                }]);
        }, 'users' => function ($qry) {
            $qry->select('id', 'display_name');
        }, 'category' => function ($qry) {
            $qry->select('id', 'name');
        }, 'stateName', 'buyerTypeRow', 'ratings', 'reviewTags'])
            ->whereHas('property', function ($qry) {
                $qry->where('status', 1);
            })
            ->where('status', 1)
            ->get()
            ->transform(function ($item) use ($propertyCategories) {
                $totalRating = 0;
                $reviewTags = [];
                $ratingsByTitle = [];
                foreach ($propertyCategories as $propertyCategory) {
                    $ratingsByTitle[$propertyCategory] = 0;
                }
                foreach ($item->ratings as $rating) {
                    $ratingsByTitle[$rating->reviewQuestion->name] = $rating->rate;
                    $totalRating += $rating->rate;
                }
                foreach ($item->reviewTags as $tag) {
                    $reviewTags[] = $tag->name;
                }
                $avgOfRating = round($totalRating / count($item->ratings));
                $item->tags = implode(', ', $reviewTags);
                $item->overall_rating = $avgOfRating;
                $item->rating_by_title = $ratingsByTitle;
                return $item;
            });
        return view('exports.company', [
            'reviews' => $reviews,
            'categories' => $propertyCategories
        ]);
    }
}
