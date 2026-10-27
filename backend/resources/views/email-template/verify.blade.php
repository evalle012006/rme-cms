<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;">@include('email-template.partials.logo')</p>
    <h2><strong>Almost there…</strong></h2>
    <p>Please click the button below to verify your email address.</p>
    <a href="{{$url}}" style="padding:15px 26px;border:0;border-radius:5px;background:#07AFA9;color:#FFFFFF;font-size:16px;display:inline-block;vertical-align:top;text-decoration:none;margin:20px 0;">Verify Email</a>
    <p style="font-size:12px;color:#3d4852;">If you’re having trouble clicking the "Verify Email" button, copy and paste the URL below into your web browser:<br>{{$url}}</p>
    <p>If you did not create this account, there is no action required.</p>
    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>

</body>
</html>
