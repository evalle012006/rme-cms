<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div
    style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px 40px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;">
        @include('email-template.partials.logo')</p>
    <p>Display name change request has been made by user {{$details['display_name']}} <br>({{$details['full_name']}}).
    </p>
    <p>Requested display name: <strong>{{$details['requested_display_name']}}</strong></p>
    @include('email-template.partials.admin-link')

    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
