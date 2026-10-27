<!DOCTYPE html>
<html>
<head>
    <title>{{url('/')}}</title>
</head>
<body>
<div style="box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.10);max-width: 500px;margin: 0 auto;padding: 20px 50px 40px;border-radius: 5px;text-align: center;background: #f9f9f9;">
    <p style="text-align: center;margin-bottom: 40px;">@include('email-template.partials.logo')</p>
    <h2>Thank you </h2>
    <p>Your review has been received. Our team will verify your review in accordance with our guidelines.
        Once approved we will notify you via email that your review has been published on our website.</p>
    <p>Thank you for sharing your experience and contributing to doing good.</p>

    @if(isset($suggestedCategory) && $suggestedCategory)
        <!-- this link will be displayed if user has submitted review on homesite or home -->
        <a href="{{url('/review-step-1?'.$suggestedCategory)}}">Click here to submit review on {{$suggestedCategory}}</a>
    @endif
        <p style="text-align: left;margin: 60px 0px 0px;">Regards,</p>
        <p style="text-align: left;margin: 0px;">{{config('mail.mail_regards')}}</p>
</div>
</body>
</html>
