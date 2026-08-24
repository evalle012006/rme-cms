<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div
    style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px 40px;border-radius: 5px;text-align: left;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;">
        <img src="{{asset('images/logo.png')}}" alt="logo"/></p>
    <p style="text-align: center;margin-bottom: 30px;">A new contact form enquiry has been submitted.</p>
    <p><strong>Name:</strong> {{$details['firstname']}} {{$details['lastname']}}</p>
    <p><strong>Email:</strong> {{$details['email']}}</p>
    <p><strong>Subject:</strong> {{$details['subject']}}</p>
    <p><strong>Message:</strong></p>
    <p style="white-space: pre-wrap;">{{$details['message']}}</p>

    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
