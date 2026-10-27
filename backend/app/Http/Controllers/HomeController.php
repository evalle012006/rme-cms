<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use App\Models\Properties;
use App\Models\PropertyCategory;
use App\Models\Review;
use App\Models\ReviewStates;
use App\Models\Suburbs;
use App\Models\Tags;
use App\Models\User;
use App\Services\ResendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->user() && auth()->user()->hasRole('admin')) {
            auth()->logout();
            return redirect()->refresh();
        }

        $recent_blogs = Blogs::with('blogCategories')->where('status', 0)->latest()->take(4)->get()->toArray();
        $recent_reviews = Review::with('reviewTags:id,name',
            'ratings',
            'property:id,project_name,developer_name,slug,logo',
            'property.company:id,company_name,logo',
            'users:id,firstname,lastname,email,display_name,profile_image')
            ->where('user_id', '<>', Auth::id())
            ->where('status', 1)
            ->latest()
            ->take(3)
            ->get()
            ->transform(function ($item) {
                $avg = 0;
                foreach ($item->ratings as $rating) {
                    $avg += $rating->rate;
                }
                $item->avg_rating = roundOffRating($avg / count($item->ratings));
                return $item;
            })
            ->toArray();
        $property_category = PropertyCategory::get()->toArray();

        $mapData = $this->getMapData();
        $groupedReviews = $mapData['grouped_reviews'];
        $suburbsLocations = $mapData['suburbs'];

        return view('welcome',
            compact('recent_blogs', 'recent_reviews', 'property_category', 'groupedReviews', 'suburbsLocations'));
    }

    // returns suburb locations and grouped reviews
    public function getMapData()
    {
        $reviews = Review::select('id', 'property_id', 'title', 'post_code', 'suburb')
            ->where('status', 1);

        // get array of suburb and postcode of reviews
        $suburbAndPostcode = $reviews->pluck('suburb', 'post_code')->toArray();
        // end

        // reviews group by suburb
        $groupedReviews = $reviews->with([
            'ratings', 'property' => function ($qry) {
                $qry->select('id', 'project_name', 'developer_name', 'lat', 'long', 'slug', 'logo')
                    ->with([
                        'company' => function ($q) {
                            $q->select('id', 'company_name', 'logo', 'slug');
                        }
                    ]);
            }
        ])
            ->get()
            ->groupBy('suburb')
//        dd($groupedReviews->toArray());
            ->transform(function ($item) {
                $ratingWithCompany = [];
                $ratingWithProperty = [];
                $propertyReviews = [];
                foreach ($item as $singleReview) {
                    $totalRating = 0;
                    foreach ($singleReview->ratings as $rating) {
                        $totalRating += $rating->rate;
                    }
                    $propertyName = $singleReview->property->project_name;
                    if ($singleReview->property->company) {

                        $companyName = $singleReview->property->company->company_name;
                        if (!isset($ratingWithCompany[$companyName])) {
                            $ratingWithCompany[$companyName] = [];
                        }
                        $ratingWithCompany[$companyName][] = round($totalRating / count($singleReview->ratings));
                    }


                    if (!isset($ratingWithProperty[$propertyName])) {
                        $ratingWithProperty[$propertyName] = [
                            'data' => $singleReview->property->toArray(),
                            'rating' => [round($totalRating / count($singleReview->ratings))]
                        ];
                    } else {
                        $ratingWithProperty[$propertyName]['rating'][] = round($totalRating / count($singleReview->ratings));
                    }
                    $propertyReviews[$propertyName][] = $singleReview->toArray();
                }

                foreach ($ratingWithCompany as $key => $singleRating) {
                    $ratingWithCompany[$key] = roundOffRating(array_sum($singleRating) / count($singleRating));
                }

                foreach ($ratingWithProperty as $key => $singleRating) {
                    $ratingWithProperty[$key]['rating'] = roundOffRating(array_sum($singleRating['rating']) / count($singleRating['rating']));
                    $ratingWithProperty[$key]['last_reviews'] = $propertyReviews[$key];
                }

                $item['company_ratings'] = $ratingWithCompany;
                $item['property_ratings'] = $ratingWithProperty;
                return $item;
            })
            ->toArray();
        // end

        $suburbsLocations = [];
        if (count($suburbAndPostcode) > 0) {
            // get lat long of suburbs
            $suburbsLocations = Suburbs::select('name', 'latitude', 'longitude');
            foreach ($suburbAndPostcode as $postCode => $suburb) {
                $suburbsLocations->orWhere(function ($qry) use ($postCode, $suburb) {
                    $qry->where('name', $suburb)
                        ->where('postcode', $postCode);
                });
            }
            $suburbsLocations = $suburbsLocations
                ->get()
                ->groupBy('name')
                ->toArray();
            // end
        }
        return [
            'grouped_reviews' => $groupedReviews,
            'suburbs' => $suburbsLocations
        ];
    }

    public function aboutPage()
    {
        $recent_reviews = Review::with('reviewTags:id,name',
            'ratings',
            'property:id,project_name,developer_name,slug,logo',
            'property.company:id,company_name,logo',
            'users:id,firstname,lastname,email,display_name,profile_image')
            ->where('user_id', '<>', Auth::id())
            ->where('status', 1)
            ->latest()
            ->take(3)
            ->get()
            ->transform(function ($item) {
                $avg = 0;
                foreach ($item->ratings as $rating) {
                    $avg += $rating->rate;
                }
                $item->avg_rating = roundOffRating($avg / count($item->ratings));
                return $item;
            })
            ->toArray();
//        $recent_reviews = Review::with('reviewTags:id,name', 'property:id,project_name,developer_name,logo',
//            'property.company:id,company_name', 'users:id,firstname,lastname,email')->where('user_id', '<>',
//            Auth::id())->where('status', 1)->latest()->take(3)->get()->toArray();
        return view('about', compact('recent_reviews'));
    }

    public function contactPage()
    {
        return view('contact');
    }


    public function myProfile()
    {
        $user = Auth::user();
        return view('profile.my-profile', compact('user'));
    }

    public function myReviews(Request $request)
    {
        $search_key = $request->get('q');
        $user = Auth::user();

        $user_review = Review::with(
            'property:id,developer_name,project_name,logo,slug',
            'property.company:id,company_name,logo,slug', 'reviewTags:name', 'ratings:review_id,rate')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'DESC');
