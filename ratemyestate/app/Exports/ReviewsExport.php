<?php

namespace App\Exports;

use App\Models\Properties;
use App\Models\RatingConfiguration;
use App\Models\Review;
use Illuminate\Contracts\View\View;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class ReviewsExport implements FromView
{
    protected $id, $propertyName;

    public function __construct($id, $propertyName)
    {
        $this->id = $id;
        $this->propertyName = $propertyName;
    }

    public function view(): View
    {
        $propertyCategories = RatingConfiguration::pluck('name');

        $reviews = Review::where('property_id', $this->id)
            ->where('status', 1)
            ->with(['reviewTags', 'ratings', 'buyerTypeRow', 'users' => function ($qry) {
                $qry->select('id', 'display_name');
            }])
            ->get()
            ->transform(function ($item) use ($propertyCategories) {
                $totalRatings = [];
                $ratingWithTitle = [];
                $tags = [];
                foreach ($item->ratings->toArray() as $rating) {
                    $totalRatings[] = $rating['rate'];
                    $ratingWithTitle[$rating['review_question']['name']] = $rating['rate'];
                }
                foreach ($propertyCategories as $category) {
                    if (!isset($ratingWithTitle[$category])) {
                        $ratingWithTitle[$category] = '-';
                    }
                }
                $item->avg_rating = round(array_sum($totalRatings) / count($totalRatings));
                $item->rating_titles = $ratingWithTitle;
                foreach ($item->reviewTags as $tag) {
                    $tags[] = $tag->name;
                }
                $item->tags = implode(', ', $tags);
                return $item;
            });
        return view('exports.reviews', [
            'reviews' => $reviews,
            'categories' => $propertyCategories,
            'property_name' => $this->propertyName
        ]);
    }
}
