<?php

namespace App\Http\Controllers;

use App\Exports\CompanyExport;
use App\Exports\ReviewsExport;
use App\Mail\ReportUserMail;
use App\Models\Advertise;
use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use App\Models\RatingConfiguration;
use App\Models\Review;
use App\Models\ReviewStates;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;

class BuilderController extends BaseController
{
    public function showDashboard()
    {
        if (auth()->user()->roles[0]['name'] === 'builder') {
            $user = auth()->user();
            $company = CompanyDetails::find($user->company_id);

            // get all properties of company
            $properties = Properties::with(['reviews' => function ($qry) {
                $qry->select('id', 'property_id', 'title', 'comment')
                    ->orderBy('created_at', 'DESC')
                    ->where('status', 1)
                    ->with(['ratings', 'reviewTags']);
            }])
                ->withCount(['reviews' => function ($qry) {
                    $qry->where('status', 1);
                }])
                ->where('status', 1)
                ->where('developer_name', $user->company_id)
                ->orderBy('created_at', 'DESC');

            // get review questions list
            $reviewQuestions = RatingConfiguration::select('id', 'name', 'color_code')
                ->get()
                ->groupBy('name')
                ->transform(function ($item) {
                    return $item[0];
                })->toArray();

            // for showing list of all properties
            $calculatedData = $this->calculateRatings($properties->get());
            $allPropertyReviews = $calculatedData[0];
            $amenityWiseRatings = $calculatedData[1];

            // for showing most recent 3 properties
            $latestProperties = $allPropertyReviews->take(3);

            // count total number of reviews
            $totalReviews = 0;
            foreach ($allPropertyReviews as $propertyReviews) {
                $totalReviews += $propertyReviews->reviews_count;
            }
            // end

            $dragToEnd = false;
            if (\request()->suburb || \request()->state || \request()->project_name) {
                $dragToEnd = true;
                $suburb = \request()->suburb;
                $state = \request()->state;
                $projectName = \request()->project_name;

                if ($projectName) {
                    $properties = $properties->where('project_name', 'LIKE', '%' . $projectName . '%');
                }

                if ($suburb) {
                    $properties = $properties->where('suburb', 'LIKE', '%' . $suburb . '%');
                }

                if ($state) {
                    $properties = $properties->where('state', 'LIKE', '%' . $state . '%');
                }

                $calculated = $this->calculateRatings($properties->get());
                $allPropertyReviews = $calculated[0];
            }

            // monthly and yearly count of reviews
            $monthlyCount = $this->getReviewsMonthlyCount(Auth::user()->company_id);
            $yearlyCount = $this->getReviewsYearlyCount(Auth::user()->company_id);
            // end

            // get review counts by date range count
            $dateRangeCount = $this->getReviewDateRangeCount(Auth::user()->company_id);
            // end

            // property review exports search
            $propertyDetailExport = [];
            if (\request()->project_id_export) {
                $exportPropertyId = \request()->project_id_export;
                $exportProperty = Properties::with(['reviews' => function ($qry) {
                    $qry->select('id', 'property_id', 'title', 'comment')
                        ->orderBy('created_at', 'DESC')
                        ->where('status', 1)
                        ->with(['ratings', 'reviewTags']);
                }])
                    ->withCount(['reviews' => function ($qry) {
                        $qry->where('status', 1);
                    }])
                    ->where('id', $exportPropertyId);

                $calculatedDataExport = $this->calculateRatings($exportProperty->get());
                $propertyDetailExport = $calculatedDataExport[0];
            }
            // end

            // get property list for dropdown search
            $propsOptions = Properties::select('id', 'project_name', 'slug')
                ->where('developer_name', $company->id)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->get();
            // end

            // categories
            $categories = PropertyCategory::select('id', 'name', 'slug')->get();
            // end

            // states
            $states = ReviewStates::select('id', 'name')->get();
            // end

            return view('builder-portal.dashboard', compact('user',
                'company',
                'latestProperties',
                'allPropertyReviews',
                'reviewQuestions',
                'amenityWiseRatings',
                'propertyDetailExport',
                'totalReviews',
                'monthlyCount',
                'yearlyCount',
                'categories',
                'propsOptions',
                'dragToEnd',
                'dateRangeCount',
                'states'));
        }
        return redirect('/');
    }

