<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\PropertyCategory;
use App\Models\Rating;
use App\Models\Review;
use App\Models\ReviewTags;
use App\Models\Suburbs;
use App\Models\User;
use App\Rules\UniqueCompanyName;
use App\Services\ResendService;
use App\Services\SocialFacebookAccountService;
use App\Support\StateResolver;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/review-step-1';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'agreement' => ['required'],
        ], ['email.unique' => 'This email has already been used.']);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 2
        ]);

        $user->assignRole('reviewer');

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        event(new Registered($user));
        $verifyUrl = $this->getVerificationUrl($user);
        app(ResendService::class)->send(
            $user->email,
            'Verify your ratemyestate.com.au account',
            'email-template.verify',
            ['url' => $verifyUrl]
        );

        $request->session()->put('user_saved', true);
        // create review if exists in cookie
        if ($request->has('review_form_data')) {
            $reviewData = json_decode($request->get('review_form_data'), true);
            $reviewRes = $this->insertReviewOfNewUser($reviewData, $user->id, $user->email);

            if ($reviewRes) {
                $request->session()->forget('guest-login');
                $request->session()->put('new_review', true);
            }
        }
        // end
        return redirect(route('register'));
        //        return redirect(route('root'));
    }

    public function showBuilderRegister()
    {
        if (!old('_token')) {
            session()->forget(['company-logo', 'company-logo-system']);
        }

        return view('auth.builder-register', [
            'oauthBuilderProfile' => session('oauth_builder_profile'),
        ]);
    }

    public function createBuilder(Request $request)
    {
        if ($request->hasFile('company_logo') && $request->file('company_logo')) {
            $name = $request->file('company_logo')->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->company_logo, $name);
            $imagePath = url('/') . '/review-form-media/' . $name;
            $request->session()->put('company-logo', $imagePath);
            $request->session()->put('company-logo-system', public_path('review-form-media/' . $name));
        }

        $oauthProfile = $request->session()->get('oauth_builder_profile');
        $isOAuthSignup = !empty($oauthProfile);

        $data = $request->all();
        if ($isOAuthSignup) {
            $data['email'] = $oauthProfile['email'];
        }
        if (isset($data['company_name'])) {
            $data['company_name'] = trim($data['company_name']);
        }
        if (($data['company_id'] ?? null) === '' || ($data['company_id'] ?? null) === 'null') {
            $data['company_id'] = null;
        }

        // Prefer explicit mode; otherwise infer from company_id vs company_name.
        $mode = $data['company_mode'] ?? null;
        if (!in_array($mode, ['existing', 'new'], true)) {
            $mode = !empty($data['company_id']) ? 'existing' : 'new';
        }
        $isExistingCompany = $mode === 'existing';

        $rules = [
            'email' => 'required|email|unique:users,email',
            'company_logo' => 'nullable',
            'agreement' => 'required',
        ];

        if ($isExistingCompany) {
            $rules['company_id'] = [
                'required',
                Rule::exists('company_details', 'id')->where(function ($query) {
                    $query->where('status', 1)
                        ->where('id', '<>', 3)
                        ->whereNull('deleted_at');
                }),
            ];
            $rules['website_url'] = 'nullable|url';
        } else {
            $rules['company_name'] = ['required', 'string', new UniqueCompanyName()];
            $rules['website_url'] = 'required|url';
        }

        if (!$isOAuthSignup) {
            $rules['password'] = 'required|confirmed|min:6';
            $rules['password_confirmation'] = 'required';
        }

        $validator = Validator::make($data, $rules, [
            'email.unique' => 'The email has already been used.',
            'company_id.required' => 'Please select a company from the list, or add a new one.',
            'company_id.exists' => 'Please select a valid company from the list.',
        ]);

        $errors = $validator->errors();
        if (!empty($errors->messages())) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        DB::beginTransaction();
        try {
            $file = null;
            if ($request->session()->exists('company-logo')) {
                $sessionImg = $request->session()->get('company-logo-system');
                $imgName = substr($sessionImg, strpos($sessionImg, '/') + 1, strlen($sessionImg));
                $file = new UploadedFile($sessionImg, $imgName);
            }

            if ($file) {
                $data['profile_image'] = $this->saveToS3($file, 'profile-image/');
            }

            $adminEmail = config('mail.contact_to');

            if ($isExistingCompany) {
                $company = CompanyDetails::where('id', $data['company_id'])
                    ->where('status', 1)
                    ->where('id', '<>', 3)
                    ->firstOrFail();

                $displayName = $company->company_name;

                $userData = [
                    'firstname' => $displayName,
                    'lastname' => $displayName,
                    'display_name' => $displayName,
                    'email' => $data['email'],
                    'status' => 1,
                    'profile_image' => isset($data['profile_image']) ? $data['profile_image'] : '',
                    'company_id' => $company->id,
                ];

                // Access requests stay pending until admin approval.
                if (!$isOAuthSignup) {
                    $userData['password'] = Hash::make($data['password']);
                }

                $user = User::create($userData);
                $user->assignRole('builder');

                if ($adminEmail) {
                    app(ResendService::class)->send(
                        $adminEmail,
                        'Builder Company Access Request',
                        'email-template.builder-company-access-request',
                        [
                            'company_name' => $company->company_name,
                            'user_email' => $user->email,
                        ]
                    );
                }
            } else {
                $userData = [
                    'firstname' => $data['company_name'],
                    'lastname' => $data['company_name'],
                    'display_name' => $data['company_name'],
                    'email' => $data['email'],
                    'status' => 1,
                    'profile_image' => isset($data['profile_image']) ? $data['profile_image'] : '',
                ];

                if ($isOAuthSignup) {
                    $userData['email_verified_at'] = Carbon::now();
                } else {
                    $userData['password'] = Hash::make($data['password']);
                }

                $user = User::create($userData);
                $user->assignRole('builder');

                $companyData = [
                    'company_name' => $data['company_name'],
                    'website' => $data['website_url'],
                ];

                if ($file) {
                    $fileLogo = app('App\Http\Controllers\Admin\CompanyDetailsController')->uploadCompanyLogo($file);
                    $companyData = array_merge($companyData, $fileLogo);
                }

                $company = CompanyDetails::create($companyData);

                if ($adminEmail) {
                    app(ResendService::class)->send(
                        $adminEmail,
                        'New Company Registered',
                        'email-template.company-registered',
                        ['company_name' => $company->company_name]
                    );
                }

                $user->company_id = $company->id;
                $user->save();
            }

            $request->session()->put('user_saved', true);
            $request->session()->put(
                'user_saved_mode',
                $isExistingCompany ? 'existing' : 'new'
            );

            if ($isOAuthSignup) {
                app(SocialFacebookAccountService::class)->linkAccountToUser(
                    $user,
                    $oauthProfile['provider_user_id'],
                    $oauthProfile['provider']
                );
                $request->session()->forget('oauth_builder_profile');
            }

            DB::commit();
            return redirect(route('show-builder'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            $request->session()->forget('user_saved');
            $request->session()->forget('user_saved_mode');
            return redirect()->back()->withInput();
        }
    }

    public function resendEmailVerification()
    {
        $email = request()->session()->get('login_cred');
        if ($email) {
            request()->session()->forget('login_cred');
            $user = User::whereEmail($email)->first();
            $verifyUrl = $this->getVerificationUrl($user);
            app(ResendService::class)->send(
                $user->email,
                'Verify your ratemyestate.com.au account',
                'email-template.verify',
                ['url' => $verifyUrl]
            );
        }
        return redirect()->back();
    }

    public function getVerificationUrl($user)
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->email)
            ]
        );
        return $verifyUrl;
    }

    public function insertReviewOfNewUser($reviewData, $userId, $email)
    {
        try {
            $insertData['user_id'] = $userId;
            $insertData['category_id'] = $reviewData['review-category'];
            $insertData['property_id'] = $reviewData['review-property-id'];
            if (isset($reviewData['review-year-complete'])) {
                $insertData['year_of_completion'] = $reviewData['review-year-complete'];
            }
            $insertData['buyer_type'] = $reviewData['buyer-type'];
            $insertData['state'] = $reviewData['review-state'];
            $insertData['post_code'] = $reviewData['review-post-code'];
            $insertData['suburb'] = $reviewData['review-suburb'];
            $insertData['locality_id'] = $reviewData['review-locality-id'] ?? null;

            // This path builds the review straight from the session, so re-derive
            // the location from the locality rather than trusting the stored text.
            $locality = $insertData['locality_id']
                ? Suburbs::find($insertData['locality_id'])
                : null;
            if ($locality) {
                $insertData['suburb'] = $locality->name;
                $insertData['post_code'] = (string) $locality->postcode;
                $stateId = StateResolver::id($locality->state_code);
                if ($stateId) {
                    $insertData['state'] = $stateId;
                }
            }

            $insertData['title'] = $reviewData['review-title'];
            $insertData['rating'] = $reviewData['review-rating'];
            $insertData['recommendation'] = $reviewData['review-recommendation'];
            $insertData['tags'] = $reviewData['review-tags'];

            if (isset($reviewData['review-comment'])) {
                $insertData['comment'] = $reviewData['review-comment'];
            }

            if (isset($reviewData['review-image'])) {
                $cookieImg = $reviewData['review-image-system'];
                $imgName = substr($cookieImg, strpos($cookieImg, '/') + 1, strlen($cookieImg));
                $file = new UploadedFile($cookieImg, $imgName);
                $insertData['review_image'] = $this->saveToS3($file, 'review-logo/');
            }

            $cookieDoc = $reviewData['review-docs-system'] ?? null;
            if (empty($reviewData['review-docs']) || !$cookieDoc || !is_file($cookieDoc)) {
                Log::error('REGISTER_REVIEW_MISSING_DOCS: proof of purchase is required for guest review submission');
                return false;
            }
            $docName = basename($cookieDoc);
            $file = new UploadedFile($cookieDoc, $docName, null, null, true);
            $insertData['review_docs'] = $this->saveToS3($file, 'review-docs/');

            $insertData['is_agree'] = $reviewData['review-is-agree'];
            $insertData['status'] = 0;
            $review_id = Review::create($insertData);

            if ($review_id) {
                if (count($insertData['tags']) > 0) {
                    $reviewTags = [];
                    foreach ($insertData['tags'] as $tags) {
                        $valueToIns = [
                            'review_id' => $review_id->id,
                            'tag_id' => $tags
                        ];
                        array_push($reviewTags, $valueToIns);
                    }
                    ReviewTags::insert($reviewTags);
                }

                if (!empty($insertData['rating'])) {
                    $rateQuestion = [];
                    foreach ($insertData['rating'] as $k_q => $v_q) {
                        $questionIns = [
                            'review_id' => $review_id->id,
                            'rating_conf_id' => $k_q,
                            'rate' => $v_q
                        ];
                        array_push($rateQuestion, $questionIns);
                    }
                    Rating::insert($rateQuestion);
                }

                $categoryData = PropertyCategory::find($insertData['category_id']);
                $recommendedCategory = '';
                if (array_search($categoryData->slug, ['homesite', 'home']) !== false) {
                    $recommendedCategory = $categoryData->slug == 'homesite' ? 'home' : 'homesite';
                }

                // send email to user and admin
                app('App\Http\Controllers\ReviewController')->sendMailUponReviewCreation($review_id->id, $recommendedCategory);
                // end
            }
            return true;
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
    }
}
