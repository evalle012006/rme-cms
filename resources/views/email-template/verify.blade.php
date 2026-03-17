<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
    <style>
        .custom-btn {
            padding: 15px 26px;
            border: 0;
            border-radius: 5px;
            right: 0;
            background: #07AFA9;
            color: #FFFFFF !important;
            font-size: 16px;
            display: inline-block;
            vertical-align: top;
            text-decoration: none;
            margin: 20px 0px;
        }

        * {
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .custom-btn:hover {
            background-color: #000000;
        }
    </style>
</head>
<body>
<div style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;"><img src="{{asset('images/logo.png')}}" alt="logo"/></p>
    <h2><strong>Almost there…</strong></h2>
    <p>Please click the button below to verify your email address.</p>
    {{--<button><a href="{{$url}}">Verify Email</a></button>--}}
    <a href="{{$url}}" class="custom-btn">Verify Email</a>
    <p>If you did not create this account, there is no action required.</p>
    <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
    <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>

</body>
</html>
