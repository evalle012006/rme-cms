<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Services\ResendService;
use Illuminate\Http\Request;

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

        if (ContactUs::create($data)) {
            $sent = app(ResendService::class)->send(
                config('mail.contact_to'),
                'Contact form: ' . $data['subject'],
                'email-template.contact-us',
                ['details' => $data]
            );

            if ($sent) {
                return redirect()->back()->with('contact_us', 'Contact has been send successfully');
            }

            return redirect()->back()->with(
                'contact_us_error',
                'Sorry, we couldn\'t deliver your message. Please try again or email us directly at admin@ratemyestate.com.au.'
            );
        }

        return redirect()->back()->with(
            'contact_us_error',
            'Sorry, we couldn\'t save your message. Please try again or email us directly at admin@ratemyestate.com.au.'
        );
    }
}
