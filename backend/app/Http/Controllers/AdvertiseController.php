<?php

namespace App\Http\Controllers;

use App\Models\Advertise;
use App\Models\Properties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertiseController extends Controller
{
    public function createAdvertise(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, ['title' => 'required', 'description' => 'required']);
        if ($validator->fails()) {
            $request->session()->put('adv_create_error', 'Error while creating advertise');
        } else {
            if ($request->hasFile('image') && $request->file('image')) {
                $data['image'] = $this->saveToS3($request->file('image'), 'adv-images/');
            }
            $property = Advertise::create($data);
            if ($property) {
                $request->session()->put('adv_create_error', 'Error while creating advertise');
            }
        }
        return redirect()->back();
    }
}
