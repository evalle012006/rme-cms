<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use App\Models\CompanyDetails;
use App\Models\Properties;
use App\Models\PropertyCategory;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        // Static, indexable marketing/content pages.
        $staticPages = [
            ['route' => 'root', 'priority' => '1.0', 'freq' => 'daily'],
            ['route' => 'about-page', 'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'contact-page', 'priority' => '0.6', 'freq' => 'monthly'],
            ['route' => 'property-listing', 'priority' => '0.9', 'freq' => 'daily'],
            ['route' => 'property-blog', 'priority' => '0.7', 'freq' => 'weekly'],
            ['route' => 'property-guide', 'priority' => '0.7', 'freq' => 'monthly'],
            ['route' => 'blog-posts', 'priority' => '0.7', 'freq' => 'weekly'],
            ['route' => 'review-guideline', 'priority' => '0.4', 'freq' => 'yearly'],
            ['route' => 'terms-of-use', 'priority' => '0.3', 'freq' => 'yearly'],
            ['route' => 'privacy-policy', 'priority' => '0.3', 'freq' => 'yearly'],
        ];
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'lastmod' => null,
                'changefreq' => $page['freq'],
                'priority' => $page['priority'],
            ];
        }

        // Review categories.
        foreach (PropertyCategory::whereNotNull('slug')->get() as $category) {
            $urls[] = [
                'loc' => route('property-listing-with-slug', ['slug' => $category->slug]),
                'lastmod' => optional($category->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Company / brand pages.
        CompanyDetails::where('status', 1)->whereNotNull('slug')
            ->select('slug', 'updated_at')->chunk(500, function ($companies) use (&$urls) {
                foreach ($companies as $company) {
                    $urls[] = [
                        'loc' => route('property.company', ['slug' => $company->slug]),
                        'lastmod' => optional($company->updated_at)->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            });

        // Property review pages.
        Properties::where('status', 1)->whereNotNull('slug')
            ->select('slug', 'updated_at')->chunk(500, function ($properties) use (&$urls) {
                foreach ($properties as $property) {
                    $urls[] = [
                        'loc' => route('property-review-slug', ['slug' => $property->slug]),
                        'lastmod' => optional($property->updated_at)->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            });

        // Published blog posts (status 0 = published).
        Blogs::where('status', 0)->whereNotNull('slug')
            ->select('slug', 'updated_at')->chunk(500, function ($blogs) use (&$urls) {
                foreach ($blogs as $blog) {
                    $urls[] = [
                        'loc' => url('blog-detail/' . $blog->slug),
                        'lastmod' => optional($blog->updated_at)->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
