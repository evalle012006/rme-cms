<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use App\Models\Review;
use App\Models\SelectMaster;
use App\Models\Tags;
use App\Support\StateResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class PropertyListingController extends Controller
{
    public function __construct()
    {
        $tags = Tags::where('is_active', 1)->get();
        view()->share('tags', $tags);
//        View::share('property-listing', $tags);
    }

    public function propertyListingPage()
    {
        $properties = Properties::with([
            'propertyAllowedCategory',
            'reviews' => function ($qry) {
                $qry->select('id', 'property_id', 'title', 'comment')
                    ->orderBy('created_at', 'DESC')
                    ->where('status', 1)
                    ->with(['ratings', 'reviewTags']);
            },
        ])->whereHas('reviews', function ($q) {
            $q->where('status', 1);
        })->where('status', 1)->orderBy('created_at', 'DESC');

        $view_type = intval(request()->get('view_type', 3));

        $properties = $this->appendPropertyRatings($properties);
        $properties = collectPaginate($properties);
        $properties->appends(['view_type' => $view_type]);

        $cities = Properties::select('city')->where('status', 1)->distinct()->get()->toArray();
        $property_category = PropertyCategory::get()->toArray();


        return $this->renderPropertyListing(compact('properties', 'cities', 'property_category', 'view_type'));
    }

    public function propertyListingByBrand()
    {
        return view('property-listing-by-brand');
    }

    public function appendPropertyRatings($properties, $selectedRatings = [])
    {
        $properties = $properties->get()->transform(function ($item) use ($selectedRatings) {
            $avgOfReviews = [];
            $ratingsByTitle = [];
            $reviewTags = [];
            $ratingCountByNumber = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            foreach ($item->reviews as $review) {
                $totalRating = 0;
                foreach ($review->ratings as $rating) {
                    if (!isset($ratingsByTitle[$rating->reviewQuestion->name])) {
                        $ratingsByTitle[$rating->reviewQuestion->name] = [];
                    }
                    $ratingsByTitle[$rating->reviewQuestion->name][] = $rating->rate;
                    $totalRating += $rating->rate;
                }
                foreach ($review->reviewTags as $tag) {
                    $reviewTags[$tag->id] = $tag->name;
                }
                $avgOfRating = round($totalRating / count($review->ratings));
                $ratingCountByNumber[$avgOfRating] += 1;
                $avgOfReviews[] = roundOffRating($totalRating / count($review->ratings));
                unset($review->ratings);
            }
            foreach ($ratingsByTitle as $key => $singleRatingArray) {
                $ratingsByTitle[$key] = roundOffRating(array_sum($singleRatingArray) / count($singleRatingArray));
            }
            $item->ratings_by_title = $ratingsByTitle;
            if (count($item->reviews) > 0) {
//                $item->overall_rating = number_format((float)(array_sum($avgOfReviews) / count($item->reviews)), 1, '.', '');
                $item->overall_rating = roundOffRating(array_sum($avgOfReviews) / count($item->reviews));
            } else {
                $item->overall_rating = 0;
            }

            $item->last_reviews = array_slice($item->reviews->toArray(), 0, 2);
            $item->review_count = count($item->reviews);

            foreach ($ratingCountByNumber as $key => $starRating) {
                $ratingCountByNumber[$key] = [$starRating, $item->review_count > 0 ? roundOffRating((100 * $starRating) / $item->review_count) : 0];
            }
            $item->rating_number = $ratingCountByNumber;
            unset($item->reviews);
            $item->review_tags = $reviewTags;
            if ($selectedRatings) {
                return array_search($item->overall_rating, $selectedRatings) !== false ? $item : null;
            }
            return $item;
        });
        foreach ($properties as $key => $property) {
            if ($property === null) {
                unset($properties[$key]);
            }
        }
        return $properties;
    }

    public function searchData(Request $request, $slug = '')
    {
        $searchKey = $request->search_key;
        $searchCompany = $request->search_company;
        $filter_by = $request->filter_by;
        $cat_type = $request->category_type;
        $tags = $request->tags;
        $selectedRatings = $request->rating;
        $view_type = $request->get('view_type', 1);
        $cat_slug = $slug;

        $cities = Properties::select('city')->where('status', 1)->distinct()->get()->toArray();
        $property_category = PropertyCategory::get()->toArray();

        $catArray = [];
        if ($cat_slug) {
            $category = PropertyCategory::where('slug', $cat_slug)->first();
            if ($category) {
                $catArray[] = $category->id;
            }
        }
        if (isset($cat_type)) {
            $catArray = array_merge($catArray, $cat_type);
        }
        if ($searchKey == null && $searchCompany !== null) {
            $companies = CompanyDetails::where('status', 1)
                ->where('company_name', 'LIKE', '%' . $searchCompany . '%')
                ->with(['companyProperties' => function ($qry) use ($catArray, $tags) {
                    $qry->where('status', 1)
                        ->with(['reviews' => function ($qry) use ($catArray, $tags) {
                            $qry->select('id', 'property_id', 'title', 'comment')
                                ->orderBy('created_at', 'DESC')
                                ->with(['ratings', 'reviewTags'])
                                ->where('status', 1);

                            if ($tags) {
                                $qry->whereHas('reviewTags', function ($query) use ($tags) {
                                    $query->whereIn('tag_id', $tags);
                                });
                            }

                            if (count($catArray) > 0) {
                                $qry->whereIn('category_id', $catArray);
                            }
                        }]);
                }]);

            if ($tags) {

                $companies->whereHas('companyProperties.reviews.reviewTags', function ($qry) use ($tags) {
                    $qry->whereIn('tag_id', $tags);
                });
            }

            if ($filter_by !== null || $cat_slug !== null) {
                $companies->whereHas('companyProperties', function ($qry) use ($filter_by, $cat_slug, $catArray) {
                    if ($filter_by) {
                        $qry->where('suburb', 'LIKE', '%' . $filter_by . '%');
                    }

                    $qry->whereHas('reviews', function ($query) use ($catArray) {
                        if (count($catArray) > 0) {
                            $query->whereIn('category_id', $catArray);
                        }
                        $query->where('status', 1);
                    });
                });
            }
            $companies = $companies->orderBy('created_at', 'DESC')->get();
            $companies->transform(function ($company) use ($selectedRatings) {
                $propertyRatings = [];
                $reviewTags = [];
                $reviewCount = 0;
                $lastReviews = [];
                $propertyWithRatings = [];
                $ratingCountByNumber = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

                foreach ($company->companyProperties as $item) {
                    $avgOfReviews = [];

                    foreach ($item->reviews as $review) {
                        $totalRating = 0;
                        foreach ($review->ratings as $rating) {
                            $totalRating += $rating->rate;
                        }
                        foreach ($review->reviewTags as $tag) {
                            $reviewTags[$tag->id] = $tag->name;
                        }
                        $avgOfRating = round($totalRating / count($review->ratings));
                        $ratingCountByNumber[$avgOfRating] += 1;
                        $avgOfReviews[] = $avgOfRating;

                        unset($review->ratings);
                    }
                    $item->overall_rating = count($item->reviews) > 0 ? roundOffRating(array_sum($avgOfReviews) / count($item->reviews)) : 0;
                    if ($item->overall_rating > 0) {
                        $propertyWithRatings[$item->project_name] = ['rate' => $item->overall_rating, 'slug' => $item->slug];
                    }
                    $propertyRatings[] = $item->overall_rating;
                    $lastReviews = array_merge(array_slice($item->reviews->toArray(), 0, 2), $lastReviews);
                    $reviewCount += count($item->reviews);
                    $item->review_tags = $reviewTags;
                }

                $propertyWithReviewCount = 0;
                foreach ($company->companyProperties as $property) {
                    $propertyWithReviewCount += count($property->reviews) > 0 ? 1 : 0;
                }

                $company->overall_rating = $propertyWithReviewCount > 0 ? roundOffRating(array_sum($propertyRatings) / $propertyWithReviewCount) : 0;
                $company->review_tags = $reviewTags;
                if ($reviewCount > 0) {
                    $company->reviews = range(1, $reviewCount);
                } else {
                    $company->reviews = [];
                }
                usort($lastReviews, function ($a, $b) {
                    return $a['id'] > $b['id'] ? -1 : 1;
                });
                $company->last_reviews = array_slice($lastReviews, 0, 2);
                $company->company_properties = $propertyWithRatings;

                $company->company = (object)[];
                $company->company->id = $company->id;
                $company->company->company_name = $company->company_name;
                $company->company->slug = $company->slug;
                $company->is_company = true;

                foreach ($ratingCountByNumber as $key => $starRating) {
                    $ratingCountByNumber[$key] = [$starRating, round((100 * $starRating) / count($company->reviews))];
                }
                $company->rating_number = $ratingCountByNumber;

                unset($company->companyProperties);
                if ($selectedRatings) {
                    return array_search($company->overall_rating, $selectedRatings) !== false ? $company : null;
                } else {
                    return $company;
                }
            });

            foreach ($companies as $key => $company) {
                if ($company == null) {
                    unset($companies[$key]);
                }
            }

            $companies = collectPaginate($companies);

            $companies->appends(array(
                'search_key' => $request->search_key,
                'filter_by' => $request->filter_by,
                'category_type' => $request->category_type,
                'is_slug' => $request->is_slug,
                'view_type' => $view_type
            ));
            $properties = $companies;
            return $this->renderPropertyListing(compact('properties', 'cities', 'property_category', 'view_type', 'cat_slug'));
        } elseif ($searchKey !== null || $filter_by !== null || isset($cat_type) || $cat_slug !== null || $searchCompany !== null) {
            $properties = Properties::with([
                'propertyAllowedCategory',
                'reviews' => function ($qry) use ($tags, $catArray) {
                    $qry->select('id', 'property_id', 'title', 'comment')
                        ->orderBy('created_at', 'DESC')
                        ->where('status', 1)
                        ->with(['ratings', 'reviewTags']);

                    if (count($catArray) > 0) {
                        $qry->whereIn('category_id', $catArray);
                    }
                },
            ])->whereHas('reviews', function ($q) {
                $q->where('status', 1);
            })
                ->where('status', 1);
            if ($tags) {
                $properties->whereHas('reviews.reviewTags', function ($query) use ($tags) {
                    $query->whereIn('tag_id', $tags);
                });
            }

            if ($filter_by) {
                $properties->where('suburb', 'LIKE', '%' . $filter_by . '%');
            }

            if ($searchKey) {
                $properties->where('project_name', 'LIKE', '%' . $searchKey . '%');
            }

            if ($searchCompany) {
                $properties->whereHas('company', function ($query) use ($searchCompany) {
                    $query->where('company_name', 'LIKE', '%' . $searchCompany . '%');
                });
            }

            if (count($catArray) > 0) {
                $properties = $properties->whereHas('reviews',
                    function ($query) use ($catArray) {
                        $query->whereIn('category_id', $catArray);
                        $query->where('status', 1);
                    }
                );
            }

            $properties = $properties->orderBy('created_at', 'DESC');
            $properties = $this->appendPropertyRatings($properties, $selectedRatings);
            $properties = collectPaginate($properties);

            $properties->appends(array(
                'search_key' => $request->search_key,
                'filter_by' => $request->filter_by,
                'category_type' => $request->category_type,
                'is_slug' => $request->is_slug,
                'view_type' => $view_type
            ));
            return $this->renderPropertyListing(compact('properties', 'cities', 'property_category', 'view_type', 'cat_slug'));
        } else {
            $properties = Properties::with('propertyAllowedCategory')->where('status', 1);
            if ($cat_slug !== null) {
                $properties = $properties->whereHas('propertyAllowedCategory',
                    function ($query) use ($cat_slug) {
                        $query->where('slug', $cat_slug);
                    }
                );
            }
            $properties = $properties->paginate(6);
            return $this->renderPropertyListing(compact('properties', 'cities', 'property_category', 'view_type', 'cat_slug'));
        }
    }

    public function getPropertyBySlug($slug)
    {
        $category = PropertyCategory::where('slug', $slug)->first();
        $properties = Properties::where('status', 1);

        $properties = $properties->whereHas('reviews',
            function ($query) use ($category) {
                $query->where('category_id', $category->id);
                $query->where('status', 1);
            }
        );
        $properties = $this->appendPropertyRatings($properties);
        $properties = collectPaginate($properties);
        $view_type = intval(request()->get('view_type', 1));
        $properties->appends(['view_type' => $view_type]);

        $cities = Properties::select('city')->where('status', 1)->distinct()->get()->toArray();
        $property_category = PropertyCategory::get()->toArray();
        $cat_slug = $slug;
        return $this->renderPropertyListing(compact('properties', 'cities', 'property_category', 'view_type', 'cat_slug'));
    }

    public function getPropertyWithCompany($slug)
    {
        $company = CompanyDetails::where('slug', $slug)->first();

        if (!empty($company)) {
            $properties = Properties::with(['reviews' => function ($qry) {
                $qry->select('id', 'property_id', 'category_id')
                    ->with('ratings')
                    ->where('status', 1);
            }, 'reviews.reviewTags'])
                ->where('developer_name', $company->id)
                ->where('status', 1)
                ->get();

            $sumOfRatings = 0;
            $totalReviews = 0;
            foreach ($properties as $property) {
                $ratingTitles = [];
                $totalRatings = 0;
                $reviewTags = [];
                $avgOfReviews = [];
                foreach ($property->reviews as $review) {
                    $totalRating = 0;
                    foreach ($review->ratings as $rating) {
                        $ratingTitles[$rating->reviewQuestion->name][] = $rating['rate'];
                        $totalRating += $rating->rate;
                    }
                    $avgOfRating = round($totalRating / count($review->ratings));
                    $avgOfReviews[] = $avgOfRating;
                    foreach ($review->reviewTags as $tag) {
                        $reviewTags[$tag->id] = $tag->name;
                    }
                }
                foreach ($ratingTitles as $key => $rates) {
                    $avgQueRating = roundOffRating(array_sum($rates) / count($rates));
                    $ratingTitles[$key] = $avgQueRating;
                    $totalRatings += $avgQueRating;
                }
                $property->que_ratings = $ratingTitles;

                if (count($property->reviews) > 0) {
                    $property->avg_rating = roundOffRating(array_sum($avgOfReviews) / count($property->reviews));
                } else {
                    $property->avg_rating = 0;
                }

                $property->review_tags = $reviewTags;
                $sumOfRatings += $property->avg_rating;
                $totalReviews += count($property->reviews);
            }
            $propertyCountWithReviews = 0;
            foreach ($properties as $singleProperty) {
                $propertyCountWithReviews += count($singleProperty->reviews) > 0 ? 1 : 0;
            }
            $avg_of_all = $propertyCountWithReviews > 0 ? roundOffRating($sumOfRatings / $propertyCountWithReviews) : 0;
            $property_category = PropertyCategory::get()->toArray();
            // get company advertise
            $advertises = Advertise::where('company_id', $company->id)->where('status', 1)->get();

            return view('property-listing-by-brand', compact('company', 'properties', 'property_category', 'avg_of_all', 'totalReviews', 'advertises'));
        }
        return redirect()->back();
    }

    public function getCompanyPropertyByCategory($category_slug, $slug)
    {
        $company = CompanyDetails::where('slug', $slug)->first();
        $category = PropertyCategory::where('slug', $category_slug)->first();
        $property_category = PropertyCategory::get()->toArray();

        if (isset($company->id) && isset($category->id)) {
            $properties = Properties::select('id', 'project_name', 'slug')
                ->with(['reviews' => function ($qry) use ($category) {
                    $qry->select('id', 'property_id', 'category_id')
                        ->with('ratings')
                        ->where('status', 1)
                        ->where('category_id', $category->id);
                }, 'reviews.reviewTags'])
                ->where('developer_name', $company->id)
                ->where('status', 1)
                ->get();
            $sumOfRatings = 0;
            $totalReviews = 0;
            foreach ($properties as $property) {
                $ratingTitles = [];
                $totalRatings = 0;
                $reviewTags = [];
                foreach ($property->reviews as $review) {
                    foreach ($review->ratings as $rating) {
                        $ratingTitles[$rating->reviewQuestion->name][] = $rating['rate'];
                    }
                    foreach ($review->reviewTags as $tag) {
                        $reviewTags[$tag->id] = $tag->name;
                    }
                }
                foreach ($ratingTitles as $key => $rates) {
                    $avgQueRating = roundOffRating(array_sum($rates) / count($rates));
                    $ratingTitles[$key] = $avgQueRating;
                    $totalRatings += $avgQueRating;
                }
                $property->que_ratings = $ratingTitles;
                if (count($ratingTitles) > 0) {

                    $property->avg_rating = roundOffRating($totalRatings / count($ratingTitles));
                } else {
                    $property->avg_rating = 0;
                }
                $property->review_tags = $reviewTags;
                $sumOfRatings += $property->avg_rating;
                $totalReviews += count($property->reviews);

            }
            $propertyCountWithReviews = 0;
            foreach ($properties as $singleProperty) {
                $propertyCountWithReviews += count($singleProperty->reviews) > 0 ? 1 : 0;
            }
            $avg_of_all = $propertyCountWithReviews > 0 ? roundOffRating($sumOfRatings / $propertyCountWithReviews) : 0;
            // get company advertise
            $advertises = Advertise::where('company_id', $company->id)->where('status', 1)->get();

            return view('property-listing-by-brand-category', compact('company', 'properties', 'property_category', 'avg_of_all', 'category_slug', 'totalReviews', 'advertises'));
        }
        return redirect()->back();
    }

    public function fetchCompany(Request $request)
    {
        $company_cookie = $request->get('cookie_company', null);
        $data = CompanyDetails::selectRaw('id, company_name as text')
            ->where('company_name', 'LIKE', "%" . $request->get('q') . "%")
            ->where('id', '<>', 3)
            ->where('status', 1)
            ->get();
        if (!empty($company_cookie)) {

            $com = CompanyDetails::selectRaw('id, company_name as text')
                ->where('company_name', 'LIKE', "%" . $request->get('q') . "%")
                ->where('id', '<>', 3)
                ->where('status', '<>', 1)
                ->where('id', $company_cookie['id'])->first();
            if ($com) {
                $data->push($com);
            }
        }
        return [
            'results' => $data,
        ];
    }

    public function fetchProperty(Request $request)
    {

        $categories = $request->get('categories');
        $cookie_property = $request->get('cookie_property', null);
        $query = Properties::selectRaw('id, project_name as text, state, suburb, post_code')->where('status', 1);

        if ($request->get('company_id')) {
            $query->where('developer_name', $request->get('company_id'));
        }

        if ($categories) {
            $query->whereHas('propertyAllowedCategory',
                function ($query) use ($categories) {
                    $query->where('property_category_id', $categories);
                }
            );
        }

        $data = $query->where('project_name', 'LIKE', "%" . $request->get('q') . "%")->get();

        if (!empty($cookie_property)) {
            $savedIdResults = Properties::selectRaw('id, project_name as text, state, suburb, post_code')
                ->where('id', $cookie_property['id'])->where('status', '<>', 1);

            if ($request->get('company_id')) {
                $savedIdResults->where('developer_name', $request->get('company_id'));
            }

            if ($categories) {
                $savedIdResults->whereHas('propertyAllowedCategory',
                    function ($query) use ($categories) {
                        $query->where('property_category_id', $categories);
                    }
                );
            }
            $savedIdResults = $savedIdResults->where('project_name', 'LIKE', "%" . $request->get('q') . "%")->first();

            if ($savedIdResults) {
                $data->push($savedIdResults);
            }
        }

        $results = $data->map(function ($property) {
            return [
                'id' => $property->id,
                'text' => $property->text,
                'state' => $property->state,
                'suburb' => $property->suburb,
                'post_code' => $property->post_code,
                'state_id' => StateResolver::id($property->state),
            ];
        })->values();

        return [
            'results' => $results,
        ];
    }

    public function getPropertyReviewBySlug($slug, Request $request)
    {
        $filter_by = $request->filter_by;
        $filter_rating = $request->filter_rating;
        $ratingNumbers = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $property = Properties::with('company')->where('slug', $slug)->first();

        $property_review = Review::with(['property:id,developer_name,project_name',
            'property.company', 'category',
            'reviewTags', 'ratings', 'users' => function ($qry) {
                $qry->withCount(['reviews' => function ($q) {
                    $q->where('status', 1);
                }]);
            }])
            ->where('property_id', $property->id)->where('status', 1);

        if ($filter_by) {
            $property_review = $property_review->where('title', 'LIKE', "%" . $filter_by . "%");
        }

        $property_review = $property_review->orderBy('created_at', 'DESC')->get()->toArray();

        $catRatings = collect();

        foreach ($property_review as $k_r => $v_r) {
            $sum = 0;
            foreach ($v_r['ratings'] as $k => $v) {
                if (!$catRatings->has($v['rating_conf_id'])) {
                    $catRatings->put($v['rating_conf_id'], collect());
                }
                $catRatings->get($v['rating_conf_id'])->push($v);

                $sum += $v['rate'];
            }
            $property_review[$k_r]['overall_rating'] = roundOffRating($sum / count($v_r['ratings']));
            $property_review[$k_r]['overall_rating_round'] = round($sum / count($v_r['ratings']));
            $ratingNumbers[$property_review[$k_r]['overall_rating_round']] += 1;
        }

//        dd($property_review);

        $avgRateColletion = collect();

        $catRatings->each(function ($item, $key) use ($avgRateColletion) {
            $dataColl = collect();

            $dataColl->put('avg', roundOffRating($item->avg('rate')));
            $dataColl->put('name', $item->get(0)['review_question']['name']);
            $dataColl->put('icon_full', $item->get(0)['review_question']['full_ratingconf_icon']);
            $avgRateColletion->put($key, $dataColl);
        });
        foreach ($ratingNumbers as $key => $starRating) {
            $ratingNumbers[$key] = [$starRating, count($property_review) > 0 ? roundOffRating((100 * $starRating) / count($property_review)) : 0];
        }
        foreach ($property_review as $k_r => $v_r) {
            if ($filter_rating) {
                if ((int)$v_r['overall_rating_round'] !== +$filter_rating) {
                    unset($property_review[$k_r]);
                }
            }
        }
        //overall rate sum
        $sum_overall = 0;
        $overall_rate = 0;
        foreach ($avgRateColletion as $avgRates) {
            $sum_overall += $avgRates['avg'];
        }
        if ($sum_overall != 0) {
//            $overall_rate = round($sum_overall / count($avgRateColletion));
            $overall_rate = roundOffRating($sum_overall / count($avgRateColletion));
        }

        $cities = Review::select('suburb')->where('status', 1)->distinct()->get()->toArray();

        return $this->renderPropertyReviewDetail(compact('property_review', 'cities', 'property', 'avgRateColletion', 'overall_rate', 'ratingNumbers'));
    }

    public function getPropertyReviewByCatSlug($cat, $slug, Request $request)
    {
        $category = PropertyCategory::where('slug', $cat)->first();
        $filter_by = $request->filter_by;
        $filter_rating = $request->filter_rating;

        $ratingNumbers = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        $property = Properties::with('company')->where('slug', $slug)->first();

        $property_review = Review::with(['property:id,developer_name,project_name',
            'property.company',
            'reviewTags', 'ratings', 'users' => function ($qry) {
                $qry->withCount(['reviews' => function ($q) {
                    $q->where('status', 1);
                }]);
            }])
            ->where('property_id', $property->id)
            ->where('category_id', $category->id)
            ->where('status', 1);


        if ($filter_by) {
            $property_review = $property_review->where('title', 'LIKE', "%" . $filter_by . "%");
        }

        $property_review = $property_review->orderBy('created_at', 'DESC')->get()->toArray();

        $allowRating = [];
        $catRatings = collect();

        foreach ($property_review as $k_r => $v_r) {
            $sum = 0;
            foreach ($v_r['ratings'] as $k => $v) {

                array_push($allowRating, $v);
                if (!$catRatings->has($v['rating_conf_id'])) {
                    $catRatings->put($v['rating_conf_id'], collect());
                }
                $catRatings->get($v['rating_conf_id'])->push($v);
                $sum += $v['rate'];
            }
            $property_review[$k_r]['overall_rating'] = roundOffRating($sum / count($v_r['ratings']));
            $property_review[$k_r]['overall_rating_round'] = round($sum / count($v_r['ratings']));
            $ratingNumbers[$property_review[$k_r]['overall_rating_round']] += 1;
        }

        $avgRateColletion = collect();

        $catRatings->each(function ($item, $key) use ($avgRateColletion) {
            $dataColl = collect();
            $dataColl->put('avg', roundOffRating($item->avg('rate')));
            $dataColl->put('name', $item->get(0)['review_question']['name']);
            $dataColl->put('icon_full', $item->get(0)['review_question']['full_ratingconf_icon']);
            $avgRateColletion->put($key, $dataColl);
        });
        foreach ($ratingNumbers as $key => $starRating) {
            $ratingNumbers[$key] = [$starRating, count($property_review) > 0 ? roundOffRating((100 * $starRating) / count($property_review)) : 0];
        }
        foreach ($property_review as $k_r => $v_r) {
            if ($filter_rating) {
                if ((int)$v_r['overall_rating_round'] !== +$filter_rating) {
                    unset($property_review[$k_r]);
                }
            }
        }
        //overall rate sum
        $sum_overall = 0;
        $overall_rate = 0;
        foreach ($avgRateColletion as $avgRates) {
            $sum_overall += $avgRates['avg'];
        }
        if ($sum_overall != 0) {
            $overall_rate = roundOffRating($sum_overall / count($avgRateColletion));
        }

        $cities = Review::select('suburb')->where('status', 1)->distinct()->get()->toArray();
        return $this->renderPropertyReviewDetail(compact('property_review', 'cities', 'property', 'cat', 'avgRateColletion', 'overall_rate', 'ratingNumbers'));
    }

    /**
     * Return listing results partial for AJAX, or the full listing page otherwise.
     */
    protected function renderPropertyListing(array $data)
    {
        if (request()->ajax()) {
            return view('partials.property-listing-results', $data);
        }

        return view('property-listing', $data);
    }

    /**
     * Return property review list partial for AJAX, or the full detail page otherwise.
     */
    protected function renderPropertyReviewDetail(array $data)
    {
        if (request()->ajax()) {
            return view('partials.property-review-list', $data);
        }

        return view('property-review-detail', $data);
    }
}
