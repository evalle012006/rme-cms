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

/**
 * Display label for a listing card category badge (property assignment, not URL slug).
 */
function listingCategoryBadgeLabel($propertiesData, $cat_slug = null, $property_category = [])
{
    if (!empty($propertiesData->is_company)) {
        if (empty($cat_slug) || empty($property_category)) {
            return null;
        }
        foreach ($property_category as $category) {
            if (($category['slug'] ?? null) === $cat_slug) {
                return $category['name'] ?? null;
            }
        }
        return null;
    }

    $allowed = $propertiesData->propertyAllowedCategory ?? null;
    if (!$allowed || (is_countable($allowed) && count($allowed) === 0)) {
        return null;
    }

    if (!empty($cat_slug)) {
        foreach ($allowed as $category) {
            if (($category->slug ?? null) === $cat_slug) {
                return $category->name ?? null;
            }
        }
    }

    $first = $allowed instanceof \Illuminate\Support\Collection ? $allowed->first() : (is_array($allowed) ? reset($allowed) : null);
    return $first->name ?? null;
}

function getCMSpageContent($id)
{
    $cms_page = \App\Models\CmsPages::where('id', '=', $id)
        ->first()->toArray();

    return $cms_page['description'];
}

/**
 * Absolute public URL for a stored path. Guarantees a single slash between base and path.
 */
function absoluteStorageUrl($path)
{
    if (!$path) {
        return null;
    }

    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }

    $filePath = ltrim($path, '/');
    $base = rtrim(env('AWS_IMAGE_URL') ?: env('AWS_URL') ?: '', '/');
    if ($base && config('filesystems.default') === 's3') {
        return $base . '/' . $filePath;
    }

    return url(\Illuminate\Support\Facades\Storage::url($filePath));
}

/**
 * Absolute URL into the React CMS admin.
 *
 * Returns null when CMS_APP_URL is unset so notification emails never fall
 * back to the public site. $path must be an in-app path beginning with "/".
 *
 * @param string $path
 * @return string|null
 */
function cmsAdminUrl($path)
{
    $path = (string) $path;
    if ($path === '' || $path[0] !== '/' || strpos($path, '//') === 0 || strpos($path, '://') !== false) {
        return null;
    }

    $base = rtrim((string) config('services.cms.url'), '/');
    if ($base === '') {
        \Illuminate\Support\Facades\Log::warning('CMS_ADMIN_URL_MISSING', ['path' => $path]);
        return null;
    }

    return $base . $path;
}

function checkUrl($url)
{
    if (!$url) {
        return '';
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }

    // Local disk: serve via public/storage symlink
    if (App::environment() == 'local' && config('filesystems.default') !== 's3') {
        return 'storage/' . ltrim($url, '/');
    }

    // Prefer explicit image base URL, then S3 disk URL from config
    $base = rtrim(env('AWS_IMAGE_URL') ?: env('AWS_URL') ?: '', '/');
    if ($base) {
        return $base . '/' . ltrim($url, '/');
    }

    // Same resolution used by models (Storage::url) — works for s3 and public disks
    return \Illuminate\Support\Facades\Storage::url(ltrim($url, '/'));
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