    public function calculateRatings($properties)
    {
        $amenitiesWiseReviews = [];
        $props = $properties->transform(function ($item) use (&$amenitiesWiseReviews) {
            $avgOfReviews = [];
            $reviewTags = [];
            foreach ($item->reviews as $review) {
                $totalRating = 0;
                foreach ($review->ratings as $rating) {
                    if (!isset($amenitiesWiseReviews[$rating->reviewQuestion->name])) {
                        $amenitiesWiseReviews[$rating->reviewQuestion->name] = [];
                    }
                    $amenitiesWiseReviews[$rating->reviewQuestion->name][] = $rating->rate;
                    $totalRating += $rating->rate;
                }
                foreach ($review->reviewTags as $tag) {
                    $reviewTags[$tag->id] = $tag->name;
                }
                $avgOfRating = round($totalRating / count($review->ratings));
                $avgOfReviews[] = $avgOfRating;
                unset($review->ratings);
            }
            $item->overall_rating = roundOffRating(array_sum($avgOfReviews) / (count($item->reviews) > 0 ? count($item->reviews) : 1));
            $item->review_tags = $reviewTags;
            return $item;
        });

        foreach ($amenitiesWiseReviews as $key => $singleAmenityArr) {
            $amenitiesWiseReviews[$key] = round((array_sum($singleAmenityArr) * 100) / (count($singleAmenityArr) * 5));
        }

        return [$props, $amenitiesWiseReviews];
    }

    public function getAmenitiesByProject(Request $request)
    {
        $propertyId = $request->property;
        $suburb = $request->suburb;
        $state = $request->state;
        $categoryId = $request->category;

        $companyId = '';
        if (!$propertyId) {
            $companyId = Auth::user()->company_id;
//            $companyId = 1;
        }
        $amenitiesWiseReviews = [];
        $review = Review::with(['ratings'])
            ->where('status', 1);

        if ($propertyId) {
            $review = $review->where('property_id', $propertyId);
        } else {
            $review = $review->whereHas('property', function ($qry) use ($companyId) {
                $qry->where('developer_name', $companyId);
            });
        }

        if ($suburb) {
            $review = $review->where('suburb', 'LIKE', '%' . $suburb . '%');
        }

        if ($state) {
//            $review = $review->whereHas('stateName', function ($qry) use ($state) {
//                $qry->where('name', 'LIKE', '%' . $state . '%');
//            });
            $review = $review->where('state', $state);
        }

        if ($categoryId) {
            $review = $review->where('category_id', $categoryId);
        }

        $review = $review->get()
            ->transform(function ($review) use (&$amenitiesWiseReviews) {
                foreach ($review->ratings as $rating) {
                    if (!isset($amenitiesWiseReviews[$rating->reviewQuestion->name])) {
                        $amenitiesWiseReviews[$rating->reviewQuestion->name] = [];
                    }
                    $amenitiesWiseReviews[$rating->reviewQuestion->name][] = $rating->rate;
                }
            });
        foreach ($amenitiesWiseReviews as $key => $singleAmenityArr) {
            $amenitiesWiseReviews[$key] = round((array_sum($singleAmenityArr) * 100) / (count($singleAmenityArr) * 5));
        }

        return json_encode(['data' => $amenitiesWiseReviews, 'review_count' => count($review)]);
    }

    public function getReviewCounts(Request $request)
    {
        $userCompany = Auth::user()->company_id;
        $month = $request->month;
        $year = $request->year;

        $monthlyCount = $this->getReviewsMonthlyCount($userCompany, $month, $year);
        $yearlyCount = $this->getReviewsYearlyCount($userCompany, $year);

        return ['yearly' => $yearlyCount, 'monthly' => $monthlyCount];
    }

