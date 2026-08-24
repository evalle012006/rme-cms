<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class UploadController extends Controller
{
    /** Allowed storage folder prefixes (must match BaseController + BlogCategory). */
    private const ALLOWED_IMAGE_PATHS = [
        'profile-image/',
        'feature-image/',
        'company-logo/',
        'property/',
        'propertycat-icon/',
        'blog-category-image/',
        'cms-images/',
        'adv-images/',
        'ratingconf-icon/',
    ];

    public function uploadImages(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['error' => 'No file provided under key "file"'], 422);
        }

        $imagePath = $request->input('image_path');
        if (!is_string($imagePath) || $imagePath === '') {
            return response()->json(['error' => 'Missing or invalid image_path'], 422);
        }

        // Normalize trailing slash so whitelist matches regardless of client form
        if (substr($imagePath, -1) !== '/') {
            $imagePath .= '/';
        }

        if (!in_array($imagePath, self::ALLOWED_IMAGE_PATHS, true)) {
            return response()->json(['error' => 'Invalid image_path'], 422);
        }

        $size = File::size($file);
        $extension = $file->getClientOriginalExtension();
        $allowed = ['gif', 'png', 'jpg', 'jpeg'];
        $ext = strtolower($extension);

        if (!in_array($ext, $allowed)) {
            return response()->json(['error' => 'Unsupported file type'], 422);
        }

        try {
            $path = $this->saveToS3($file, $imagePath);
        } catch (\Exception $e) {
            Log::error('UPLOAD_FAILED: ' . $e->getMessage());
            return response()->json(['error' => 'File upload failed'], 500);
        }

        // Thumbnail — read back from storage (works for both local and S3)
        try {
            $this->saveThumbToS3($path);
        } catch (\Exception $e) {
            Log::error('UPLOAD_THUMB_ERROR: ' . $e->getMessage());
        }

        return response()->json(['filename' => $path, 'size' => $size]);
    }

    public function uploadFile(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['error' => 'No file provided'], 422);
        }

        $size = File::size($file);

        try {
            $path = $this->saveToS3($file, 'Uploads/pdf_files/');
        } catch (\Exception $e) {
            Log::error('FILE_UPLOAD_FAILED: ' . $e->getMessage());
            return response()->json(['error' => 'File upload failed'], 500);
        }

        return response()->json(['filename' => $path, 'size' => $size]);
    }

    public function deleteUpload($folder, $image)
    {
        $filename       = $image;
        $path_final_dir = public_path() . '/../../Uploads/' . $folder . '/';
        $thumb_path     = 'thumb/';

        if (File::delete($path_final_dir . $filename)) {
            File::delete($path_final_dir . $thumb_path . $filename);
            return 1;
        }
        return 0;
    }

    public function getFile($filename)
    {
        if (config('filesystems.default') === 's3') {
            if (!Storage::disk('s3')->exists($filename)) {
                abort(404);
            }
            return redirect(Storage::disk('s3')->url($filename));
        }

        $path = storage_path('app/' . $filename);
        if (!File::exists($path)) {
            abort(404);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);
        $response->header('Access-Control-Allow-Origin', '*');
        return $response;
    }
}