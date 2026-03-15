<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;

function setRouteActiveClass($routes, $activeClass = 'active', $notActiveClass = '')
{
    if (is_array($routes)) {
        foreach ($routes as $route) {
            if (Request::is($route)) {
                return $activeClass;
            }
        }
    }
    return Request::is($routes) ? $activeClass : $notActiveClass;
}

function reverseSlug($value)
{
    return ucwords(str_replace(['-', '_'], ' ', $value));
}

function trimDescription($description)
{
    return substr(strip_tags($description), 0, 50);
}

function trimTitle($title)
{
    $paragraph = substr($title, 0, 22);
    if (strlen($title) > 25) {
        return $paragraph . '...';
    }
    return $paragraph;
}

function parseDate($date)
{
    if (!empty($date)) {
        return Carbon::parse($date)->format('F j,Y');
    }
    return $date;
}

function setPropertySuburbSelected($val)
{
    if (request()->input('filter_by') == $val) {
        return 'selected';
    }
    return '';
}

function setSortCategorySelected($id)
{
    $cat_type = request()->input('category_type');
    if (isset($cat_type) > 0 && in_array($id, $cat_type)) {
        return 'checked';
    }
    return '';
}

function getPropertyCategory()
{
    return \App\Models\PropertyCategory::get()->toArray();
}

function getCMSpageContent($id)
{
    $cms_page = \App\Models\CmsPages::where('id', '=', $id)
        ->first()->toArray();

    return $cms_page['description'];
}

function checkUrl($url)
{
    if (!$url) {
        return '';
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    } else {
        if (App::environment() == 'local') {
            return ('storage/' . $url);
        }
        return (env('AWS_IMAGE_URL') . $url);
    }
}

// converts string to heading. eg. my_reviews => My reviews
function makeHeading($str)
{
    return ucfirst(str_replace('_', ' ', $str));
}


// runs pagination on laravel collection object
function collectPaginate($items, $options = [], $perPage = 6, $page = null)
{
    $options = array_merge($options, ["path" => request()->url()]);
    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

    $items = $items instanceof Collection ? $items : Collection::make($items);

    return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
}

// convert rating number to css class - ex. 3.5 to 3-5
function ratingClass($rating)
{
    if (strpos($rating, '.0') !== false) {
        return str_replace('.0', '', $rating);
    } else {
        return str_replace('.5', '-5', $rating);
    }
}

// converts exponential rating into nearest dividend of 5
// ex. 3.1 to 3.0, 3.3 to 3.5, 3.5 to 3.5
function roundOffRating($rating)
{
    return number_format((round($rating / 0.5) * 0.5), 1, '.', '');
}

function validateUrl($url)
{
    if (!str_starts_with($url, 'http') && !str_starts_with($url, 'https')) {
        return 'http://' . $url;
    }
    return $url;
}
