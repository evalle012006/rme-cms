<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        view()->composer("welcome", "App\Http\ViewComposers\HomeViewComposer");
//        view()->composer("about", "App\Http\ViewComposers\AboutViewComposer");
//        view()->composer("property-guide", "App\Http\ViewComposers\PropertyGuideViewComposer");
//        view()->composer("property-coach", "App\Http\ViewComposers\PropertyCoachViewComposer");
//        view()->composer("contact", "App\Http\ViewComposers\ContactViewComposer");

        view()->composer("welcome", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("about", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("property-guide", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("property-coach", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("contact", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("property-listing", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("review-guideline", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("term-of-use", "App\Http\ViewComposers\CmsGeneralViewComposer");
        view()->composer("privacy-policy", "App\Http\ViewComposers\CmsGeneralViewComposer");
    }
}
