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
use App\Models\Suburbs;
use App\Models\User;
use App\Rules\UniqueCompanyName;
use App\Services\ResendService;
use App\Support\StateResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
                'review-suburb',
                'review-locality-id'
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
        // Keep step-2 answers in session so Go Back / Next / validation redirects stay prefilled.

        $property_id = $request->session()->get('review-property-id');
        $review_cat = $request->session()->get('review-category');

        if ($property_id && $review_cat) {
            $isHomeland = $this->isHomelandCategory($review_cat);

            //new flow with step 1 cat selected
            if (!$isHomeland) {
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

            $request->session()->forget('step2');
            //allow rating
            return view('review-step.review-step2', compact('propertyRatings', 'review_cat', 'isHomeland'));
        }
        return redirect()->route('review-step-1');
    }

    protected function isHomelandCategory($reviewCat): bool
    {
        if ((int) $reviewCat === 2) {
            return true;
        }

        $category = PropertyCategory::select('id', 'slug')->find($reviewCat);

        return $category && $category->slug === 'homeland';
    }

    protected function uploadedFileFromSessionPath($path): ?UploadedFile
    {
        if (!$path || !is_file($path)) {
            return null;
        }

        return new UploadedFile($path, basename($path), null, null, true);
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
            'locality_id' => 'required|exists:suburbs,id',
        ], [
            'project_id.required' => 'Project/Estate Name is required',
            'locality_id.required' => 'Please select a State, Suburb and Postcode from the list',
            'locality_id.exists' => 'Please select a State, Suburb and Postcode from the list',
        ]);

        $data = $this->applyPropertyLocationToReviewData($data, $data['project_id']);

        $previousProperty = $request->session()->get('review-property-id');
        $previousCategory = $request->session()->get('review-category');

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
        $request->session()->put('review-locality-id', $data['locality_id']);

        if ($request->session()->has('review-property-id') && $request->session()->has('buyer-type')
            && $request->session()->has('review-category')) {
            // Only reset step-2 draft when property/category actually changed
            if ((string) $previousProperty !== (string) $data['project_id']
                || (string) $previousCategory !== (string) $data['categories']) {
                $this->removeStep2SessionData([
                    'review-image',
                    'review-image-system',
                    'review-docs',
                    'review-docs-system',
                    'review-dev-page',
                    'guest-login',
                ]);
            }

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
        $proofRequiredMessage = 'Proof of purchase is required. Please upload a document that confirms your purchase.';

        if ($request->review_image) {
            $name = $request->review_image->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->review_image, $name);
            $imagePath = url('/') . '/review-form-media/' . $name;
            $request->session()->put('review-image', $imagePath);
            $request->session()->put('review-image-system', public_path('review-form-media/' . $name));
        }

        if ($request->hasFile('review_docs')) {
            $request->validate([
                'review_docs' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,bmp,svg,webp|max:10240',
            ], [
                'review_docs.mimes' => 'Proof of purchase must be a PDF, Word document, or image.',
                'review_docs.max' => 'Proof of purchase must not be larger than 10MB.',
            ]);

            $name = $request->file('review_docs')->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->file('review_docs'), $name);
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
        $sessionDocPath = $request->session()->get('review-docs-system');
        if (!$request->session()->exists('review-docs') || !$sessionDocPath || !is_file($sessionDocPath)) {
            $errors->add('review_docs', $proofRequiredMessage);
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

        // Default to submit when Enter is pressed (button value may be omitted).
        // Accept legacy name="submit" for older form posts.
        $formAction = $request->input('form_action', $request->input('submit', 'submit'));

        if ($formAction === 'next') {
            $request->session()->put('guest-login', true);

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

        if (Auth::user()) {
            try {
                $data['user_id'] = Auth::id();

                if ($request->session()->has('review-state')) {
                    $data['state'] = $request->session()->get('review-state');
                }

                if ($request->session()->has('review-post-code')) {
                    $data['post_code'] = $request->session()->get('review-post-code');
                }

                if ($request->session()->has('review-suburb')) {
                    $data['suburb'] = $request->session()->get('review-suburb');
                }

                if ($request->session()->has('review-locality-id')) {
                    $data['locality_id'] = $request->session()->get('review-locality-id');
                }

                if ($request->session()->has('review-year-complete')) {
                    $data['year_of_completion'] = $request->session()->get('review-year-complete');
                }
                if ($request->session()->has('buyer-type')) {
                    $data['buyer_type'] = $request->session()->get('buyer-type');
                }
                if ($request->session()->has('review-category')) {
                    $data['category_id'] = $request->session()->get('review-category');
                }
                if ($request->session()->exists('review-image')) {
                    $file = $this->uploadedFileFromSessionPath($request->session()->get('review-image-system'));
                    if ($file) {
                        $data['review_image'] = $this->saveToS3($file, self::REVIEW_STORAGE_PATH);
                    }
                }

                $file = $this->uploadedFileFromSessionPath($request->session()->get('review-docs-system'));
                if (!$file) {
                    return redirect()->back()
                        ->withErrors(['review_docs' => $proofRequiredMessage])
                        ->withInput();
                }
                $data['review_docs'] = $this->saveToS3($file, self::REVIEWDOCS_STORAGE_PATH);
                $data['status'] = 0;
                if (empty($data['property_id']) && $request->session()->has('review-property-id')) {
                    $data['property_id'] = $request->session()->get('review-property-id');
                }
                $data = $this->applyPropertyLocationToReviewData(
                    $data,
                    $data['property_id'] ?? $request->session()->get('review-property-id')
                );

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
                    $request->session()->forget('review-locality-id');
                    $request->session()->forget('review-title');
                    $request->session()->forget('review-is-agree');
                    $request->session()->forget('review-comment');
                    $request->session()->forget('review-recommendation');
                    $request->session()->forget('review-tags');
                    $request->session()->forget('review-rating');
                    $request->session()->forget('guest-user');
                    $request->session()->forget('guest-login');

                    $request->session()->put('step2', true);

                    $categoryData = PropertyCategory::find($data['category_id']);
                    $recommendedCategory = '';
                    if ($categoryData && array_search($categoryData->slug, ['homesite', 'home']) !== false) {
                        $recommendedCategory = ($categoryData->slug == 'homesite' ? 'home' : 'homesite');
                        $request->session()->put('recommended-review-category', $recommendedCategory);
                    } else {
                        $request->session()->forget('recommended-review-category');
                    }

                    $this->sendMailUponReviewCreation($review_id->id, $recommendedCategory);
                    return redirect()->route('review-step-3');
                }
            } catch (\Throwable $e) {
                Log::error('REVIEW_STEP_TWO_SUBMIT_ERROR: ' . $e->getMessage());
                return redirect()->route('review-step-2')
                    ->with('error', 'Unable to submit review. Please try again.')
                    ->withInput();
            }
        }

        $request->session()->put('guest-login', true);
        return redirect()->route('review-step-2')->with('error', 'Login Required Before Review');
    }

    public function sendMailUponReviewCreation($reviewId, $suggestedCategory)
    {
        $review = Review::select('id', 'property_id', 'title', 'user_id')
            ->whereId($reviewId)
            ->with('property:id,project_name', 'users')
            ->first();

        if (!$review) {
            Log::error('REVIEW_NOTIFY_MISSING_REVIEW', ['review_id' => $reviewId]);
            return;
        }

        $reviewData = $review->toArray();
        $mailer = app(ResendService::class);
        $reviewerEmail = $reviewData['users']['email'] ?? null;

        try {
            if ($reviewerEmail) {
                $mailer->send(
                    $reviewerEmail,
                    'Thank you',
                    'email-template.pending-review',
                    ['suggestedCategory' => $suggestedCategory]
                );
            } else {
                Log::error('REVIEW_NOTIFY_MISSING_REVIEWER_EMAIL', ['review_id' => $reviewId]);
            }
        } catch (\Exception $e) {
            Log::error('REVIEW_REVIEWER_MAIL_ERROR: ' . $e->getMessage(), [
                'review_id' => $reviewId,
            ]);
        }

        $adminEmails = User::role('admin')
            ->where('status', 1)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($adminEmails->isEmpty()) {
            Log::error('REVIEW_ADMIN_MAIL_ERROR: no active admin emails found', [
                'review_id' => $reviewId,
            ]);
            return;
        }

        $user = $reviewData['users'] ?? [];
        $property = $reviewData['property'] ?? [];
        $viewData = [
            'property_name' => $property['project_name'] ?? '',
            'full_name' => trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')),
            'display_name' => $user['display_name'] ?? '',
        ];

        foreach ($adminEmails as $adminEmail) {
            try {
                $mailer->send(
                    $adminEmail,
                    'New review submitted',
                    'email-template.review-submitted',
                    ['details' => $viewData]
                );
            } catch (\Exception $e) {
                Log::error('REVIEW_ADMIN_MAIL_ERROR: ' . $e->getMessage(), [
                    'review_id' => $reviewId,
                    'admin_email' => $adminEmail,
                ]);
            }
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
            'review_docs' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,bmp,svg,webp|max:10240',
            'is_agree' => 'required',
            'state' => 'required',
            'post_code' => 'required',
            'suburb' => 'required',
            'locality_id' => 'required|exists:suburbs,id',
        ], [
            'property_id.required' => 'Project/Estate Name is required',
            'review_docs.required' => 'Proof of purchase is required. Please upload a document that confirms your purchase.',
            'review_docs.file' => 'Proof of purchase is required. Please upload a document that confirms your purchase.',
            'review_docs.mimes' => 'Proof of purchase must be a PDF, Word document, or image.',
            'review_docs.max' => 'Proof of purchase must not be larger than 10MB.',
            'locality_id.required' => 'Please select a State, Suburb and Postcode from the list',
            'locality_id.exists' => 'Please select a State, Suburb and Postcode from the list',
        ]);


        if (Auth::user()) {
            $session_data = session()->all();
            $review_parent_id = $this->saveReviewForParent($session_data);
            if (!$review_parent_id) {
                return redirect()->route('review-step-2')
                    ->withErrors(['review_docs' => 'Proof of purchase is required. Please upload a document that confirms your purchase.'])
                    ->withInput();
            }

            $data['user_id'] = Auth::id();
            $data['parent_id'] = $review_parent_id;
            $data['category_id'] = $session_data['review-category'];

            if ($request->hasFile('review_image')) {
                $file = $request->file('review_image');
                $data['review_image'] = $this->saveToS3($file, self::REVIEW_STORAGE_PATH);
            }

            $file = $request->file('review_docs');
            $data['review_docs'] = $this->saveToS3($file, self::REVIEWDOCS_STORAGE_PATH);
            $data['status'] = 0;
            $data = $this->applyPropertyLocationToReviewData($data, $data['property_id'] ?? null);

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
                $request->session()->forget('review-locality-id');
                $request->session()->forget('review-title');
                $request->session()->forget('review-is-agree');
                $request->session()->forget('review-comment');
                $request->session()->forget('review-recommendation');
                $request->session()->forget('review-image');
                $request->session()->forget('review-docs');
                $request->session()->forget('review-dev-page');

                $request->session()->put('step2-1', true);

                $categoryData = PropertyCategory::find($data['category_id']);
                $recommendedCategory = '';
                if ($categoryData && array_search($categoryData->slug, ['homesite', 'home']) !== false) {
                    $recommendedCategory = ($categoryData->slug == 'homesite' ? 'home' : 'homesite');
                }
                $this->sendMailUponReviewCreation($review_parent_id, $recommendedCategory);

                return redirect()->route('review-step-3');
            }
            return redirect()->route('review-step-2')->with('error', 'Login Required Before Review');
        }


