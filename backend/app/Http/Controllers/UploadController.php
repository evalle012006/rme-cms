<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Image;

class UploadController extends Controller
{

    public function uploadImages(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['error' => 'No file provided under key "file"'], 422);
        }

        $size = File::size($file);
        $destinationPath = storage_path() . '/app/public/Uploads/images/';

        // ← CHANGED: removed @ suppression so failures surface in logs
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $extension        = $file->getClientOriginalExtension();
        $uniquesavename   = time() . uniqid(rand());
        $filename         = str_random(12) . '-' . $uniquesavename . '.' . $extension;
        $allowed          = ['gif', 'png', 'jpg', 'jpeg', 'JPG', 'PNG', 'GIF'];  // ← normalised
        $ext              = strtolower(pathinfo($filename, PATHINFO_EXTENSION));   // ← CHANGED: lowercase for reliable comparison

        $upload_success = $file->move($destinationPath, $filename);

        if ($upload_success && in_array($ext, array_map('strtolower', $allowed))) {
            // ← CHANGED: removed unreachable icon resize block (was after a return statement)
            //   thumb generation is kept; icon generation removed since it was dead code
            if (!File::isDirectory($destinationPath . 'thumb')) {
                File::makeDirectory($destinationPath . 'thumb', 0777, true);
            }
            try {
                $img = Image::make($destinationPath . $filename);
                $img->resize(150, 150);
                $img->save($destinationPath . 'thumb/' . $filename);
            } catch (\Exception $e) {
                Log::error('UPLOAD_THUMB_ERROR: ' . $e->getMessage());
                // Non-fatal — original upload succeeded
            }

            return response()->json(['filename' => 'Uploads/images/' . $filename, 'size' => $size]);
        } elseif ($upload_success) {
            return response()->json(['filename' => 'Uploads/images/' . $filename, 'size' => $size]);
        } else {
            Log::error('UPLOAD_FAILED: ' . $filename);
            return response()->json(['error' => 'File upload failed'], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return response()->json(['error' => 'No file provided'], 422);
        }

        $size            = File::size($file);
        $destinationPath = storage_path() . '/app/public/Uploads/pdf_files/';

        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true);
        }

        $uniquesavename = time() . uniqid(rand());
        $filename       = str_random(12) . '-' . $uniquesavename . '.' . $file->getClientOriginalExtension();

        $upload_success = $file->move($destinationPath, $filename);

        if ($upload_success) {
            return response()->json(['filename' => $filename, 'size' => $size]);
        } else {
            Log::error('FILE_UPLOAD_FAILED: ' . $filename);
            return response()->json(['error' => 'File upload failed'], 500);
        }
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
        $path = storage_path('app/' . $filename);
        if (!File::exists($path)) {
            abort(404);
        }
        $file     = File::get($path);
        $type     = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);
        $response->header('Access-Control-Allow-Origin', '*');
        return $response;
    }
}