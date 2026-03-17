<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetails;
use App\Models\PropertyCategory;
use App\Models\Rating;
use App\Models\Review;
use App\Models\ReviewTags;
use App\Models\User;
use App\Notifications\SendVerifyAccountEmail;
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
            'status' => 1
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
        $user->notify(new SendVerifyAccountEmail($verifyUrl));

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
        return view('auth.builder-register');
    }

    public function createBuilder(Request $request)
    {
        if ($request->hasFile('company_logo') && $request->file('company_logo')) {
//            dd('in');
            $name = $request->file('company_logo')->getClientOriginalName();
            Storage::disk('public_form_media')->putFileAs('', $request->company_logo, $name);
            $imagePath = url('/') . '/review-form-media/' . $name;
            $request->session()->put('company-logo', $imagePath);
            $request->session()->put('company-logo-system', public_path('review-form-media/' . $name));
        }

        $rules = [
            'company_name' => 'required|unique:company_details,company_name',
            'email' => 'required|email|unique:users,email',
            'website_url' => 'required|url',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
            'company_logo' => 'nullable',
            'agreement' => 'required'
        ];
        $data = $request->all();

        $validator = Validator::make($data, $rules, ['email.unique' => 'The email has already been used.']);

        $errors = $validator->errors();
//        if (!$request->session()->exists('company-logo')) {
//            $errors->add('company-logo', 'The company logo field is required.');
//        }
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

            $user = User::create([
                'firstname' => $data['company_name'],
                'lastname' => $data['company_name'],
                'display_name' => $data['company_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 1,
                'profile_image' => isset($data['profile_image']) ? $data['profile_image'] : ''
            ]);
            $user->assignRole('builder');

            $companyData = [
                'company_name' => $data['company_name'],
                'website' => $data['website_url']
            ];

            if ($file) {
                $fileLogo = app('App\Http\Controllers\Admin\CompanyDetailsController')->uploadCompanyLogo($file);
                $companyData = array_merge($companyData, $fileLogo);
            }

//            if ($request->session()->exists('company-logo')) {
//                $sessionImg = $request->session()->get('company-logo-system');
//                $imgName = substr($sessionImg, strpos($sessionImg, '/') + 1, strlen($sessionImg));
//                $file = new UploadedFile($sessionImg, $imgName);
//                $fileLogo = app('App\Http\Controllers\Admin\CompanyDetailsController')->uploadCompanyLogo($file);
//                $companyData = array_merge($companyData, $fileLogo);
//            }

            $company = CompanyDetails::create($companyData);

            if ($user && $company) {
//                event(new Registered($user));
//                $verifyUrl = $this->getVerificationUrl($user);
//                $user->notify(new SendVerifyAccountEmail($verifyUrl));
                $adminEmail = User::whereId(1)->pluck('email')->first();

                \Mail::to($adminEmail)->send(new \App\Mail\CompanyRegistered($company->company_name));

                $request->session()->put('user_saved', true);
                $user->company_id = $company->id;
                $user->save();
            }
            DB::commit();
            return redirect(route('show-builder'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            $request->session()->forget('user_saved');
            return redirect()->back();
        }
    }

    public function resendEmailVerification()
    {
        $email = request()->session()->get('login_cred');
        if ($email) {
            request()->session()->forget('login_cred');
            $user = User::whereEmail($email)->first();
            $verifyUrl = $this->getVerificationUrl($user);
            $user->notify(new SendVerifyAccountEmail($verifyUrl));
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

            if ($reviewData['review-docs']) {
                $cookieDoc = $reviewData['review-docs-system'];
                $docName = substr($cookieDoc, strpos($cookieDoc, '/') + 1, strlen($cookieDoc));
                $file = new UploadedFile($cookieDoc, $docName);
                $insertData['review_docs'] = $this->saveToS3($file, 'review-docs/');
            }

            $insertData['is_agree'] = $reviewData['review-is-agree'];
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
