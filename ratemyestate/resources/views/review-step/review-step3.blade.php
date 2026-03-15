@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url(images/about-banner.jpg);">
        <div class="banner-caption">
            <h2>Write a Review</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Write a review</span>
            </div>
        </div>
    </div>

    <div class="review-steps-section">
        <div class="container">
            <div class="review-steps-box">
                <div class="steps-wrap">
                    <ul>
                        <li class="single-step {{Request::is('review-step-1')?'active':''}}"><a
                                href="{{route('review-step-1')}}"><h5>Step 1<span>Basic Details</span>
                                </h5></a></li>
                        <li class="single-step {{Request::is('review-step-2')?'active':''}}"><a
                                href="{{route('review-step-2')}}"><h5>Step 2<span>Your Review </span>
                                </h5></a></li>
                        <li class="single-step {{Request::is('review-step-3')?'active':''}}"><a
                                href="{{route('review-step-3')}}"><h5>Step
                                    3<span>Submit Review</span></h5></a></li>
                    </ul>
                </div>
                <div class="thank-u-wrap">
                    <div class="thanku-box">
                        <div class="icon-wrap">
                            <img src="images/thank-u-icon.png">
                        </div>
                        <div class="msg-wrap">
                            <h4>Thank you for sharing</h4>
                            <div class="share-reviews">
                                <h6>Your review will be pending until it is approved by admin. </h6>
                                @if(session()->has('recommended-review-category'))
                                    <a href="{{url('/review-step-1?'.session()->get('recommended-review-category'))}}">
                                        <h6>
                                            <strong>
                                                Click here to write review
                                                on {{session()->get('recommended-review-category')}}
                                            </strong>
                                        </h6>
                                    </a>
                                @endif
                                <h6>Share your Reviews </h6>
                                <ul>
                                    <li><a href="#"><i class="fa fa-google" aria-hidden="true"></i></a></li>
                                    <li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                </ul>
                                <a href="{{url('/')}}" class="btn yellow-btn m-t-15">Back to Website</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
