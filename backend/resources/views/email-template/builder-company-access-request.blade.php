<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div
    style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px 40px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;">
        <img src="{{asset('images/logo.png')}}" alt="logo"/></p>
    <p>A builder has requested access to an existing company.</p>
    <p style="text-align: left;margin: 24px 0 0;">Company: <strong>{{$company_name}}</strong></p>
    <p style="text-align: left;margin: 8px 0 0;">Requester email: <strong>{{$user_email}}</strong></p>
    <p style="text-align: left;margin: 8px 0 0;">Please review and approve this account in the admin CMS.</p>

    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
