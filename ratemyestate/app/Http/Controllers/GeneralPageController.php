<?php

namespace App\Http\Controllers;

class GeneralPageController extends BaseController
{

    public function getPrivacyPolicy()
    {
        return view('privacy-policy');
    }
    public function getReviewGuideline()
    {
        return view('review-guideline');
    }
    public function getTermsOfUse()
    {
        return view('term-of-use');
    }

}
