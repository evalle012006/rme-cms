@extends('layouts.app-layout')

@section('content')
    <div class="property-review-detail-section property-review-custom">
        <div class="container">
            <div class="featured-review-wrap">
                <h1 class="custom-title-h1">{{$property['project_name']}}</h1>
                <div class="property-review-content">
                    <div class="property-review-top">
                        @if(!empty($property_review))
                            <div class="avarage-wrap-rating avarage-wrap-rating-custom">
                                <div class="avarage-wrap-rating-left">
                                    <img
                                        src="{{$property['full_property_logo'] ? $property['full_property_logo'] : '/images/homepage/HomepageHero.jpg'}}">
                                </div>
                                <div class="avarage-wrap-rating-right">
                                    <div class="avarage-rating">{{$overall_rate}}<p class="mobile-show">Total
                                            reviews {{count($property_review)}}</p></div>
                                    <div class="review-wrap">
                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($overall_rate)}}">
                                            <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                        </ul>
                                    </div>
                                    <p class="mobile-hide">Total reviews {{count($property_review)}}</p>
                                </div>
                            </div>
                        @endif

                        <div class="property-detail">
                            <p><a>{{$property['project_name']}}</a>

                                @if($property['company'] && $property['company']['id'] !== 3)
                                    by
                                    <a href="{{url('property/'.$property['company']['slug'])}}">{{$property['company']['company_name']}}</a>
                                @endif
                            </p>
                            <div class="rating-right">
                                <div class="rating-list">
                                    @foreach($ratingNumbers as $ratingNumber => $starRating)
                                        <div class="rating-list-single">
                                            <a class="{{request()->get('filter_rating')==$ratingNumber ? 'active-rating': '' }}"
                                               href="{{($starRating[1] == 0 || request()->get('filter_rating')==$ratingNumber) ? request()->url() : request()->url().'?filter_rating='.$ratingNumber}}">
                                                <div class="star-counter">
                                                    <span>{{$ratingNumber}}</span><img
                                                        src="{{asset('images/rating-img-black.png')}}"/>
                                                </div>
                                            </a>

                                            <div class="star-rating-percentage">
                                                <div class="star-total-percentage">
                                                    <div class="star-count-percentage"
                                                         style="width: {{$starRating[1]}}%;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="star-rating-counter">
                                                ({{$starRating[0]}})
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            </p>
                        </div>
                        <div class="btn-wrap">
                            <a class="btn" href="{{url('review-step/'.$property['slug'])}}">Write Review</a>
                        </div>
                    </div>
                    @if(!empty($property_review))
                        <div class="rated-exp">
                            <h6>Rated Experience</h6>
                            <ul>
                                @foreach($avgRateColletion as $avgRate)
                                    <li><span>{{$avgRate['name']}}</span>
                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($avgRate['avg'])}}">
                                            <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                            <div class=" overall-rating-wrap">
                                <h6>Overall Ratings</h6>
                                <ul class="general-star-rating overall-rate-color-{{ratingClass($overall_rate)}}">
                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                </ul>
                            </div>
                        </div>

                        {{--                        <div class="review-footer">--}}
                        {{--                            <div class="useful-wrap">--}}
                        {{--                                <a href="#"><img src="{{asset('images/useful-icon.png')}}">Useful</a>--}}
                        {{--                            </div>--}}
                        {{--                            <div class="share-wrap">--}}
                        {{--                                <a href="#"><img src="{{asset('images/share-icon-grey.png')}}">Share</a>--}}
                        {{--                            </div>--}}
                        {{--                            --}}{{--                            <div class="flag">--}}
                        {{--                            --}}{{--                                <a href="#"><img src="{{asset('images/flag-icon.png')}}"></a>--}}
                        {{--                            --}}{{--                            </div>--}}
                        {{--                        </div>--}}
                    @endif
                </div>
            </div>
            <div class="top-10-reviews-wrap">
                <div class="section-title">
                    <h5>Reviews {{count($property_review)}}</h5>
                    <div class="filter-wrap">
                        <span>Filter By:</span>
                        @if(isset($cat))
                            <form action="{{url('property-review/'.request()->segment(2).'/'.request()->segment(3))}}"
                                  method="GET">
                                <input type="text" name="filter_by" value="{{request()->input('filter_by')}}"
                                       placeholder="Search by review">
                                <input type="submit" name="" value="Search">
                                @else
                                    <form action="{{url('property-review/'.request()->segment(2))}}" method="GET">

                                        <input type="text" name="filter_by" value="{{request()->input('filter_by')}}"
                                               placeholder="Search by review">
                                        <input type="submit" name="" value="Search">
                                    </form>
                        @endif
                    </div>
                </div>
                @foreach($property_review as $propReview)
                    <div class="section-content">
                        <div class="top-single-review">
                            <div class="review-author">
                                <img class="profile-photo"
                                     src="{{$propReview['users']['profile_image'] ? url(Storage::url($propReview['users']['profile_image'])) : asset('images/author-img-grey.png')}}">
                                <div class="author-detail">
                                    <h5>{{$propReview['users']['display_name']}}
                                        {{--                                        ({{$propReview['users']['firstname']. ' '. $propReview['users']['lastname']}}--}}
                                        {{--                                        )--}}
                                    </h5>
                                    <span>{{$propReview['users']['reviews_count']}} {{$propReview['users']['reviews_count'] == 1 ? 'review' : 'reviews'}}</span>
                                </div>
                                <img class="useful-icon {{!$propReview['recommendation'] ? 'rotate-180' : ''}}" src="{{asset('images/thumbs-up.png')}}">
                            </div>
                            <div class="main-review-content">
                                <ul class="rate-star-wrap overall-rate-color-{{ratingClass($propReview['overall_rating'])}}">
                                    <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                    <li><a><img src="{{asset('images/rating-star.png')}}"></a></li>
                                </ul>
                                <div class="time-span">
                                    <span>
                                    @if(Carbon\Carbon::now()->diffInDays($propReview['created_at']) > 0)
                                            {{Carbon\Carbon::now()->diffInDays($propReview['created_at'])}} Days Ago
                                        @elseif(Carbon\Carbon::now()->diffInHours($propReview['created_at']) > 0)
                                            {{Carbon\Carbon::now()->diffInHours($propReview['created_at'])}} Hours Ago
                                        @else
                                            {{Carbon\Carbon::now()->diffInMinutes($propReview['created_at'])}} Minutes
                                            Ago
                                        @endif
                                    </span>
                                </div>
                                <div class="review-title">
                                    <h4>{{$propReview['title']}}</h4>
                                </div>
                                <div class="review-detail-watch">
                                    <div class="show-btn"><span>Show Review Detail <i class="fa fa-chevron-circle-down"
                                                                                      aria-hidden="true"></i></span>
                                    </div>
                                    <div class="content-panel">
                                        <div class="rated-exp">
                                            <h6>Rated Experience</h6>
                                            <ul>
                                                @foreach($propReview['ratings'] as $questionList)
                                                    <li><span>{{$questionList['review_question']['name']}}</span>
                                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($questionList['rate'])}}">
                                                            <li><a><img
                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                            </li>
                                                            <li><a><img
                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                            </li>
                                                            <li><a><img
                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                            </li>
                                                            <li><a><img
                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                            </li>
                                                            <li><a><img
                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="overall-rating-wrap">
                                                <h6>Overall Ratings</h6>
                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($propReview['overall_rating'])}}">
                                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                    <li><a><img src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="comment-tags-wrap">
                                            <h6>Comment Tags</h6>
                                            <div class="tags-wrap">
                                                <ul>
                                                    @foreach($propReview['review_tags'] as $tags)
                                                        <li><a>{{$tags['name']}}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                        @if(!empty($propReview['comment']))
                                            <div class="comment-wrap">
                                                <h6>Comment</h6>
                                                <p>{{$propReview['comment']}}</p>
                                            </div>
                                        @endif
                                        @if(!isset($cat))
                                            <div class="comment-wrap">
                                                <h6>Category</h6>
                                                <p>{{$propReview['category']['name']}}</p>
                                            </div>
                                        @endif
                                        @if(!empty($propReview['full_review_image']))
                                            <div class="photos-wrap">
                                                <h6>Photo</h6>
                                                <div class="photos-row">
                                                    <img class="review-image" src="{{$propReview['full_review_image']}}">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    {{--                                    <div class="review-footer">--}}
                                    {{--                                        <div class="useful-wrap">--}}
                                    {{--                                            <a href="#"><img src="{{asset('images/useful-icon.png')}}">Useful</a>--}}
                                    {{--                                        </div>--}}
                                    {{--                                        <div class="share-wrap">--}}
                                    {{--                                            <a href="#"><img src="{{asset('images/share-icon-grey.png')}}">Share</a>--}}
                                    {{--                                        </div>--}}
                                    {{--                                        --}}{{--                                        <div class="flag">--}}
                                    {{--                                        --}}{{--                                            <a href="#"><img src="{{asset('images/flag-icon.png')}}"></a>--}}
                                    {{--                                        --}}{{--                                        </div>--}}
                                    {{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if(empty($property_review))
                    <h4>No customer review found...</h4>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        const filterRating = {!! json_encode(request()->get('filter_rating')) !!};
        if (filterRating) {
            $('html, body').animate({
                scrollTop: $(".section-title").offset().top
            }, 500);
        }

    </script>
@endsection
