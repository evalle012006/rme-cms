<?php

use Illuminate\Http\Request;
use Illuminate\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('uploadImages', 'UploadController@uploadImages');
//Route::post('user/checkemailuser', 'AuthenticationController@checkUserEmail');
Route::post('fetch-suburbs', 'HomeController@fetchSuburbs');
Route::post('autocomplete-suburb', 'HomeController@autocompleteSuburb');

Route::group(['namespace' => 'Admin', 'prefix' => 'auth'], function () {
    Route::post('login', 'AuthenticationController@login');
    Route::post('reviewer/signup', 'UserController@reviewerSignup');
    Route::post('reviewer/update', 'UserController@reviewerUpdate');

    Route::post('builder/signup', 'UserController@builderSignup');
    Route::post('builder/update', 'UserController@builderUpdate');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'AuthenticationController@logout');
        Route::get('user', 'AuthenticationController@user');
    });
});

Route::group(['namespace' => 'Admin', 'middleware' => 'auth:api'], function () {

    Route::post('datatable-users', 'UserController@getDatatableUsers');
    Route::post('datatable-category', 'CategoryController@getDatatableProperty');
    Route::post('datatable-ratings', 'RatingController@getDatatableRating');
    Route::post('datatable-property-approve', 'PropertyController@getDatatablePropertyApprove');
    Route::post('datatable-property-pending', 'PropertyController@getDatatablePropertyPending');
    Route::post('datatable-tags', 'TagsController@getDatatableTags');
    Route::post('datatable-blog-category', 'BlogController@getDatatableBlogCategory');
    Route::post('datatable-blogs', 'BlogController@getDatatableBlog');
    Route::post('datatable-company-details', 'CompanyDetailsController@getDatatableCompany');
    Route::post('datatable-contact-details', 'UserController@getDatatableContact');
    Route::post('datatable-cms-content', 'SelectOptionController@getDatatableCmsContent');
    Route::post('datatable-reviews', 'ReviewController@getDatatableReview');
    Route::post('datatable-builders', 'UserController@getDatatableBuilders');
    Route::post('datatable-advertises', 'AdvertiseController@getAdvertisesDatatable');

    Route::post('adv/change-status', 'AdvertiseController@changeAdvertiseStatus');

    Route::post('user', 'UserController@getUserById');
    Route::post('user-update/me', 'UserController@updateMe');
    Route::post('user-password-reset/me', 'AuthenticationController@resetPassword');
    Route::get('get-users', 'UserController@getUsers');
    Route::post('change-request-status', 'UserController@changeRequestStatus');
    Route::post('builder/change-builder-status', 'UserController@changeBuilderStatus');
    Route::post('builder/get-by-id', 'UserController@getBuilderInfoById');
    Route::post('builder/update-builder', 'UserController@updateBuilder');

    Route::post('property-category-add', 'CategoryController@addPropertyCategory');
    Route::post('property-category-update', 'CategoryController@updatePropertyCategory');
    Route::post('property-category-delete', 'CategoryController@deletePropertyCategory');
    Route::post('get-property-category', 'CategoryController@getPropertyCategory');
    Route::get('get-all-property-category', 'CategoryController@getAllPropertyCategory');

    Route::post('rating-add', 'RatingController@addRating');
    Route::post('rating-update', 'RatingController@updateRating');
    Route::post('rating-delete', 'RatingController@deleteRating');
    Route::post('get-rating', 'RatingController@getRating');
    Route::get('get-rating-configuration', 'RatingController@getAllRatingConfiguration');

    Route::get('properties-with-allowed-ratings', 'PropertyRateController@getPropertyWithAllowedRatings');
    Route::post('set-property-with-allowed-ratings', 'PropertyRateController@setPropertyAllowedRatings');

    Route::post('property-import-file', 'PropertyController@importProperty');
    Route::post('property-add', 'PropertyController@addProperty');
    Route::post('property-update/{id}', 'PropertyController@updateProperty');
    Route::post('property-delete', 'PropertyController@deleteProperty');
    Route::get('property-get/{id}', 'PropertyController@getProperty');
    Route::get('property-get-all', 'PropertyController@getPropertyAll');
    Route::post('property-gallery-add', 'PropertyController@addPropertyGallery');
    Route::post('approve-property', 'PropertyController@approveProperty');

    Route::post('tag-add', 'TagsController@addTag');
    Route::post('tag-update', 'TagsController@updateTag');
    Route::post('tag-delete', 'TagsController@deleteTag');
    Route::get('tag-get/{id}', 'TagsController@getTag');

    Route::post('blog-category-add', 'BlogController@addBlogCategory');
    Route::post('blog-category-update', 'BlogController@updateBlogCategory');
    Route::get('blog-category-get/{id}', 'BlogController@getBlogCategory');
    Route::get('get-all-blog-category', 'BlogController@getAllBlogCategory');

    Route::post('blog-add', 'BlogController@addBlog');
    Route::post('blog-update', 'BlogController@updateBlog');
    Route::get('blog-get/{id}', 'BlogController@getBlog');
    Route::post('blog-delete', 'BlogController@deleteBlog');


    Route::post('company-add', 'CompanyDetailsController@addCompany');
    Route::post('company-update', 'CompanyDetailsController@updateCompany');
    Route::post('company-get', 'CompanyDetailsController@getCompany');
    Route::get('search-company/{searchItem}', 'CompanyDetailsController@getCompanySearch');
    Route::get('get-all-company', 'CompanyDetailsController@getAllCompany');
    Route::post('company/change-status', 'CompanyDetailsController@changeCompanyStatus');
    Route::post('company/delete', 'CompanyDetailsController@deleteCompany');

    Route::get('option/get-select-option/{id}', 'SelectOptionController@getSelectOptionMasters');

    Route::get('cms/get-cms/{id}', 'SelectOptionController@getCmsById');
    Route::post('cms-add', 'SelectOptionController@addCms');
    Route::post('cms-update/{id}', 'SelectOptionController@updateCms');

    Route::get('review/get/{id}', 'ReviewController@getReviewById');
    Route::post('review/change-status', 'ReviewController@changeReviewStatus');
    Route::get('get-states', 'ReviewController@getReviewStates');
    Route::post('review-update/{id}', 'ReviewController@updateReview');
    Route::delete('review/delete/{id}', 'ReviewController@deleteReview');
    Route::post('get-suburbs', 'ReviewController@getSuburbs');
    Route::post('suburbs-by-state', 'ReviewController@getSuburbsByState');

    Route::get('buyer-type-get', 'ReviewController@getBuyerTypes');

    Route::group(['prefix' => 'cms-page'], function () {
        Route::post('get', 'CmsPagesController@getCmsPageData');
        Route::post('save', 'CmsPagesController@saveCmsPageData');
    });

    Route::group(['prefix' => 'dashboard'], function () {
        Route::post('get', 'DashboardController@index');
        Route::post('filter-rating-chart', 'DashboardController@filterRatingChart');
        Route::post('properties-chart', 'DashboardController@getPropertiesChart');
        Route::post('review-count-chart', 'DashboardController@getReviewCountsByDate');
        Route::post('get-property-options', 'DashboardController@getPropertyReviewCount');
        Route::get('export-csv', 'DashboardController@downloadCsv');
    });
});
