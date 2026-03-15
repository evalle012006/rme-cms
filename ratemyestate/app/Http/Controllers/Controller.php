<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $PROFILE_STORAGE_PATH = 'profile-image/';
    public $FEATURE_STORAGE_PATH = 'feature-image/';
    public $COMPANY_LOGO_STORAGE_PATH = 'company-logo/';
    public $PROPERTY_LOGO_STORAGE_PATH = 'property/';

    function saveToS3(UploadedFile $file, $path = '')
    {
        if (App::environment() == 'local') {
            return str_replace('public/', '', $file->storePublicly('public/' . $path . now()->format('Y/m/d')));
        }
        return $file->storePublicly($path . now()->format('Y/m/d'));
    }

    function saveThumbToS3($path, $width = 300, $height = 200)
    {
        if (config('filesystems.default') !== 's3') {
            $url = url(Storage::url($path));
        } else {
            $url = Storage::url($path);
        }

        $img = Image::make($url)->resize($width, $height)->stream();
        $date = now()->format('Y/m/d');
        $thumbPath = str_replace($date, $date . '/thumb', $path);
        Storage::put($thumbPath, $img, 'public');
        return $thumbPath;
    }

}
