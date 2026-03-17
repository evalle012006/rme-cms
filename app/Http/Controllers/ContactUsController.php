<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ContactUsController extends BaseController
{
    public function saveContactUs(Request $request)
    {
        $data = request()->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);

        if(ContactUs::create($data))
        {
            return redirect()->back()->with('contact_us', 'Contact has been send successfully');
        }
    }


}
