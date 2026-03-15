<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Image;

class UploadController extends Controller
{

    public function uploadImages(Request $request)
    {

        $file = $request->file('file');
        $size = File::size($file);
        $destinationPath = storage_path() . '/app/public/Uploads/images/';
        @mkdir($destinationPath, 0777);
        $extension = $file->getClientOriginalExtension();
        $uniquesavename = time().uniqid(rand());
        $filename = str_random(12) .'-'.$uniquesavename.'.' . $extension;
        $allowed = array('gif', 'png', 'jpg', 'Jpeg', 'JPG', 'PNG', 'GIF');
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $upload_success = $request->file('file')->move($destinationPath, $filename);

        if ($upload_success && in_array($ext, $allowed))
        {
            if ($ext == 'pdf')
            {

            } else
            {
                @mkdir($destinationPath . 'thumb', 0777);
                $img = Image::make($destinationPath . $filename);
                $img->resize(150, 150);

                $img->save($destinationPath . 'thumb/' . $filename);
                return response()->json(['filename' => $filename, 'size' => $size]);

                @mkdir($destinationPath . 'icon', 0777);
                $img = Image::make($destinationPath . $filename);
                $img->resize(40, 40);

                $img->save($destinationPath . 'icon/' . $filename);
            }
            return response()->json(['filename' => $filename, 'size' => $size]);
        } else if ($upload_success)
        {
            return response()->json(['filename' => $filename, 'size' => $size]);
        } else
        {
            return 'YEP: Problem in file upload';
        }
    }



    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $size = File::size($file);
        $destinationPath = storage_path() . '/app/public/Uploads/pdf_files/';
        @mkdir($destinationPath, 0777);
        $extension = $file->getClientOriginalExtension();
        $uniquesavename = time().uniqid(rand());
        $filename = str_random(12) .'-'.$uniquesavename.'.' . $extension;
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $upload_success = $request->file('file')->move($destinationPath, $filename);

        if ($upload_success)
        {
            return response()->json(['filename' => $filename, 'size' => $size]);
        } else
        {
            return 'YEP: Problem in file upload';
        }
    }

    public function deleteUpload($folder, $image)
    {
        $filename = $image;
        $path_final_dir = public_path() . '/../../Uploads/' . $folder . '/';

        $thumb_path = 'thumb/';

        if (File::delete($path_final_dir . $filename))
        {
            if (File::delete($path_final_dir . $thumb_path . $filename))
            {

            }
            return 1;
        } else
        {
            return 0;
        }
    }
    public function getFile($filename)
    {
        $path = storage_path('app/' . $filename);
        if (!File::exists($path))
        {
            abort(404);
        }
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        $response->header("Access-Control-Allow-Origin", '*');
        return $response;
    }

}
