@extends('layouts.app-layout')

@section('content')
    <div class="review-detail-content-wrap property-detail-wrap">
        <div class="container">
            <div class="featured-review-box">
                <div class="logo-wrap">
                    <img
                        src="{{$company->full_company_logo ? $company->full_company_logo : asset('images/homepage/HomepageHero.jpg')}}">
                </div>
                <div class="review-content">
                    <h3>{{$company->company_name}}</h3>
                    <div class="review-count">Reviews {{$totalReviews}} <span class="level">Excellent</span></div>
                    <div class="rating-wrap">
                        <ul class="general-star-rating overall-rate-color-{{ratingClass($avg_of_all)}}">
                            <li><a href="#"><img src="{{asset('images/rating-img.png')}}"></a></li>
                            <li><a href="#"><img src="{{asset('images/rating-img.png')}}"></a></li>
                            <li><a href="#"><img src="{{asset('images/rating-img.png')}}"></a></li>
                            <li><a href="#"><img src="{{asset('images/rating-img.png')}}"></a></li>
                            <li><a href="#"><img src="{{asset('images/rating-img.png')}}"></a></li>
                        </ul>
                        {{--                        <img src="{{asset('images/overall-5star.png')}}">--}}
                    </div>
                </div>
                @if($company->website)
                    <div class="web-detail-wrap">
                        <a href="{{validateUrl($company->website)}}" target="_blank"><img
                                src="{{asset('images/link-img.png')}}">{{$company->website}}
                        </a>
                        <a href="{{validateUrl($company->website)}}" target="_blank" class="btn">Visit this website</a>
                    </div>
                @endif
            </div>
            <div class="review-left-part">
                <div class="property-wise-reviews-wrap company-info property-wise-reviews-wrap-custom">
                    @foreach($properties as $property)
                        <div class="property-wise-reviews-single">
                            <div class="property-img-wrap">
                                <img
                                    src="{{$property->full_property_logo ? $property->full_property_logo : asset('images/featured-property-review-img.jpg')}}">
                            </div>
                            <div class="property-detail">
                                <div class="property-reviews">
                                    <div class="author-btm">
                                        <p>
                                            <a onclick="event.stopPropagation();"
                                               href="{{url('property-review/'.$property->slug)}}">{{$property->project_name}}</a>
                                        </p>
                                    </div>
                                    <div class="rating-wrap">
                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($property->avg_rating)}}">
                                            @foreach(range(0,4) as $number)
                                                <li><a><img
                                                            src="{{asset('images/rating-img.png')}}"></a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <a href="javascript:void(0);" class="show-more">
                                            <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="total-reviews">
                                    <div class="title">Total Reviews</div>
                                    <div class="review-count-center">{{count($property->reviews)}}</div>
                                </div>
                                <div class="btn-wrap">
                                    <a class="btn btn-bordered" onclick="event.stopPropagation();"
                                       href="{{url('property-review/'.$property->slug)}}">More Info</a>
                                </div>
                            </div>
                        </div>
                        <div class="more-details-section">
                            <div class="content-panel">
                                @if($property->que_ratings && count($property->que_ratings) > 0)
                                    <div class="rating-wrapper-in">
                                        @foreach($property->que_ratings as $title=> $titleRate)
                                            <div class="rating-wrap">
                                                <p>{{$title}}</p>
                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($titleRate)}}">
                                                    <li><a href="#"><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a href="#"><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a href="#"><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a href="#"><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a href="#"><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="review-right-part">
                <div class="about-company">
                    <div class="content">
                        <p>{{$company->bio}}</p>
                    </div>
                </div>
                <div class="contact-info-wrap">
                    <h6>Contact</h6>
                    <ul>
                        @if($company->website)
                            <li class="email"><a href="{{validateUrl($company->website)}}" target="_blank">{{$company->website}}</a></li>
                        @endif
                        @if($company->contact_number)
                            <li class="phone"><a href="tel:{{$company->contact_number}}">{{$company->contact_number}}</a></li>
                        @endif
                        @if($company->location)
                            <li class="address">{{$company->location}}</li>
                        @endif
                    </ul>
                </div>
                <div class="share-rating">
                    <h6>Share the company's rating</h6>
                    <ul>
                        <li><a href="https://www.facebook.com/sharer/sharer.php?u={{\Request::url()}}" target="_blank"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                        <li><a href="https://twitter.com/intent/tweet?url={{\Request::url()}}" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                    </ul>
                </div>
                <div class="category-wrap">
                    <h6>Category</h6>
                    <ul>
                        @foreach($property_category as $cat)
                            <li><a href="{{route('property-listing-with-slug',['slug'=>$cat['slug']])}}">{{$cat['name']}}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
