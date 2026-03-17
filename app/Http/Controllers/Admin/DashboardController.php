<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AllExport;
use App\Exports\CompanyExport;
use App\Http\Controllers\BaseController;
use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\RatingConfiguration;
use App\Models\Review;
use App\Models\ReviewStates;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends BaseController
{
    public function index()
    {
        $companyCount = CompanyDetails::where('status', 1)->count();
        $propertyCount = Properties::where('status', 1)->count();
        $reviewCount = Review::where('status', 1)->count();
        $reviewerCount = User::where('status', 1)->whereHas('roles', function ($qry) {
            $qry->where('name', 'reviewer');
        })->count();

        // review counts vs. days chart data
        $weekArray = [];


        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
        $reviews = Review::where('created_at', '>', $startOfWeek)
            ->where('created_at', '<', $endOfWeek)
            ->where('status', 1)
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d M');
            })
            ->transform(function ($item) {
                return count($item);
            })
            ->toArray();

        $dates = CarbonPeriod::create(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());

        foreach ($dates as $date) {
            $date = Carbon::parse($date)->format('d M');
            if (isset($reviews[$date])) {
                $weekArray[$date] = $reviews[$date];
            } else {
                $weekArray[$date] = 0;
            }
        }

        // stars vs amenities chart data
        $allReviews = Review::select('id')->with('ratings')->where('status', 1)->get();
        $amenitiesWiseReviews = [];
        foreach ($allReviews as $review) {
            foreach ($review->ratings as $rating) {
                if (!isset($amenitiesWiseReviews[$rating->reviewQuestion->name])) {
                    $amenitiesWiseReviews[$rating->reviewQuestion->name] = [];
                }
                $amenitiesWiseReviews[$rating->reviewQuestion->name][] = $rating->rate;
            }
        }

        foreach ($amenitiesWiseReviews as $key => $singleAmenityArr) {
            $amenitiesWiseReviews[$key] = roundOffRating((array_sum($singleAmenityArr)) / count($singleAmenityArr));
        }

        $propertyCategories = RatingConfiguration::pluck('name');


        foreach ($propertyCategories as $question) {
            if (!isset($amenitiesWiseReviews[$question])) $amenitiesWiseReviews[$question] = 0;
        }
        // get list of properties
        $properties = Properties::select('id', 'project_name as name')->where('status', 1)->get();
        // end

        // get list of companies
        $companies = CompanyDetails::select('id', 'company_name as name')->where('status', 1)->where('id', '!=', 3)->get();
        // end

        // for initially selected company's properties count
        $propertiesPieChart = $this->getReviewCountByProperty(count($companies) > 0 ? $companies[0]['id'] : '');
        // end

        // for states dropdown
        $states = ReviewStates::select('id', 'name')->get();
        // end

        // for properties count chart
