<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div
    style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px 40px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;"><img src="{{asset('images/logo.png')}}" alt="logo"/></p>
    <p>
        Your review has been approved. You can check out your review here.<br>
        <a href="{{url('/my-review')}}">my reviews</a>
    </p>
    <p>Be sure to share your experience online with friends and everyone.</p>
    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
