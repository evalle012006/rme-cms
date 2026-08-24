<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Route;

Route::get('email-pending', function () {
    return view('email-template.user-company-assigned');
});
Route::get('email-approve', function () {
    return view('email-template.review-submitted');
});

Route::get('storage/{filename}', 'UploadController@getFile')->where('filename', '.*');
Auth::routes(['verify' => true]);

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

// register screen for builder / company
Route::get('builder/register', 'Auth\RegisterController@showBuilderRegister')->name('show-builder');
Route::post('builder/register', 'Auth\RegisterController@createBuilder')->name('save-builder');
// end

// XML sitemap for search engines
Route::get('sitemap.xml', 'SitemapController@index')->name('sitemap');

Route::get('/', 'HomeController@index')->name('root');

Route::get('/about', 'HomeController@aboutPage')->name('about-page');
Route::get('/contact', 'HomeController@contactPage')->name('contact-page');

// below route was "property-listing" client wanted to have "reviews-listing", not changing route name
Route::get('/reviews-listing', 'PropertyListingController@propertyListingPage')->name('property-listing');
//Route::get('property-listing-by-brand', 'PropertyListingController@propertyListingByBrand')->name('property-listing-by-brand');
Route::get('/review-step-1',
    'ReviewController@reviewStepOnePage')->name('review-step-1')->middleware('review.middleware');
Route::get('/review-step-2', 'ReviewController@reviewStepTwoPage')->name('review-step-2');
Route::get('/review-step-3', 'ReviewController@reviewStepThreePage')->name('review-step-3');
Route::get('/review-step-2-1', 'ReviewController@reviewStepTwoHomeLand')->name('review-step-2-1');
Route::get('review-listing', 'ReviewController@reviewListing')->name('review-listing');
Route::get('/nav-review-step1', 'ReviewController@navReviewStepOne')->name('nav-review-step1');


// socialite — throttled to make brute-force enumeration expensive
Route::middleware('throttle:20,1')->group(function () {
    // Public site (Blade / session guard)
    // google
    Route::get('login/google', 'Auth\LoginController@redirectToGoogleProvider')->name('socialite-google');
    Route::get('login/google/callback', 'Auth\LoginController@handleGoogleProviderCallback');

    // facebook
    Route::get('login/facebook', 'Auth\LoginController@redirectToFacebookProvider')->name('socialite-facebook');
    Route::get('login/facebook/callback', 'Auth\LoginController@handleFacebookProviderCallback');

    // React CMS (Passport token, admin-only, no auto-provision)
    Route::get('cms/auth/{provider}', 'Auth\CmsSocialLoginController@redirect')
        ->where('provider', 'google|facebook')
        ->name('cms.social.redirect');
    Route::get('cms/auth/{provider}/callback', 'Auth\CmsSocialLoginController@callback')
        ->where('provider', 'google|facebook')
        ->name('cms.social.callback');
});
Route::any('/property_search', 'PropertyListingController@searchData')->name('property_search');
Route::any('property_search/{slug}', 'PropertyListingController@searchData')->name('property_search_slug');

Route::get('resend-verification', 'Auth\RegisterController@resendEmailVerification');
//my-profile
Route::group(['middleware' => 'checkAuth'], function () {
    Route::get('/my-profile', 'HomeController@myProfile')->name('my-profile-page');
    Route::get('my-review', 'HomeController@myReviews')->name('my-review-page');
    Route::post('update-profile', 'HomeController@updateProfile')->name('update-profile');
    Route::post('update-photo', 'HomeController@changeProfileImage')->name('update-photo');
    Route::post('change-request', 'HomeController@changeRequest')->name('change-request');
    Route::post('update-company-bio', 'BuilderController@updateCompanyBio')->name('update-bio');

    // builder portal routes
    Route::group(['prefix' => 'builder'], function () {
        Route::get('dashboard', 'BuilderController@showDashboard')->name('builder-dashboard');
        Route::get('my-project', 'BuilderController@myProperties')->name('builder-property');
        Route::permanentRedirect('my-property', '/builder/my-project');
        Route::get('company-users', 'BuilderController@getCompanyUsers')->name('company-users');
        Route::post('create-property', 'BuilderController@createProperty')->name('create-property');
        Route::post('create-advertise', 'AdvertiseController@createAdvertise')->name('create-adv');
        Route::post('review-counts', 'BuilderController@getReviewCounts')->name('review-counts');
        Route::post('date-wise-review-counts', 'BuilderController@getDateRangeCounts')->name('date-wise-counts');
        Route::post('amenities-by-project', 'BuilderController@getAmenitiesByProject')->name('amenities-by-project');
        Route::get('export-company', 'BuilderController@exportCompany')->name('export-company');
        Route::post('update-logo', 'BuilderController@updateCompanyLogo')->name('update-logo');
        Route::get('export-property-reviews/{id}', 'BuilderController@exportPropertyReviews')->name('export-reviews');
        Route::post('add-user', 'BuilderController@addNewUser')->name('add-builder-user');
        Route::post('validate-email', 'BuilderController@validateEmail')->name('validate-email');
        Route::post('report', 'BuilderController@reportBuilder')->name('report-builder');
        Route::post('update-property-logo', 'BuilderController@updatePropertyLogo')->name('update-property-logo');
    });
    Route::post('update-review', 'ReviewController@updateReview')->name('update-review');
});