//        $propertiesBarChart = $this->getReviewCountByPropertyTemp();
        $totalPropertyCount = Properties::where('status', 1)->count();
        // end

        // for company review count in order for bar chart
        $propertiesBarChart = $this->getCompanyReviewCount();
        // end

        // review question color codes
        $reviewQuestions = RatingConfiguration::select('id', 'name', 'color_code')
            ->get()
            ->groupBy('name')
            ->transform(function ($item) {
                return $item[0]['color_code'];
            })->toArray();
        // end

        return $this->formatSuccessResponse('success', [
            'counters' => [
                'company' => $companyCount,
                'property' => $propertyCount,
                'review' => $reviewCount,
                'reviewer' => $reviewerCount,
            ],
            'chart1' => [
                'week_data' => $weekArray,
            ],
            'chart2' => [
                'data' => $amenitiesWiseReviews
            ],
            'properties' => $properties,
            'companies' => $companies,
            'properties_pie_chart' => $propertiesPieChart,
            'properties_bar_chart' => [
                'data' => $propertiesBarChart,
                'total' => $totalPropertyCount
            ],
            'options' => [
                'state' => $states
            ],
            'review_question' => $reviewQuestions
        ]);
    }

    // mode options : first or last
    public function getCompanyReviewCount($mode = 'first', $state = '', $suburb = '')
    {
        $result = CompanyDetails::select('id', 'company_name as project_name')
            ->where('status', 1)
            ->with(['companyProperties' => function ($qry) {
                $qry->select('id', 'developer_name')
                    ->where('status', 1)
                    ->with(['reviews' => function ($qry) {
                        $qry->select('id', 'property_id', 'title', 'comment')
                            ->orderBy('created_at', 'DESC')
                            ->with(['ratings', 'reviewTags'])
                            ->where('status', 1);
                    }]);
            }]);

        if ($state || $suburb) {
            $result = $result->whereHas('companyProperties', function ($qry) use ($state, $suburb) {
                if ($state) {
                    $qry->where('state', $state);
                }
                if ($suburb) {
                    $qry->where('suburb', $suburb);
                }
            });
        }

        $result = $result->get()
            ->transform(function ($company) {
                $propertyRatings = [];

                foreach ($company->companyProperties as $item) {
                    $avgOfReviews = [];
                    foreach ($item->reviews as $review) {
                        $totalRating = 0;
                        foreach ($review->ratings as $rating) {
                            $totalRating += $rating->rate;
                        }
                        $avgOfRating = round($totalRating / count($review->ratings));
                        $avgOfReviews[] = $avgOfRating;
                        unset($review->ratings);
                    }
                    if (count($item->reviews) > 0) {
                        $item->overall_rating = roundOffRating(array_sum($avgOfReviews) / count($item->reviews));
                    } else {
                        $item->overall_rating_exp = 0;
                    }
                    $propertyRatings[] = $item->overall_rating;
                }


                $propertyWithReviewCount = 0;
                foreach ($company->companyProperties as $property) {
                    $propertyWithReviewCount += count($property->reviews) > 0 ? 1 : 0;
                }

                $company->overall_rating = $propertyWithReviewCount > 0 ? roundOffRating(array_sum($propertyRatings) / $propertyWithReviewCount) : 0;
                return $company;
            })->toArray();

        usort($result, function ($a, $b) use ($mode) {
            return ($mode === 'first' ? $a['overall_rating'] < $b['overall_rating'] : $a['overall_rating'] > $b['overall_rating']) ? 1 : -1;
        });
        return array_slice($result, 0, 30);
    }

    public function getPropertyReviewCount(Request $request)
    {
        $companyId = $request->company_id;
        $state = $request->state;
        $suburb = $request->suburb;
        $mode = $request->mode;

        if ($companyId) {
            $properties = Properties::select('id', 'project_name')->withCount(['reviews' => function ($qry) {
                $qry->where('status', 1);
            }])->where('status', 1);

            if ($companyId) {
                $properties = $properties->where('developer_name', $companyId);
            }

            if ($state) {
                $properties = $properties->whereState($state);
            }

            if ($suburb) {
                $properties = $properties->where('suburb', $suburb);
            }

            $data = $properties->get()->transform(function ($item) {
                $avgOfReviews = [];
                foreach ($item->reviews as $review) {
                    $totalRating = 0;
                    foreach ($review->ratings as $rating) {
                        $totalRating += $rating->rate;
                    }
                    $avgOfRating = round($totalRating / count($review->ratings));
                    $avgOfReviews[] = $avgOfRating;
                    unset($review->ratings);
                }
                if (count($item->reviews) > 0) {
                    $item->overall_rating = roundOffRating(array_sum($avgOfReviews) / count($item->reviews));
                } else {
                    $item->overall_rating = 0;
                }
                return $item;
            })->toArray();

            usort($data, function ($a, $b) use ($mode) {
                return ($mode === 'first' ? $a['overall_rating'] < $b['overall_rating'] : $a['overall_rating'] > $b['overall_rating']) ? 1 : -1;
            });

            return $this->formatSuccessResponse('success', ['data' => array_slice($data, 0, 30)]);
        } else {
            $result = $this->getCompanyReviewCount($mode, $state, $suburb);
            return $this->formatSuccessResponse('success', ['data' => array_slice($result, 0, 30)]);
        }
    }

    public function getReviewCountByPropertyTemp($companyId = '', $start = 0, $limit = 30)
    {
        $qry = Properties::select('id', 'project_name')->withCount(['reviews' => function ($qry) {
            $qry->where('status', 1);
        }])->where('status', 1);

        if ($companyId) {
            $qry = $qry->where('developer_name', $companyId);
        }

        return $qry->take($limit)->get();
    }

    public function getReviewCountByProperty($companyId)
    {
        return Properties::select('id', 'project_name')->withCount(['reviews' => function ($qry) {
            $qry->where('status', 1);
        }])->where('status', 1)
            ->where('developer_name', $companyId)
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    public function getReviewCountsByDate(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $reviews = Review::where('created_at', '>', $start)
            ->where('created_at', '<', $end)
            ->where('status', 1)
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d M');
            })
            ->transform(function ($item) {
                return count($item);
            })
            ->toArray();
        $period = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $date = Carbon::parse($date)->format('d M');
            if (isset($reviews[$date])) {
                $period[$date] = $reviews[$date];
            } else {
                $period[$date] = 0;
            }
        }
        return $this->formatSuccessResponse('success', $period);
    }

    public function getPropertiesChart(Request $request)
    {
        $properties = $this->getReviewCountByProperty($request->company_id);
        return $this->formatSuccessResponse(200, $properties);
    }

    public function filterRatingChart(Request $request)
    {
        $companyId = $request->company_id;
        $propertyId = $request->project_id;
        $suburb = $request->suburb;
        $state = $request->state;

        $allReviews = Review::select('id')->with('ratings')->where('status', 1);

        if ($companyId) {
            $allReviews = $allReviews->whereHas('property', function ($qry) use ($companyId) {
                $qry->where('developer_name', $companyId);
            });
        }

        if ($propertyId) {
            $allReviews = $allReviews->wherePropertyId($propertyId);
        }

        if ($suburb) {
            $allReviews = $allReviews->where('suburb', 'LIKE', '%' . $suburb . '%');
        }
        if ($state) {
            $allReviews = $allReviews->where('state', $state);
        }
        $allReviews = $allReviews->get();

        $amenitiesWiseReviews = [];
        foreach ($allReviews as $review) {
            foreach ($review->ratings as $rating) {
                if (!isset($amenitiesWiseReviews[$rating->reviewQuestion->name])) {
                    $amenitiesWiseReviews[$rating->reviewQuestion->name] = [];
                }
                $amenitiesWiseReviews[$rating->reviewQuestion->name][] = $rating->rate;
            }
        }

        foreach ($amenitiesWiseReviews as $key => $singleAmenityArr) {
            $amenitiesWiseReviews[$key] = roundOffRating((array_sum($singleAmenityArr)) / count($singleAmenityArr));
        }
        $propertyCategories = RatingConfiguration::pluck('name');
        foreach ($propertyCategories as $question) {
            if (!isset($amenitiesWiseReviews[$question])) $amenitiesWiseReviews[$question] = 0;
        }
        return $this->formatSuccessResponse('success', $amenitiesWiseReviews);
    }

    public function downloadCsv()
    {
//        return ($_SERVER);
        try {
            if (App::environment() == 'local') {
                Excel::store(new AllExport, 'csv-exports/admin-export.csv', 'public', \Maatwebsite\Excel\Excel::CSV);
            } else {
                Excel::store(new AllExport, 'csv-exports/admin-export.csv', 's3', \Maatwebsite\Excel\Excel::CSV, [
                    'visibility' => 'public',
                ]);
            }

            return $this->formatSuccessResponse('success', ['url' => 'csv-exports/admin-export.csv']);
        } catch (\Exception $e) {
            Log::info('CSV_EXPORT_ERROR : ' . $e->getMessage());
            return $this->formatFailureResponse('error');
        }
    }
}