//        return redirect()->route('review-step-1')->with('error', 'Login Required Before Review');


    }

    public function saveReviewForParent($session_data)
    {
        $file = $this->uploadedFileFromSessionPath($session_data['review-docs-system'] ?? null);
        if (!$file) {
            return null;
        }

        $data = [];
        $data['user_id'] = Auth::id();
        $data['property_id'] = $session_data['review-property-id'];
        $data['category_id'] = $session_data['review-category'];
        $data['recommendation'] = $session_data['review-recommendation'];
        $data['title'] = $session_data['review-title'];
        $data['comment'] = $session_data['review-comment'];
        $data['review_image'] = $session_data['review-image'] ?? '';
        $data['review_docs'] = $this->saveToS3($file, self::REVIEWDOCS_STORAGE_PATH);
        $data['buyer_type'] = $session_data['buyer-type'];
        $data['state'] = $session_data['review-state'];
        $data['post_code'] = $session_data['review-post-code'];
        $data['suburb'] = $session_data['review-suburb'];
        $data['locality_id'] = $session_data['review-locality-id'] ?? null;
        $data['year_of_completion'] = $session_data['review-year-complete'];
        $data['status'] = 0;
        $data = $this->applyPropertyLocationToReviewData($data, $data['property_id'] ?? null);

        if (!empty($session_data['review-image-system'])) {
            $imageFile = $this->uploadedFileFromSessionPath($session_data['review-image-system']);
            if ($imageFile) {
                $data['review_image'] = $this->saveToS3($imageFile, self::REVIEW_STORAGE_PATH);
            }
        }

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
        try {
            $data = $request->validate([
                'company_name' => ['required', 'string', new UniqueCompanyName()],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failure',
                'message' => collect($e->errors())->flatten()->first()
                    ?: 'A company with this name already exists. Please select it from the list instead of creating a duplicate.',
                'errors' => $e->errors(),
            ], 422);
        }

        $data['company_name'] = trim($data['company_name']);
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

    /**
     * When the selected property has a location, persist that location on
     * the review (state as review_states.id). Otherwise keep the posted
     * values and still coerce state to an id when possible.
     *
     * Either way the result is passed through the locality guard, so the stored
     * text always matches the referenced locality record.
     */
    private function applyPropertyLocationToReviewData(array $data, $propertyId)
    {
        $property = $propertyId ? Properties::find($propertyId) : null;
        if ($property && ($property->state || $property->suburb || $property->post_code)) {
            $stateId = StateResolver::id($property->state);
            if ($stateId) {
                $data['state'] = $stateId;
            }
            if ($property->suburb) {
                $data['suburb'] = $property->suburb;
            }
            if ($property->post_code) {
                $data['post_code'] = $property->post_code;
            }

            // The property's location wins, so re-resolve the locality to match
            // it rather than trusting the locality_id the form posted. Legacy
            // properties hold free text, so this can fail to resolve — keep the
            // posted locality in that case rather than blocking the submission.
            $resolved = $this->resolveLocalityId(
                $data['suburb'] ?? null,
                $data['post_code'] ?? null,
                $data['state'] ?? null
            );

            if ($resolved) {
                $data['locality_id'] = $resolved;
            }

            return $this->applyLocalityToReviewData($data);
        }

        $stateId = StateResolver::id($data['state'] ?? null);
        if ($stateId) {
            $data['state'] = $stateId;
        }

        return $this->applyLocalityToReviewData($data);
    }

    /**
     * FR-7 guard. The submitted combination must resolve to a single locality;
     * the stored state, suburb and postcode are then re-derived from that row so
     * the legacy text columns can never drift out of sync or be spoofed.
     *
     * @throws ValidationException when the combination does not resolve
     */
    private function applyLocalityToReviewData(array $data)
    {
        $locality = empty($data['locality_id'])
            ? null
            : Suburbs::active()->find($data['locality_id']);

        if (!$locality) {
            throw ValidationException::withMessages([
                'locality_id' => 'Please select a State, Suburb and Postcode from the list',
            ]);
        }

        $stateId = StateResolver::id($locality->state_code);

        $data['locality_id'] = (int) $locality->id;
        $data['suburb'] = $locality->name;
        $data['post_code'] = (string) $locality->postcode;
        if ($stateId) {
            $data['state'] = $stateId;
        }

        return $data;
    }

    /**
     * Single locality matching a suburb, postcode and state, or null when the
     * combination is missing or ambiguous.
     *
     * @return int|null
     */
    private function resolveLocalityId($suburb, $postcode, $state)
    {
        $normalised = Suburbs::normalise($suburb);
        $postcode = trim((string) $postcode);

        if ($normalised === '' || $postcode === '') {
            return null;
        }

        $query = Suburbs::active()
            ->where('name_normalised', $normalised)
            ->where('postcode', $postcode);

        $stateRow = StateResolver::resolve($state);
        if ($stateRow) {
            $query->where('state_code', $stateRow->abbreviation);
        }

        $matches = $query->take(2)->get(['id']);

        return $matches->count() === 1 ? (int) $matches->first()->id : null;
    }
}
