<?php

namespace App\Http\Controllers;

use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyAvailableCategory;
use App\Models\PropertyCategory;
use App\Models\Rating;
use App\Models\RatingConfiguration;
use App\Models\Review;
use App\Models\ReviewTags;
use App\Models\SelectMaster;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends BaseController
{
    public function reviewListing()
    {
        $recent_reviews = Review::with('reviewTags:id,name', 'property:id,project_name,developer_name', 'property.company:id,company_name', 'users:id,firstname,lastname,email')
            ->where('user_id', '<>', Auth::id())
            ->where('status', 1)
            ->latest()->take(12)
            ->get()->toArray();
        return view('review-listing', compact('recent_reviews'));
    }

    public function reviewStepOnePage()
    {
        $persistStep1Data = \request()->session()->get('persistData');
        \request()->session()->forget('persistData');
        if (!$persistStep1Data) {
            $step1Fields = [
                'review-company-id',
                'review-property-id',
                'review-year-complete',
                'buyer-type',
                'review-category',
                'review-state',
                'review-post-code',
                'review-suburb'
            ];
            $removeItems = [
                'review-image',
                'review-image-system',
                'review-docs',
                'review-docs-system'
            ];
            $this->removeStep2SessionData(array_merge($step1Fields, $removeItems));
        }
        $property_category = PropertyCategory::get()->toArray();
        $buyertype = SelectMaster::with('optionSelect')->where('id', 1)->first()->toArray();
        return view('review-step.review-step1', compact('property_category', 'buyertype'));
    }

    public function reviewStepTwoPage(Request $request)
    {
        if (!$request->session()->exists('guest-login')) {
            if (!old('_token')) {
                $this->removeStep2SessionData(['review-image', 'review-image-system', 'review-docs', 'review-docs-system']);
            } else {
                $this->removeStep2SessionData();
            }
        }
        // dont clear form step 2 data when previous url is review-step-2 or when guest-user is set in session

        $property_id = \request()->session()->get('review-property-id');
        $review_cat = \request()->session()->get('review-category');

//        if (Auth::user()) {
        if ($property_id && $review_cat) {

            //new flow with step 1 cat selected
            if ($review_cat != 2) {
                $cat = PropertyCategory::with(['propertyAllowedRating' => function ($qry) {
                    $qry->orderBy('name', 'ASC');
                }])
                    ->whereHas('propertyAllowedRating', function ($q) use ($review_cat) {
                        $q->where('property_category_id', $review_cat);
                    })->first();
                $propertyRatings = $cat->toArray();
            } else {
                //homebuilder cat id for home and land question
                $homebuilder_id = 5;
                $cat = PropertyCategory::with(['propertyAllowedRating' => function ($qry) {
                    $qry->orderBy('name', 'ASC');
                }])
                    ->whereHas('propertyAllowedRating', function ($q) use ($homebuilder_id) {
                        $q->where('property_category_id', $homebuilder_id);
                    })->first();
                $propertyRatings = $cat->toArray();
            }

//            usort($propertyRatings['property_allowed_rating'], function ($a, $b) {
//                return $a['pivot']['order'] > $b['pivot']['order'] ? 1 : -1;
//            });

            $request->session()->forget('step2');
            //allow rating
            return view('review-step.review-step2', compact('propertyRatings', 'review_cat'));
        }
        return redirect()->route('review-step-1');
    }

    public function reviewStepTwoHomeLand(Request $request)
    {
        if (\request()->session()->get('review-dev-page')) {
            $homesite_id = 1;
            $cat = PropertyCategory::with('propertyAllowedRating')->whereHas('propertyAllowedRating', function ($q) use ($homesite_id) {
                $q->where('property_category_id', $homesite_id);
            })->first();
            $propertyRatings = $cat->toArray();
            $buyertype = SelectMaster::with('optionSelect')->where('id', 1)->first()->toArray();
            $request->session()->forget('step2-1');
            return view('review-step.review-step2-1', compact('propertyRatings', 'buyertype'));
        }
        return redirect()->route('review-step-1');
    }

    public function reviewStepThreePage()
    {
        if (\request()->session()->get('step2') || \request()->session()->get('step2-1')) {
            return view('review-step.review-step3');
        }
        return redirect()->route('review-step-1');
    }

    public function saveReviewStepOne(Request $request)
    {
        $data = request()->validate([
            'project_id' => 'required',
            'buyer_type' => 'required',
            'categories' => 'required',
            'company_id' => 'nullable',
            'year_of_completion' => 'nullable|numeric',
            'state' => 'required',
            'post_code' => 'required',
            'suburb' => 'required',
        ], ['project_id.required' => 'Project/Estate Name is required']);

        if (isset($data['company_id'])) {
            $request->session()->put('review-company-id', $data['company_id']);
        }
        $request->session()->put('review-property-id', $data['project_id']);
        $request->session()->put('review-year-complete', $data['year_of_completion']);
        $request->session()->put('buyer-type', $data['buyer_type']);
        $request->session()->put('review-category', $data['categories']);
        $request->session()->put('review-state', $data['state']);
        $request->session()->put('review-post-code', $data['post_code']);
        $request->session()->put('review-suburb', $data['suburb']);

        if ($request->session()->has('review-property-id') && $request->session()->has('buyer-type')
            && $request->session()->has('review-category')) {

            return redirect()->route('review-step-2');
        }
        return redirect()->back();
    }

    public function removeStep2SessionData($additionalKeys = [])
    {
        $keys = [
            'review-title',
            'review-recommendation',
            'review-is-agree',
            'review-comment',
            'review-tags',
            'review-rating'
        ];
        Session::forget(array_merge($additionalKeys, $keys));
    }

    public function saveReviewStepTwo(Request $request)
    {
        if ($request->review_image) {
            $name = $request->review_image->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->review_image, $name);
            $imagePath = url('/') . '/review-form-media/' . $name;
            $request->session()->put('review-image', $imagePath);
            $request->session()->put('review-image-system', public_path('review-form-media/' . $name));
        }

        if ($request->review_docs) {
            $name = $request->review_docs->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->review_docs, $name);
            $imagePath = url('/') . '/review-form-media/' . $name;
            $request->session()->put('review-docs', $imagePath);
            $request->session()->put('review-docs-system', public_path('review-form-media/' . $name));
        }

        $rules = [
            'title' => 'required',
            'recommendation' => 'required',
            'property_id' => 'nullable',
            'comment' => 'nullable',
            'tags' => 'required|array',
            'rating' => 'required|array',
            'is_agree' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        $errors = $validator->errors();
        if (!$request->session()->exists('review-docs')) {
            $errors->add('review_docs', 'The review docs field is required.');
        }
        if (!empty($errors->messages())) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $data = $request->all();

        $request->session()->put('review-title', $data['title']);
        $request->session()->put('review-recommendation', $data['recommendation']);
        $request->session()->put('review-is-agree', $data['is_agree']);
        $request->session()->put('review-comment', $data['comment']);
        $request->session()->put('review-tags', $data['tags']);
        $request->session()->put('review-rating', $data['rating']);

        if ($request->submit == 'submit') {
            if (Auth::user()) {
                $data['user_id'] = Auth::id();

                if ($request->session()->has('review-state')) {
                    $data['state'] = \request()->session()->get('review-state');
                }

                if ($request->session()->has('review-post-code')) {
                    $data['post_code'] = \request()->session()->get('review-post-code');
                }

                if ($request->session()->has('review-suburb')) {
                    $data['suburb'] = \request()->session()->get('review-suburb');
                }

                if ($request->session()->has('review-year-complete')) {
                    $data['year_of_completion'] = \request()->session()->get('review-year-complete');
                }
                if ($request->session()->has('buyer-type')) {
                    $data['buyer_type'] = \request()->session()->get('buyer-type');
                }
                if ($request->session()->has('review-category')) {
                    $data['category_id'] = \request()->session()->get('review-category');
                }
                if ($request->session()->exists('review-image')) {
                    $sessionImg = $request->session()->get('review-image-system');
                    $imgName = substr($sessionImg, strpos($sessionImg, '/') + 1, strlen($sessionImg));
                    $file = new UploadedFile($sessionImg, $imgName);
//                    $file = file_get_contents($request->session()->get('review-image'));
                    $data['review_image'] = $this->saveToS3($file, self::REVIEW_STORAGE_PATH);
                }

                if ($request->session()->exists('review-docs')) {
                    $sessionDoc = $request->session()->get('review-docs-system');
                    $docName = substr($sessionDoc, strpos($sessionDoc, '/') + 1, strlen($sessionDoc));
                    $file = new UploadedFile($sessionDoc, $docName);
//                    $file = file_get_contents($request->session()->get('review-docs'));
                    $data['review_docs'] = $this->saveToS3($file, self::REVIEWDOCS_STORAGE_PATH);
                }

                if ($review_id = Review::create($data)) {
                    if (count($data['tags']) > 0) {
                        $reviewTags = [];
                        foreach ($data['tags'] as $tags) {
                            $valueToIns = [
                                'review_id' => $review_id->id,
                                'tag_id' => $tags
                            ];
                            array_push($reviewTags, $valueToIns);
                        }
                        ReviewTags::insert($reviewTags);
                    }

                    if (!empty($data['rating'])) {
                        $rateQuestion = [];
                        foreach ($data['rating'] as $k_q => $v_q) {
                            $questionIns = [
                                'review_id' => $review_id->id,
                                'rating_conf_id' => $k_q,
                                'rate' => $v_q
                            ];
                            array_push($rateQuestion, $questionIns);
                        }
                        Rating::insert($rateQuestion);
                    }
                    $request->session()->forget('review-property-id');
                    $request->session()->forget('review-category');
                    $request->session()->forget('review-year-complete');
                    $request->session()->forget('review-state');
                    $request->session()->forget('review-post-code');
                    $request->session()->forget('review-suburb');
                    $request->session()->forget('review-title');
                    $request->session()->forget('review-is-agree');
                    $request->session()->forget('review-comment');
                    $request->session()->forget('review-recommendation');
                    $request->session()->forget('guest-user');

                    $request->session()->put('step2', true);

                    $categoryData = PropertyCategory::find($data['category_id']);
                    $recommendedCategory = '';
                    if (array_search($categoryData->slug, ['homesite', 'home']) !== false) {
                        $recommendedCategory = ($categoryData->slug == 'homesite' ? 'home' : 'homesite');
                        $request->session()->put('recommended-review-category', $recommendedCategory);
                    } else {
                        $request->session()->forget('recommended-review-category');
                    }


                    $this->sendMailUponReviewCreation($review_id->id, $recommendedCategory);
                    // send email
//                    \Mail::to(Auth::user()->email)->send(new \App\Mail\PendingReviewMail());
//                    // end
//
//                    // send email to admin
//                    $viewData = [
//                        'property_name' => $reviewData['property']['project_name'],
//                        'full_name' => $reviewData['user']['firstname'] . ' ' . $reviewData['user']['lastname'],
//                        'display_name' => $reviewData['user']['display_name']
//                    ];
//                    \Mail::to(Auth::user()->email)->send(new \App\Mail\ReviewSubmitted($viewData));
                    // end
                    return redirect()->route('review-step-3');
                }
            }
            $request->session()->put('guest-login', true);
            return redirect()->route('review-step-2')->with('error', 'Login Required Before Review');
        } else {
            if (Auth::user()) {

                if ($request->hasFile('review_image')) {
                    $file = $request->file('review_image');
                    $temp_review_image = $this->saveToS3($file, self::REVIEW_TEMP_STORAGE_PATH);
                    $request->session()->put('review-image', $temp_review_image);
                }

                if ($request->hasFile('review_docs')) {
                    $file = $request->file('review_docs');
                    $temp_review_docs = $this->saveToS3($file, self::REVIEWDOCS_TEMP_STORAGE_PATH);
                    $request->session()->put('review-docs', $temp_review_docs);
                }

                $request->session()->put('review-dev-page', true);
                return redirect()->route('review-step-2-1');
            }
            return redirect()->route('review-step-2')->with('error', 'Login Required Before Review');
        }
    }

    public function sendMailUponReviewCreation($reviewId, $suggestedCategory)
    {
        try {
            $reviewData = Review::select('id', 'property_id', 'title', 'user_id')
                ->whereId($reviewId)
                ->with('property:id,project_name', 'users')
                ->first()
                ->toArray();

            $adminEmail = User::whereId(1)->pluck('email')->first();
            // send email
            \Mail::to($reviewData['users']['email'])->send(new \App\Mail\PendingReviewMail($suggestedCategory));
            // end

            // send email to admin
            $viewData = [
                'property_name' => $reviewData['property']['project_name'],
                'full_name' => $reviewData['users']['firstname'] . ' ' . $reviewData['users']['lastname'],
                'display_name' => $reviewData['users']['display_name']
            ];
            \Mail::to($adminEmail)->send(new \App\Mail\ReviewSubmitted($viewData));
            // end
        } catch (\Exception $e) {
            Log::info('EMAIL_ERROR : ' . $e->getMessage());
        }
    }

    public function saveReviewStepTwoHomeLand(Request $request)
    {

        $data = request()->validate([
            'title' => 'required',
            'recommendation' => 'required',
            'property_id' => 'required',
            'buyer_type' => 'required',
            'comment' => 'nullable',
            'year_of_completion' => 'nullable',
            'tags' => 'required|array',
            'rating' => 'required|array',
            'review_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'review_docs' => 'required',
            'is_agree' => 'required',
            'state' => 'required',
            'post_code' => 'required',
            'suburb' => 'required',
        ], ['property_id.required' => 'Project/Estate Name is required']);


        if (Auth::user()) {
            $session_data = session()->all();
            $review_parent_id = $this->saveReviewForParent($session_data);

            $data['user_id'] = Auth::id();
            $data['parent_id'] = $review_parent_id;
            $data['category_id'] = $session_data['review-category'];

            if ($request->hasFile('review_image')) {
                $file = $request->file('review_image');
                $data['review_image'] = $this->saveToS3($file, self::REVIEW_STORAGE_PATH);
            }

            if ($request->hasFile('review_docs')) {
                $file = $request->file('review_docs');
                $data['review_docs'] = $this->saveToS3($file, self::REVIEWDOCS_STORAGE_PATH);
            }

            if ($review_id = Review::create($data)) {
                if (count($data['tags']) > 0) {
                    $reviewTags = [];
                    foreach ($data['tags'] as $tags) {
                        $valueToIns = [
                            'review_id' => $review_id->id,
                            'parent_id' => $review_parent_id,
                            'tag_id' => $tags
                        ];
                        array_push($reviewTags, $valueToIns);
                    }
                    ReviewTags::insert($reviewTags);
                }

                if (!empty($data['rating'])) {
                    $rateQuestion = [];
                    foreach ($data['rating'] as $k_q => $v_q) {
                        $questionIns = [
                            'review_id' => $review_id->id,
                            'parent_id' => $review_parent_id,
                            'rating_conf_id' => $k_q,
                            'rate' => $v_q
                        ];
                        array_push($rateQuestion, $questionIns);
                    }
                    Rating::insert($rateQuestion);
                }
                $request->session()->forget('review-property-id');
                $request->session()->forget('review-category');
                $request->session()->forget('review-year-complete');
                $request->session()->forget('review-state');
                $request->session()->forget('review-post-code');
                $request->session()->forget('review-suburb');
                $request->session()->forget('review-title');
                $request->session()->forget('review-is-agree');
                $request->session()->forget('review-comment');
                $request->session()->forget('review-recommendation');
                $request->session()->forget('review-image');
                $request->session()->forget('review-docs');
                $request->session()->forget('review-dev-page');

                $request->session()->put('step2-1', true);
                return redirect()->route('review-step-3');
            }
            return redirect()->route('review-step-2')->with('error', 'Login Required Before Review');
        }


//        return redirect()->route('review-step-1')->with('error', 'Login Required Before Review');


    }

    public function saveReviewForParent($session_data)
    {
        $data = [];
        $data['user_id'] = Auth::id();
        $data['property_id'] = $session_data['review-property-id'];
        $data['category_id'] = $session_data['review-category'];
        $data['recommendation'] = $session_data['review-recommendation'];
        $data['title'] = $session_data['review-title'];
        $data['comment'] = $session_data['review-comment'];
        $data['review_image'] = $session_data['review-image'] ?? '';
        $data['review_docs'] = $session_data['review-docs'];
        $data['buyer_type'] = $session_data['buyer-type'];
        $data['buyer_type'] = $session_data['buyer-type'];
        $data['state'] = $session_data['review-state'];
        $data['post_code'] = $session_data['review-post-code'];
        $data['suburb'] = $session_data['review-suburb'];
        $data['year_of_completion'] = $session_data['review-year-complete'];

        $review = Review::create($data);

        if (count($session_data['review-tags']) > 0) {
            $reviewTags = [];
            foreach ($session_data['review-tags'] as $tags) {
                $valueToIns = [
                    'review_id' => $review->id,
                    'tag_id' => $tags
                ];
                array_push($reviewTags, $valueToIns);
            }
            ReviewTags::insert($reviewTags);
        }

        if (!empty($session_data['review-rating'])) {
            $rateQuestion = [];
            foreach ($session_data['review-rating'] as $k_q => $v_q) {
                $questionIns = [
                    'review_id' => $review->id,
                    'rating_conf_id' => $k_q,
                    'rate' => $v_q
                ];
                array_push($rateQuestion, $questionIns);
            }
            Rating::insert($rateQuestion);
        }
        return $review->id;

    }

    public function getReviewBySlug($slug)
    {
        $review = Review::with('property:id,project_name,developer_name', 'property.company:id,company_name', 'users', 'ratings', 'reviewTags')
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();
        $user = $review->toArray();
        $expiresAt = now()->addHours(3);
        if (Auth::id() !== $user['users']['id']) {
            views($review)
                ->delayInSession($expiresAt)
                ->record();
        }
        $sum = 0;
        foreach ($user['ratings'] as $k => $v) {
            $sum += $v['rate'];
        }
        $user['overall_rating'] = roundOffRating($sum / count($user['ratings']));

        return view('review-detail', compact('review', 'user'));
    }

    public function reviewStepRedirect($slug)
    {
//        if (Auth::user()) {
        if ($slug) {
            $review = Properties::where('slug', $slug)->first();

            \request()->session()->put('review-property-id', $review->property_id);
            return redirect()->route('review-step-2');
        }
        return redirect()->back();
//        }
//        return redirect()->back()->with('error', 'Login Required Before Review');
    }

    public function homeCatReview(Request $request)
    {
        \request()->session()->put('category_id', $request->get('categories'));
        return redirect()->route('review-step-1');
    }

    public function saveCompanyByReview(Request $request)
    {
        $data = request()->validate([
            'company_name' => 'required|unique:company_details,company_name',
        ]);

        if ($data['company_name']) {
            $data['is_from'] = 'frontend';
            $data['status'] = 0;
            $company = CompanyDetails::create($data);
            $response = [
                'status' => 'success',
                'id' => $company->id,
                'text' => $company->company_name
            ];
            return $this->jSonFormatSuccessResponse($response);
        }
        return $this->jSonFormatFailureResponse('company name not found');
    }

    public function savePropertyByReview(Request $request)
    {
        $data = request()->validate([
            'project_name' => 'required',
            'company_id' => 'required',
        ]);
        $data['developer_name'] = $data['company_id'];
        $data['is_from'] = 'frontend';
        $property = Properties::create($data);

        $property_cat = PropertyCategory::get()->toArray();

        $valIns = [];
        foreach ($property_cat as $cats) {
            array_push($valIns, [
                'property_id' => $property->id,
                'property_category_id' => $cats['id']
            ]);
        }
        PropertyAvailableCategory::insert($valIns);

        $response = [
            'status' => 'success',
            'id' => $property->id,
            'text' => $property->project_name
        ];

        return $this->jSonFormatSuccessResponse($response);
    }

    public function navReviewStepOne(Request $request)
    {
        $request->session()->put('persistData', true);
        return redirect()->route('review-step-1');
    }

    public function updateReview(Request $request)
    {
        $rules = [
            'id' => 'required',
            'title' => 'required',
            'tags' => 'required|array'
        ];
        $validator = Validator::make($request->all(), $rules);
        if (!$validator->fails()) {
            $data = [
                'title' => $request->title,
                'comment' => $request->comment
            ];

            $review = Review::find($request->id);

            if (!$review->status) {
                $update = Review::where('id', $request->id)->update($data);
                if ($update) {
                    $tags = $request->tags;
                    $newTags = [];
                    foreach ($tags as $tag) {
                        $newTags[] = [
                            'review_id' => $request->id,
                            'tag_id' => $tag
                        ];
                    }
                    ReviewTags::where('review_id', $request->id)->delete();
                    ReviewTags::insert($newTags);
                }
            }
        }
        return redirect()->back();
    }


}
