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
    <p>User {{$data['reported_user']}} from
        company {{$data['company_name']}} has been reported
        by {{$data['user']}}.</p>
    @if($data['reason'])
        <strong>Description : </strong>
        <p>{{$data['reason']}}</p>
    @endif
    @include('email-template.partials.admin-link')
    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