    public function getDateRangeCounts(Request $request)
    {
        $userCompany = Auth::user()->company_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $reviewCounts = $this->getReviewDateRangeCount($userCompany, $startDate, $endDate);
        return ['count' => $reviewCounts];
    }

    public function getReviewsYearlyCount($userCompany, $year = '')
    {
        return Review::whereHas('property', function ($qry) use ($userCompany) {
            $qry->where('developer_name', $userCompany);
        })->where('status', 1)
            ->whereYear('created_at', '=', $year ? $year : Carbon::now()->year)
            ->count();
    }

    public function getReviewsMonthlyCount($userCompany, $month = '', $year = '')
    {
        return Review::whereHas('property', function ($qry) use ($userCompany) {
            $qry->where('developer_name', $userCompany);
        })->where('status', 1)
            ->whereMonth('created_at', '=', $month ? $month : Carbon::now()->month)
            ->whereYear('created_at', '=', $year ? $year : Carbon::now()->year)
            ->count();
    }

    public function getReviewDateRangeCount($companyId, $startDate = '', $endDate = '')
    {
        return Review::whereHas('property', function ($qry) use ($companyId) {
            $qry->where('developer_name', $companyId);
        })->where('status', 1)
            ->where('created_at', '>=', ($startDate ? $startDate : Carbon::now()->startOfMonth()->format('Y-m-d H:i:s')))
            ->where('created_at', '<=', ($endDate ? $endDate : Carbon::now()->format('Y-m-d H:i:s')))
            ->count();
    }