//            ->where('status', 1);

        if ($search_key) {
            $user_review = $user_review->whereHas('property',
                function ($query) use ($search_key) {
                    $query->where('project_name', 'LIKE', '%' . $search_key . '%');
                }
            );
        }
        $user_review = $user_review->paginate(6)->appends(request()->query());
        $user_review->getCollection()->transform(function ($item) {
            $totalRating = 0;
            foreach ($item->ratings as $rating) {
                $totalRating += $rating->rate;
            }
            $item->avg_rating = roundOffRating($totalRating / count($item->ratings));
            return $item;
        });
        if ($request->ajax()) {
            return view('partials.my-review-list', compact('user_review'));
        }

        return view('profile.my-review', compact('user', 'user_review'));
    }

    public function changeProfileImage(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'profile_image' => 'nullable'
        ]);
        $user = User::find($request->id);
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->saveToS3($request->file('profile_image'), $this->PROFILE_STORAGE_PATH);

            if (!empty($user->profile_image)) {
                Storage::delete($user->profile_image);
            }
        }
        $updated = $user->update($data);
        if ($updated) {
            return response()->json([], 200);
        }
        return response()->json('error', 500);
    }

    public function updateProfile(Request $request)
    {
        $data = request()->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email,' . $request->id,
//            'mobile' => 'required|unique:users,mobile,' . $request->id,
//            'mobile' => 'nullable|min:10|max:10|numeric',
//            'mobile' => 'sometimes|required|regex:/[0-9]{10}/',
//            'mobile' => 'nullable|digits:10|unique:users,mobile,'.$request->id,
            'mobile' => 'nullable|unique:users,mobile,' . $request->id,
//            'location' => 'required',
            'location' => 'nullable',
            'profile_image' => 'nullable'
        ], ['mobile.digits' => 'Phone number is not valid.']);

        $user = User::find($request->id);
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->saveToS3($request->file('profile_image'), $this->PROFILE_STORAGE_PATH);

            if (!empty($user->profile_image)) {
                Storage::delete($user->profile_image);
            }
        }

        $user->update($data);
        return Redirect::to("my-profile")->with('profile-update', 'Great! Profile Updated Successfully.');
    }

    public function changeRequest(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'display_name' => 'required'
        ]);
        $user = User::find($data['id']);
        if ($user->has_change_request === 0) {
            if ($user->display_name === $data['display_name']) {
                return Redirect::to("my-profile")->with('request_placed_error',
                    'You must enter different display name.');
            }
            $user->has_change_request = 1;
            $user->requested_display_name = $data['display_name'];
            $user->save();
            // send mail to admin
            $adminEmail = config('mail.contact_to');
            $details = [
                'display_name' => $user->display_name,
                'full_name' => $user->firstname . ' ' . $user->lastname,
                'requested_display_name' => $user->requested_display_name,
            ];
            if ($adminEmail) {
                app(ResendService::class)->send(
                    $adminEmail,
                    'Display name change request',
                    'email-template.change-request-admin',
                    [
                        'details' => $details,
                        'adminUrl' => cmsAdminUrl('/users?request=' . $user->id),
                    ]
                );
            }
            return Redirect::to("my-profile")->with('request_placed',
                'Great! Request has been placed to change display name.');
        }
        return Redirect::to("my-profile")->with('request_placed_error',
            'You have already placed request for new display name.');
    }

    public function fetchTags(Request $request)
    {
//        if (!empty($request->get('q'))) {
        $data = Tags::selectRaw('id, name as text')
            ->where('is_active', 1)
            ->where('name', 'LIKE', "%" . $request->get('q') . "%")
            ->get();
//        }
        return [
            'results' => $data,
        ];
    }

    public function fetchReviewStates(Request $request)
    {
//        if (!empty($request->get('q'))) {
        $data = ReviewStates::selectRaw('id, name as text')
            ->where('name', 'LIKE', "%" . $request->get('q') . "%")
            ->get();
//        }
        return [
            'results' => $data,
        ];
    }

    public function fetchSuburbs(Request $request)
    {
        $postCode = $request->postcode;
        $search = $request->q;

        if (!empty($postCode)) {
            $data = Suburbs::selectRaw('name as text')
                ->where('postcode', $postCode)
                ->where('name', 'LIKE', '%' . $search . '%')
                ->get()->toArray();

            $res = array_map(function ($item) {
                $item['id'] = $item['text'];
                return $item;
            }, $data);

            return [
                'results' => $res
            ];
        }
        return [];
    }

    public function fetchSuburbsByStateWithPostcode(Request $request)
    {
        $stateId = $request->state_id;

        if (empty($stateId)) {
            return ['results' => []];
        }

        $state = ReviewStates::select('id', 'name')->find($stateId);
        if (!$state) {
            return ['results' => []];
        }

        $query = Suburbs::where('state', $state->name)
            ->select('name', 'postcode')
            ->distinct()
            ->orderBy('name')
            ->orderBy('postcode');

        $search = $request->q;
        if (!empty($search)) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $rows = $query->get();
        $nameCounts = $rows->groupBy('name')->map->count();

        $results = $rows->map(function ($row) use ($nameCounts) {
            $name = $row->name;
            $postcode = (string) $row->postcode;
            $duplicate = ($nameCounts[$name] ?? 0) > 1;

            return [
                'id' => $duplicate ? $name . '|' . $postcode : $name,
                'text' => $duplicate ? $name . ' (' . $postcode . ')' : $name,
                'name' => $name,
                'postcode' => $postcode,
            ];
        })->values();

        return ['results' => $results];
    }

    public function autocompleteSuburb(Request $request)
    {
        $search = $request->search;
        $data = Suburbs::selectRaw('name as text')
            ->where('name', 'LIKE', '%' . $search . '%')
            ->distinct('text')
            ->get()
            ->take(30)
            ->toArray();

        return [
            'results' => $data
        ];
    }

    public function autocompletePropertySuburb(Request $request)
    {
        $search = $request->search;
        $data = Suburbs::selectRaw('name')
            ->where('name', 'LIKE', '%' . $search . '%')
            ->distinct('text')
            ->get()
            ->take(30)
            ->transform(function ($item) {
                return [
                    'id' => $item->name,
                    'text' => $item->name
                ];
            });

        return [
            'results' => $data
        ];
    }

}
