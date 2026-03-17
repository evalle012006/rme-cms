@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{$sectionData['banner']['image'] ? checkUrl($sectionData['banner']['image']) : "images/homepage/HomepageHero.jpg"}});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h2>{{$sectionData['banner']['title']}}</h2>
            @else
                <h2>About Us</h2>
            @endif
            <div class="breadcrumb-wrap">
                <a href="#">Home</a> / <span>About Us</span>
            </div>
        </div>
    </div>

    <div class="get-to-know-section">
        <div class="container">
            <div class="side-title">
                <p>Get to know about us</p>
            </div>
            <div class="img-wrap">
                @if($sectionData['get_to_know']['image'])
                    <img src="{{checkUrl($sectionData['get_to_know']['image'])}}">
                @else
                    <img src="{{asset('images/homepage/AboutUs1.jpg')}}">
                @endif
            </div>
            <div class="content-wrap">
                <h3>Get to know </h3>
                @if($sectionData['get_to_know']['title'])
                    <h2>{{$sectionData['get_to_know']['title']}}</h2>
                @else
                    <h2>About RateMyEstate</h2>
                @endif

                @if($sectionData['get_to_know']['body'])
                    {!! $sectionData['get_to_know']['body'] !!}
                @else
                    <p>When it comes to buying a property you often buy with the ‘artist impression only,’ with a range
                        of
                        renders and images,on a promise that it’ll be luxurious orhigh end, but is it? </p>
                    <p>Are you ready to hand over hundreds of thousands and in some instances millions without knowing
                        who
                        you are buying from? </p>
                @endif
            </div>
        </div>
    </div>

    <div class="how-we-start-section">
        <div class="container">
            <div class="section-title">
                <h3>Our mission</h3>
                @if($sectionData['our_mission']['title'])
                    <h2>{{$sectionData['our_mission']['title']}}</h2>
                @else
{{--                    <h2>We are competitive in Real Estate Solutions.</h2>--}}
                @endif
            </div>
            <div class="section-content">
                <div class="single-col">
                    <div class="icon-wrap">
                        <img src="{{asset('images/start-icon1.png')}}">
                    </div>
                    {{--                    {{json_encode($sectionData['our_mission'])}}}--}}
                    @if($sectionData['our_mission']['other_params'])
                        @if(isset($sectionData['our_mission']['other_params']['card_1']) && $sectionData['our_mission']['other_params']['card_1'])
                            {!! $sectionData['our_mission']['other_params']['card_1'] !!}
                        @else
                            <p>Are you ready to hand over hundreds of thousands and in some instances millions without
                                knowing
                                who you are buying from? </p>
                        @endisset
                    @else
                        <p>Are you ready to hand over hundreds of thousands and in some instances millions without
                            knowing
                            who you are buying from? </p>
                    @endif
                </div>
                <div class="single-col">
                    <div class="icon-wrap">
                        <img src="{{asset('images/start-icon2.png')}}">
                    </div>
                    @if($sectionData['our_mission']['other_params'])
                        @if(isset($sectionData['our_mission']['other_params']['card_2']) && $sectionData['our_mission']['other_params']['card_2'])
                            {!! $sectionData['our_mission']['other_params']['card_2'] !!}
                        @else
                            <p>Build a property review community that helps buyers make a better buying decision</p>
                        @endisset
                    @else
                        <p>Build a property review community that helps buyers make a better buying decision</p>
                    @endif
                </div>
                <div class="single-col">
                    <div class="icon-wrap">
                        <img src="{{asset('images/start-icon3.png')}}">
                    </div>
                    @if($sectionData['our_mission']['other_params'])
                        @if(isset($sectionData['our_mission']['other_params']['card_3']) && $sectionData['our_mission']['other_params']['card_3'])
                            {!! $sectionData['our_mission']['other_params']['card_3'] !!}
                        @else
                            <p>Help buyers navigate the complex homebuying journey</p>
                        @endisset
                    @else
                        <p>Help buyers navigate the complex homebuying journey</p>
                    @endif
                </div>
                <div class="single-col">
                    <div class="icon-wrap">
                        <img src="{{asset('images/start-icon4.png')}}">
                    </div>
                    @if($sectionData['our_mission']['other_params'])
                        @if(isset($sectionData['our_mission']['other_params']['card_4']) && $sectionData['our_mission']['other_params']['card_4'])
                            {!! $sectionData['our_mission']['other_params']['card_4'] !!}
                        @else
                            <p>Become a trusted source in home purchasing journey through shared experiences.</p>
                        @endisset
                    @else
                        <p>Become a trusted source in home purchasing journey through shared experiences.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="solution-section">
        <div class="container">
            <div class="content-wrap">
                @if($sectionData['our_platform']['title'])
                    <h3>{{$sectionData['our_platform']['title']}}</h3>
                @else
                    <h3>Be Confident</h3>
                @endif


                @if($sectionData['our_platform']['body'])
                    {!! $sectionData['our_platform']['body'] !!}
                @else
                    <p>If you are one of the lucky people to afford a home be sure to search the reviews using
                        ratemyestate
                        to gauge the builder or developer you are considering to purchase from.</p>
                    <p> If you have recently bought a property be a hero and rate your builder and developer and reward
                        good
                        performances and help others with their decision.
                    </p>
                @endif
            </div>
            <div class="img-wrap">
                @if($sectionData['our_platform']['image'])
                    <img src="{{checkUrl($sectionData['our_platform']['image'])}}">
                @else
                    <img src="{{asset('images/homepage/AboutUs2.jpg')}}">
                @endif
            </div>
        </div>
    </div>








