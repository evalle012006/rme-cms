@extends('layouts.app-layout')

@section('content')
    <div class="map-section map-section-new">
        <div class="container">
            <div class="map-section-title">
                <div class="srch-wrap">
                    <form action="{{route('general-search')}}">
                        <input type="text" name="q" id="search_key" title="search" placeholder="Search" value="{{$q}}">
                        <button>Search</button>
                    </form>
                </div>
            </div>
            <div class="content-wrap">
                <h3 class="search-page-headings">Properties</h3>
                <div class="content-wrap-row search-reviews">
                    @foreach($properties as $property)
                        <div class="single-box m-t-15">
                            <div class="single-box-in">
                                <img
                                    src="{{$property->is_company && $property->company ? $property->full_company_logo : ($property->full_property_logo ? $property->full_property_logo :asset('images/property-review-img-1.png'))}}"
                                    alt="">
                                <div class="property-img-wrap">
                                    <h4>
                                        @if($property->project_name)
                                            <a href={{url('property-review/'.$property->slug)}}>
                                                {{$property->project_name}}
                                            </a>
                                        @endif

                                        @if($property->company && $property->company->id !== 3 && !$property->project_name)
                                            @if($property->project_name)
                                                by
                                            @endif

                                            <a href="{{url('property/'.$property->company->slug)}}">{{$property->company->company_name}}</a>
                                        @endif
                                    </h4>
                                    <p>Total reviews {{$property->reviews_count}}</p>
                                    <div class="btn-wrap">
                                        <a class="btn" href={{url('review-step-1?')}}>
                                            Write Review
                                        </a>
                                        <a class="btn btn-bordered"
                                           href="{{$property->company && $property->is_company ? url('property/'.$property->company->slug) : url('property-review/'.$property->slug)}}">
                                            More Info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(count($properties)===0)
                    <div>No Properties found</div>
                @endif

                @if(count($properties) < $totalProperties)
                    <button class="btn m-t-15" id="property-show-more">
                        Show More
                    </button>
                @endif
            </div>
            <div class="content-wrap reviews-wrapper">
{{--                display: flex;--}}
{{--                flex-wrap: wrap;--}}
                <h3 class="search-page-headings">Reviews</h3>
                <div class="content-wrap-row search-reviews">
                    @foreach($reviews as $review)
                        <div class="section-content m-t-15">
                            <div class="review-box-col">
                                <div class="img-wrap">
                                    <img
                                        src="{{$review->full_review_image ? $review->full_review_image :asset('images/property-review-img-1.png')}}"
                                        alt="review image">
                                </div>
                                <div class="author-detail">
                                    <div class="author-top">
                                        <div class="rating-wrap">
                                            <div class="rating-left">
                                                <div class="rating-wrap">
                                                    <ul class="general-star-rating overall-rate-color-{{ratingClass($review->avg_rating)}}">
                                                        @foreach(range(0,4) as $number)
                                                            <li>
                                                                <a href="">
                                                                    <img src="{{asset('images/rating-img.png')}}"
                                                                         alt="">
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                            </div>
                                            <h6>{{$review->users->display_name}}</h6>
                                        </div>
                                    </div>
                                    <div class="author-btm">
                                        <p>
                                            <strong>{{$review->users->display_name}}</strong>
                                            reviewed <a
                                                href="{{url('property-review/'.$review->property->slug)}}">{{$review->property->project_name}}</a>
                                        </p>
                                    </div>

                                    <div class="review-text">
                                        <p>{{$review->comment}}</p>
                                    </div>
                                    <div class="tags-wrap">
                                        <ul>
                                            @foreach($review->reviewTags as $reviewTag)
                                                <li><a>{{$reviewTag->name}}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="btn-wrap">
                                        @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                                            <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                                        @endif
                                        <a class="btn btn-bordered"
                                           href="{{url('property-review/'.$review->property->slug)}}">More Info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(count($reviews)===0)
                    <div>No Review found</div>
                @endif


                @if(count($reviews) < $totalReviews)
                    <button class="btn m-t-15" id="review-show-more">
                        Show More
                    </button>
                @endif

            </div>
            <div class="content-wrap blog-wrapper-content">
                <h3 class="search-page-headings">Blogs</h3>
                <div class="blog-content-wrap">
                    @foreach($blogs as $blog)
                        <div class="single-blog-col">
                            <div class="single-blog-col-in">
                                <div class="img-wrap">
                                    <a href="{{url('/blog-detail/'.$blog['slug'])}}">
                                        <img src="{{$blog['full_feature_image']}}" alt="blog">
                                    </a>
                                </div>
                                <div class="content-wrap">
                                    <h4>
                                        <a href="{{url('/blog-detail/'.$blog['slug'])}}">{{trimTitle($blog['title'])}}</a>
                                    </h4>
                                    <div class="meta-wrap">
                                        <ul>
                                            <li class="calender">
                                                <img src="{{url('images/calender-img.png')}}" alt="blog-image">
                                                {{\Carbon\Carbon::parse($blog['created_at'])->format('d M, Y')}}
                                            </li>
                                        </ul>
                                    </div>
                                    <p>{!! trimDescription($blog['description']) !!}</p>
                                    <a class="learn-more" href="{{url('/blog-detail/'.$blog['slug'])}}">Learn More</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(count($blogs)===0)
                    <div>No Blogs found</div>
                @endif
                @if(count($blogs) < $totalBlogs)
                    <button class="btn m-t-15" id="blog-show-more">
                        Show More
                    </button>
                @endif
            </div>
        </div>
    </div>
    {{--    {{\Request::all()}}--}}
@endsection


@section('extra-script')
    <script>
        $(document).ready(function () {
            const q = "{{\Request::get('q')}}";
            let pt = +"{{\Request::get('pt')}}";
            let rt = +"{{\Request::get('rt')}}";
            let bt = +"{{\Request::get('bt')}}";

            if(!pt) pt = 5;
            if(!rt) rt = 5;
            if(!bt) bt = 5;
            console.log(pt);
            $("#property-show-more").click(function () {
                pt += 5;
                redirect();
            });
            $("#review-show-more").click(function () {
                rt += 5;
                redirect();
            });
            $("#blog-show-more").click(function () {
                bt += 5;
                redirect();
            });

            function redirect() {
                const search = 'q=' + q + '&pt=' + pt + '&rt=' + rt + "&bt=" + bt;
                window.location.href = "{{url('/search?')}}" + search;
            }
        })
    </script>
@endsection
