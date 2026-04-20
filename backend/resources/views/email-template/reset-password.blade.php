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
            background: #3490dc;
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
    </style>
</head>
<body>
<div style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 570px;margin: 0 auto;padding: 20px 50px;border-radius: 5px;text-align: left;background: #f9f9f9">
    <p style="text-align: center;margin-bottom: 40px;color: black"><img src="{{asset('images/logo.png')}}" alt="logo"/></p>
    <h2 style="color: #3d4852;font-size: 19px"><strong>Hello!</strong></h2>
    <p style="color: #3d4852;font-size: 16px">You are receiving this email because we received a password reset request for your account.</p>
    <div style="text-align: center;"><a href="{{$link}}" class="custom-btn">Reset Password</a></div>
    <p style="color: #3d4852;font-size: 16px">This password reset link will expire in 48 Hours.</p>
    <p style="color: #3d4852;font-size: 16px">If you did not request a password reset, no further action is required.</p>
    <p style="text-align: left;margin: 20px 0px 0px;color: #3d4852;font-size: 16px">Regards,</p>
    <p style="text-align: left;margin: 0px;color: #3d4852;font-size: 16px;padding-bottom: 10px">Rate My eState</p>
    <p style="color: #3d4852;font-size: 12px;border-top: 1px solid #3d4852;padding-top: 10px">If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:{{$link}}</p>
</div>


</body>
</html>