//blog detail
Route::permanentRedirect('/property-coach', '/property-blog');
Route::get('/property-blog', 'PropertyCoachController@propertyCoachPage')->name('property-blog');
Route::get('blog-detail/{slug}', 'PropertyCoachController@blogDetails');
Route::get('/blog-posts/{slug?}', 'PropertyCoachController@allBlogPosts')->name('blog-posts');

//property listing
//Route::get('property-listing/{slug}', 'PropertyListingController@getPropertyBySlug');

// below route was "property-listing/{slug}" client wanted to have "reviews-listing/{slug}", not changing route name
Route::get('reviews-listing/{slug}', 'PropertyListingController@searchData')->name('property-listing-with-slug');
Route::get('property/{slug}', 'PropertyListingController@getPropertyWithCompany')->name('property.company');
Route::get('property/{category_slug}/{slug}', 'PropertyListingController@getCompanyPropertyByCategory');

Route::post('/autocomplete/fetch-company', 'PropertyListingController@fetchCompany')->name('autocomplete.fetchCompany');
Route::post('/autocomplete/fetch-property',
    'PropertyListingController@fetchProperty')->name('autocomplete.fetchProperty');
Route::post('/autocomplete/fetch-tags', 'HomeController@fetchTags')->name('autocomplete.fetchTags');
Route::post('/autocomplete/fetch-states', 'HomeController@fetchReviewStates')->name('autocomplete.fetchStates');
Route::post('/autocomplete/fetch-suburbs', 'HomeController@fetchSuburbs')->name('autocomplete.fetchSuburbs');

Route::post('review-save-one', 'ReviewController@saveReviewStepOne')->name('review-save-one');
Route::post('review-save-two', 'ReviewController@saveReviewStepTwo')->name('review-save-two');
Route::post('review-save-two-new', 'ReviewController@saveReviewStepTwoHomeLand')->name('review-save-two-new');
Route::get('review/{slug}', 'ReviewController@getReviewBySlug');
Route::get('review-step/{slug}', 'ReviewController@reviewStepRedirect');

Route::post('contact-save', 'ContactUsController@saveContactUs')->name('contact-save');


Route::get('home-review', 'ReviewController@homeCatReview')->name('home-cat-review');


//property review
Route::get('property-review/{slug}', 'PropertyListingController@getPropertyReviewBySlug')->name('property-review-slug');
Route::get('property-review/{cat}/{slug}', 'PropertyListingController@getPropertyReviewByCatSlug');


//cms pages
Route::get('privacy-policy', 'GeneralPageController@getPrivacyPolicy')->name('privacy-policy');
Route::get('review-guidelines', 'GeneralPageController@getReviewGuideline')->name('review-guideline');
Route::get('terms-of-use', 'GeneralPageController@getTermsOfUse')->name('terms-of-use');

//review add steps company and property
Route::post('review/save-company', 'ReviewController@saveCompanyByReview');
Route::post('review/save-property', 'ReviewController@savePropertyByReview');
//});

// add comment tag for step 2
Route::post('tags/add', 'Admin\TagsController@addTag')->name('tag.add');

// property guide page
Route::get('property-guide', 'PropertyGuideController@propertyGuidePage')->name('property-guide');

// autocomplete suburb
Route::post('get-suburbs', 'HomeController@autocompleteSuburb')->name('get-suburbs');
Route::post('get-property-suburbs', 'HomeController@autocompletePropertySuburb')->name('get-property-suburbs');


Route::group(['prefix' => 'search'], function () {
    Route::get('/', 'SearchController@search')->name('general-search');
});
