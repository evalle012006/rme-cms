@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <h2>Review Listing</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Review Listing</span>
            </div>
        </div>
    </div>
    <div class="recent-reviews-section">
        <div class="container">
            <div class="content-box">
                <div class="section-title">
                    <div class="title-wrap">
                        <h2>Recent Reviews</h2>
                        <h5>Buy with Confidence</h5>
                    </div>
                </div>
                @foreach($recent_reviews as $reviews)
                    <div class="section-content">
                        <div class="review-box-col">
                            <div class="property-img-wrap">
                                <a href="{{url('review/'.$reviews['slug'])}}">
                                    <img src="{{asset('images/property-review-img-1.png')}}">
                                </a>

                            </div>
                            <div class="author-detail">
                                <div class="author-top">
                                    <div class="img-wrap">
                                        <img src="{{asset('images/author-1.png')}}">
                                    </div>
                                    <div class="rating-wrap">
                                        <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>
                                        <ul>
                                            <li><img src="{{asset('images/rating-img.png')}}"></li>
                                            <li><img src="{{asset('images/rating-img.png')}}"></li>
                                            <li><img src="{{asset('images/rating-img.png')}}"></li>
                                            <li><img src="{{asset('images/rating-img.png')}}"></li>
                                            <li><img src="{{asset('images/rating-img.png')}}"></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="author-btm">
                                    <p>Reviewed to <a href="#">{{$reviews['property']['project_name']}}</a> </p>
                                </div>
                            </div>
                            <div class="tags-wrap">
                                <ul>
                                    @foreach($reviews['review_tags'] as $reviewTags)
                                        <li><a>{{$reviewTags['name']}}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="btn-wrap">
                                <a class="btn" href="#">Write Review</a>
                                <a class="btn btn-bordered" href="{{url('review/'.$reviews['slug'])}}">More Info</a>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if(!count($recent_reviews) > 0)
                    <h4>No Recent Reviews Found..</h4>
                @endif
            </div>
        </div>
    </div>
@endsection