    public function myProperties()
    {
        if (auth()->user()->roles[0]['name'] === 'builder') {
            $user = auth()->user();
            $company = CompanyDetails::find($user->company_id);

            // get property reviews
            $properties = Properties::with(['reviews' => function ($qry) {
                $qry->select('id', 'property_id', 'title', 'comment')
                    ->orderBy('created_at', 'DESC')
                    ->where('status', 1)
                    ->with(['ratings', 'reviewTags']);
            }])
                ->where('status', 1)
                ->whereDeveloperName($user->company_id)
                ->orderBy('created_at', 'DESC');

            $properties = app('App\Http\Controllers\PropertyListingController')->appendPropertyRatings($properties);
            $properties = collectPaginate($properties, [], 3);

            // get pending properties
            $pendingProperties = Properties::whereStatus(0)
                ->whereDeveloperName($user->company_id)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ["*"], 'pending');

            // get paginated advertises

            $advertises = Advertise::where('created_by', Auth::user()->id)
                ->where('company_id', Auth::user()->company_id)
                ->orderBy('created_at', 'DESC')
                ->paginate(3, ["*"], 'advertise');
            $propertyCategory = PropertyCategory::get()->toArray();
            return view('builder-portal.my-property', compact('user', 'company', 'properties', 'pendingProperties', 'advertises', 'propertyCategory'));
        }
        return redirect('/');
    }

    public function createProperty(Request $request)
    {
        $rules = [
            'developer_name' => 'required',
            'project_name' => 'required',
            'email' => 'required',
            'lat' => 'required',
            'long' => 'required',
            'categories' => 'required'
        ];
        $data = $request->all();
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->saveToS3($request->file('logo'), $this->PROPERTY_LOGO_STORAGE_PATH);
        }
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $request->session()->put('property_create_error', 'Error while creating property');
        } else {
            $property = Properties::create($data);
            if ($property) {
                $result = app('App\Http\Controllers\Admin\PropertyController')->savePropertyCategories($property->id, $data['categories']);
                if ($result) {
                    $request->session()->put('property_create_error', 'Error while creating property');
                }
            }
        }
        return redirect()->back();
    }

    public function updateCompanyBio(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, ['id' => 'required|exists:company_details,id']);
        if ($validator->fails()) {
            return response()->json('error');
        } else {
            $updated = CompanyDetails::find($data['id'])->update(['bio' => $data['bio']]);
            if ($updated) {
                return response()->json('success');
            }
            return response()->json('error');
        }
    }

    public function updateCompanyLogo(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'profile_image' => 'nullable'
        ]);
        $company = CompanyDetails::find($request->id);
        if ($request->hasFile('profile_image')) {
            $data['logo'] = $this->saveToS3($request->file('profile_image'), $this->COMPANY_LOGO_STORAGE_PATH);
            $data['logo_thumb'] = $this->saveThumbToS3($data['logo']);
            if (!empty($company->logo)) {
                Storage::delete($company->logo);
            }
        }
        $updated = $company->update($data);
        if ($updated) {
            return response()->json([], 200);
        }
        return response()->json('error', 500);
    }

    public function exportCompany()
    {
        $companyName = Auth::user()->company->company_name;
        return Excel::download(new CompanyExport, $companyName . '.csv');
    }

    public function exportPropertyReviews($id)
    {
        $property = Properties::find($id);
        return Excel::download(new ReviewsExport($id, $property->project_name), $property->project_name . '.csv');
    }

    public function validateEmail(Request $request)
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        ];
        $validator = Validator::make(['email' => $request->email], $rules);
        if ($validator->fails()) {
            return "false";
        }
        return "true";
    }

    public function addNewUser(Request $request)
    {
        $rules = [
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required|min:6',
            'email' => 'required|unique:users,email'
        ];
        $data = $request->all();

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back();
        } else {
            try {
                if ($request->has('profile_image') && $request->profile_image) {
                    $profileImage = $request->profile_image;
                    $imgName = substr($profileImage, strpos($profileImage, '/') + 1, strlen($profileImage));
                    $file = new UploadedFile($profileImage, $imgName);
                    $data['profile_image'] = $this->saveToS3($file, 'profile-image/');
                }
                $companyId = Auth::user()->company_id;
                $companyData = Auth::user()->company;

                $user = User::create([
                    'company_id' => $companyId,
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'status' => 1,
                    'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'profile_image' => isset($data['profile_image']) ? $data['profile_image'] : ''
                ]);
                $emailData = ['email' => $data['email'], 'password' => $data['password'], 'company_name' => $companyData->company_name];
                app('App\Http\Controllers\Admin\CompanyDetailsController')->sendResetPwMail($emailData);
                $user->assignRole('builder');
                return redirect()->back()->with(['user_created' => true]);
            } catch (\Exception $e) {
                Log::info('NEW_BUILDER_CREATE_ERROR:' . $e->getMessage());
                return redirect()->back();
            }
        }
    }

    public function reportBuilder(Request $request)
    {
        $builderUser = $request->id;
        $reason = $request->reason;
        $companyId = Auth::user()->company_id;
        $user = User::where('id', $builderUser)->where('company_id', $companyId)->first();
        if ($user) {
            $data = [
                'reported_user' => $user->firstname . ' ' . $user->lastname,
                'user' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'reason' => $reason,
                'company_name' => Auth::user()->company->company_name
            ];
            $adminEmail = User::whereId(1)->pluck('email')->first();
            Mail::to($adminEmail)->send(new ReportUserMail($data));
            return $this->jSonFormatSuccessResponse(['status' => 'success']);
        } else {
            return $this->jSonFormatFailureResponse('error');
        }
    }

    public function getCompanyUsers()
    {
        if (auth()->user()->roles[0]['name'] === 'builder') {
            $user = auth()->user();
            $company = CompanyDetails::find($user->company_id);

            // get company users
            $companyUsers = User::select('id', 'firstname', 'lastname', 'profile_image', 'email')
                ->where('company_id', $user->company_id)
                ->where('id', '!=', $user->id)
                ->where('email_verified_at', '!=', null)
                ->get();
            // end

            return view('builder-portal.company-users', compact('user',
                'company',
                'companyUsers'));
        }
        return redirect('/');
    }

    public function updatePropertyLogo(Request $request)
    {
        $data = $request->all();
        if ($data['id'] && $request->hasFile('logo')) {
            $data['logo'] = $this->saveToS3($request->file('logo'), $this->PROPERTY_LOGO_STORAGE_PATH);
            $property = Properties::find($data['id']);
            $property->update($data);
        }
        return redirect()->back();
    }
}