{{--    <div class="recent-reviews-section">--}}
{{--        <div class="container">--}}
{{--            <div class="content-box">--}}
{{--                <div class="section-title">--}}
{{--                    <div class="title-wrap">--}}
{{--                        <h2>Recent Reviews</h2>--}}
{{--                        @if($sectionData['recent_reviews']['title'])--}}
{{--                            <h5>{{$sectionData['recent_reviews']['title']}}</h5>--}}
{{--                        @else--}}
{{--                            <h5>Buy with Confidence</h5>--}}
{{--                        @endif--}}
{{--                        @if($sectionData['recent_reviews']['body'])--}}
{{--                            {!! $sectionData['recent_reviews']['body'] !!}--}}
{{--                        @else--}}
{{--                            <p>Your home is one of the most expensive assets you will own. Do your research, know who--}}
{{--                                and--}}
{{--                                what you’re dealing with.</p>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                    <div class="btn-wrap">--}}
{{--                        <a href="{{route('review-listing')}}">Show all Reviews</a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="section-content-slider">--}}
{{--                    @foreach($recent_reviews as $reviews)--}}
{{--                        <div class="section-content">--}}
{{--                            <div class="review-box-col">--}}
{{--                                <div class="property-img-wrap">--}}
{{--                                    <a href="{{url('review/'.$reviews['slug'])}}">--}}
{{--                                        <img src="{{asset('images/property-review-img-1.png')}}">--}}
{{--                                    </a>--}}
{{--                                    --}}{{--                            <div class="img-tag">Homesite</div>--}}
{{--                                </div>--}}
{{--                                <div class="author-detail">--}}
{{--                                    <div class="author-top">--}}
{{--                                        <div class="img-wrap">--}}
{{--                                            <img src="{{asset('images/author-1.png')}}">--}}
{{--                                        </div>--}}
{{--                                        <div class="rating-wrap">--}}
{{--                                            <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>--}}
{{--                                            <ul>--}}
{{--                                                <li><img src="{{asset('images/rating-img.png')}}"></li>--}}
{{--                                                <li><img src="{{asset('images/rating-img.png')}}"></li>--}}
{{--                                                <li><img src="{{asset('images/rating-img.png')}}"></li>--}}
{{--                                                <li><img src="{{asset('images/rating-img.png')}}"></li>--}}
{{--                                                <li><img src="{{asset('images/rating-img.png')}}"></li>--}}
{{--                                            </ul>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="author-btm">--}}
{{--                                        <p>Reviewed to <a href="#">{{$reviews['property']['project_name']}}</a></p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="tags-wrap">--}}
{{--                                    <ul>--}}
{{--                                        @foreach($reviews['review_tags'] as $reviewTags)--}}
{{--                                            <li><a href="#">{{$reviewTags['name']}}</a></li>--}}
{{--                                        @endforeach--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                                <div class="btn-wrap">--}}
{{--                                    <a class="btn" href="{{url('review-step/'.$reviews['slug'])}}">Write Review</a>--}}
{{--                                    <a href="{{url('review/'.$reviews['slug'])}}" class="btn btn-bordered">More Info</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}


{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--                @if(!count($recent_reviews) > 0)--}}
{{--                    <h4>No Recent Reviews Found..</h4>--}}
{{--                @endif--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}




    <div class="recent-reviews-section recent-reviews-custom">
        <div class="container">
            <div class="content-box">
                <div class="section-title">
                    <div class="title-wrap">
                        <h2>Recent Reviews</h2>
                        @if($sectionData['recent_reviews']['title'])
                            <h5>{{$sectionData['recent_reviews']['title']}}</h5>
                        @else
                            <h5>Buy with Confidence</h5>
                        @endif
                        @if($sectionData['recent_reviews']['body'])
                            {!! $sectionData['recent_reviews']['body'] !!}
                        @else
                            <p>Your home is one of the most expensive assets you will own. Do your research, know who
                                and
                                what you’re dealing with.</p>
                        @endif

                    </div>
                    <div class="btn-wrap">
                        <a href="{{route('property-listing')}}">Show all Reviews</a>
                    </div>
                </div>
                <div class="section-content-slider desktop-view">
                    @foreach($recent_reviews as $reviews)
                        <div class="section-content section-content-custom">
                            <div class="review-box-col">
                                <div class="review-box-col-in">
                                    <div class="property-img-wrap" style="display: none;">
                                        <a href="{{url('review/'.$reviews['slug'])}}">
                                            <img
                                                src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                        </a>
                                        {{--                            <div class="img-tag">Homesite</div>--}}
                                    </div>
                                    <div class="author-detail-wrapper">
                                        <div class="author-detail">
                                            <div class="author-top">
                                                <div class="img-wrap">
                                                    {{--                                            <img--}}
                                                    {{--                                                src="{{$reviews['users']['full_profile_image'] ?$reviews['users']['full_profile_image'] :  asset('images/author-1.png')}}">--}}
                                                    <img
                                                        src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                                </div>
                                                <div class="rating-wrap">
                                                    <div class="rating-left">
                                                        <div class="rating-wrap">
                                                            <ul class="general-star-rating overall-rate-color-{{ratingClass($reviews['avg_rating'])}}">
                                                                @foreach(range(0,4) as $number)
                                                                    <li><a href=""><img
                                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                    </div>
                                                    <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>

                                                </div>
                                            </div>
                                            <div class="author-btm">
                                                <p><strong>{{$reviews['users']['display_name']}}</strong> reviewed <a
                                                        href="{{url('property-review/'.$reviews['property']['slug'])}}">{{$reviews['property']['project_name']}}</a>
                                                </p>
                                            </div>

                                            <div class="review-text">
                                                <p>
                                                    {{strlen($reviews['comment']) > 30 ? substr($reviews['comment'], 0, 30).'...': $reviews['comment']}}
                                                </p>
                                            </div>
                                        </div>
                                        @if(count($reviews['review_tags'])>0)
                                            <div class="tags-wrap">
                                                <ul>
                                                    @foreach($reviews['review_tags'] as $reviewTags)
                                                        <li><a>{{$reviewTags['name']}}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="review-box-col-new">
                                    <div class="btn-wrap">
                                        @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                                            <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                                        @endif
                                        <a class="btn btn-bordered"
                                           href="{{url('property-review/'.$reviews['property']['slug'])}}">More Info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="section-content-slider mobile-view">
                    @foreach($recent_reviews as $reviews)
                        <div class="section-content section-content-custom">
                            <div class="review-box-col">
                                <div class="property-img-wrap" style="display: none;">
                                    <a href="{{url('review/'.$reviews['slug'])}}">
                                        <img
                                            src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                    </a>
                                    {{--                            <div class="img-tag">Homesite</div>--}}
                                </div>
                                <div class="author-detail-wrapper">
                                    <div class="author-detail">
                                        <div class="author-top">
                                            <div class="img-wrap">
                                                {{--                                            <img--}}
                                                {{--                                                src="{{$reviews['users']['full_profile_image'] ?$reviews['users']['full_profile_image'] :  asset('images/author-1.png')}}">--}}
                                                <img
                                                    src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                            </div>
                                            <div class="rating-wrap">
                                                <div class="rating-left">
                                                    <div class="rating-wrap">
                                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($reviews['avg_rating'])}}">
                                                            @foreach(range(0,4) as $number)
                                                                <li><a href=""><img
                                                                            src="{{asset('images/rating-img.png')}}"></a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>

                                                </div>
                                                <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>

                                            </div>
                                        </div>
                                        <div class="author-btm">
                                            <p><strong>{{$reviews['users']['display_name']}}</strong> reviewed <a
                                                    href="{{url('property-review/'.$reviews['property']['slug'])}}">{{$reviews['property']['project_name']}}</a>
                                            </p>
                                        </div>

                                        <div class="review-text">
                                            <p>
                                                {{strlen($reviews['comment']) > 30 ? substr($reviews['comment'], 0, 30).'...': $reviews['comment']}}
                                            </p>
                                        </div>
                                    </div>
                                    @if(count($reviews['review_tags'])>0)
                                        <div class="tags-wrap">
                                            <ul>
                                                @foreach($reviews['review_tags'] as $reviewTags)
                                                    <li><a>{{$reviewTags['name']}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="review-box-col-new">
                                <div class="btn-wrap">
                                    @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                                        <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                                    @endif
                                    <a class="btn btn-bordered"
                                       href="{{url('property-review/'.$reviews['property']['slug'])}}">More Info</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(!count($recent_reviews) > 0)
                    <h4>No Recent Reviews Found..</h4>
                @endif
                @if ($message = \Session::get('error'))
                    <div class="alert alert-danger alert-block">
                        {{--                                <button type="button" class="close" data-dismiss="alert">×</button>--}}
                        <strong class="text-danger">{{ $message }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>



@endsection
