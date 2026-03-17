<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;

class BaseController extends Controller
{
    const PROFILE_STORAGE_PATH = 'profile-image/';
    const FEATURE_STORAGE_PATH = 'feature-image/';
    const COMPANY_LOGO_STORAGE_PATH = 'company-logo/';
    const REVIEW_STORAGE_PATH = 'review-logo/'; //  when change this path change in register controller aswell
    const REVIEW_TEMP_STORAGE_PATH = 'review-temp-logo/';
    const REVIEWDOCS_STORAGE_PATH = 'review-docs/'; //  when change this path change in register controller aswell
    const REVIEWDOCS_TEMP_STORAGE_PATH = 'review-temp-docs/';
    const PROPERTY_STORAGE_PATH = 'property-image/';
    const PROPERTY_LOGO_STORAGE_PATH = 'property/';
    const RATINGCONF_STORAGE_PATH = 'ratingconf-icon/';
    const PROPERTYCAT_STORAGE_PATH = 'propertycat-icon/';
    const CMS_PAGE_PATH = 'cms-images/';
    const ADV_IMAGE_PATH = 'adv-images/';

    public function formatSuccessResponse($msg, $data, $code = 200)
    {
        $response = [
            'status' => 'success',
            'code' => $code,
            'message' => $msg,
            'data' => $data
        ];
        return response()->json($response, $code);
    }

    public function formatFailureResponse($msg, $code = 500)
    {
        $response = [
            'status' => 'failure',
            'code' => $code,
            'message' => $msg
        ];
        return response()->json($response, $code);
    }

    public function jSonFormatSuccessResponse($data, $code = 200)
    {
        return response()->json($data, $code);
    }

    public function jSonFormatFailureResponse($msg, $code = 500)
    {
        return response()->json($msg, $code);
    }

    public function apiFormatSuccessResponse($msg, $itmsg, $data, $code = 200)
    {
        $response = [
            'status' => 'success',
            'code' => $code,
            'msg' => $msg,
            'itmsg' => $itmsg,
            'data' => $data
        ];
        return response()->json($response, $code);
    }

    public function apiFormatFailureResponse($msg, $itmsg, $code = 500)
    {
        $response = [
            'status' => 'unsuccess',
            'code' => $code,
            'message' => $msg,
            'errors' => $itmsg
        ];
        return response()->json($response, $code);
    }


    public function getTokenId(Request $request)
    {
        $token = $request->bearerToken();
        return (new Parser())->parse($token)->getClaims()['jti']->getValue();
    }
}
