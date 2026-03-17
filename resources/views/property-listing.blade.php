@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <h2>Reviews Listing</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Reviews Listing</span>
            </div>
        </div>
    </div>
    <div class="review-listing-section property-review-listing">
        <div class="container">
            <div class="review-box-wrap">
                <h3>Reviews Listing</h3>
                <div class="view-filter">
                    <h4>View by:</h4>
                    <div class="view-row">
                        @if(strpos(request()->url(),'property_search')!==false)
                            <a href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>2]))}}">
                                <div class="4-grid">
                                    <img src="{{asset('images/4-grid.png')}}">
                                </div>
                            </a>
                            <a href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>1]))}}">
                                <div class="9-grid">
                                    <img src="{{asset('images/9-grid.png')}}">
                                </div>
                            </a>
                            <a href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>3]))}}">
                                <div class="list-view">
                                    <img src="{{asset('images/list.png')}}">
                                </div>
                            </a>
                        @else
                            @empty($cat_slug)
                                <a href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>2]))}}">
                                    <div class="4-grid">
                                        <img src="{{asset('images/4-grid.png')}}">
                                    </div>
                                </a>
                                <a href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>1]))}}">
                                    <div class="9-grid">
                                        <img src="{{asset('images/9-grid.png')}}">
                                    </div>
                                </a>
                                <a href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>3]))}}">
                                    <div class="list-view">
                                        <img src="{{asset('images/list.png')}}">
                                    </div>
                                </a>
                            @else
                                <a href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>2],['slug'=>$cat_slug]))}}">
                                    <div class="4-grid">
                                        <img src="{{asset('images/4-grid.png')}}">
                                    </div>
                                </a>
                                <a href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>1],['slug'=>$cat_slug]))}}">
                                    <div class="9-grid">
                                        <img src="{{asset('images/9-grid.png')}}">
                                    </div>
                                </a>
                                <a href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>3],['slug'=>$cat_slug]))}}">
                                    <div class="list-view">
                                        <img src="{{asset('images/list.png')}}">
                                    </div>
                                </a>
                            @endempty
                        @endif
                    </div>
                </div>
                <div class="filter-row">
                    <form
                        action="{{isset($cat_slug) ? route('property_search_slug',$cat_slug) : route('property_search')}}"
                        method="GET">
                        <input type="hidden" name="view_type" value="{{request()->get('view_type',1)}}">
                        <div class="sort-filter">
                            <h4>Filter by:</h4>

                            <div class="filter-wrap">
                                <div class="cat-filter">
                                </div>

                                <div class="filter-srch-row">
                                    <input type="text" name="filter_by" value="{{request()->input('filter_by')}}"
                                           placeholder="Search by Suburb">
                                    {{--                                    <input type="hidden" name="is_slug"--}}
                                    {{--                                           value="{{request()->segment(2) ?? request()->input('is_slug')}}">--}}
                                    <input type="text" name="search_key" value="{{request()->input('search_key')}}"
                                           placeholder="Search by Project Name">
                                    <input type="text" name="search_company"
                                           value="{{request()->input('search_company')}}"
                                           placeholder="Search by Company Name">
                                    <input type="submit" name="" value="Search">

                                </div>
                                <div class="advance-srch-drpdwn">
                                    <h6>Advanced Search</h6>
                                    <div class="filter-dropdown">
                                        <div class="sort-filter-col">
                                            @if(!isset($cat_slug) || $cat_slug=='')
                                                <div class="cat-filter">
                                                    <h6>Sort Category</h6>
                                                    @foreach($property_category as $propertyCat)
                                                        <label>
                                                            <input type="checkbox" name="category_type[]"
                                                                   value="{{$propertyCat['id']}}" {{ setSortCategorySelected($propertyCat['id']) }}
                                                            >
                                                            <span>{{$propertyCat['name']}}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="rating-filter">
                                                <h6>Sort By Ratings</h6>
                                                @foreach(array_reverse(range(1,5)) as $number)
                                                    <label>
                                                        <input type="checkbox" name="rating[]"
                                                               value={{$number}} {{request()->input('rating') ?( array_search($number,request()->input('rating'))!==false ? 'checked' : '' ): ''}}>
                                                        <span><img
                                                                src="{{url('images/'.$number.'-star-rating.png')}}"></span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="review-tag-filter">
                                                <h6>Sort By Review Tags</h6>
                                                <ul>
                                                    @foreach($tags as $tag)
                                                        <li>
                                                            <label>
                                                                <input type="checkbox" name="tags[]"
                                                                       value={{$tag->id}} {{request()->input('tags') ?( array_search($tag->id,request()->input('tags'))!==false ? 'checked' : '' ): ''}}>
                                                                <span>{{$tag->name}}</span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{--                {{$tags}}--}}
                <div id="list1" class="box-listing ">
                    @if($view_type != 3)
                        @foreach($properties as $propertiesData)
                            <div class="single-box {{$view_type==2 ? 'two-col' : ''}}">
                                <div class="single-box-height properties-custom-wrap">
                                    <div class="properties-author-wtap">
                                        <div class="properties-top-bottom">
                                            <div class="properties-top">
                                                <div class="properties-top-left">
                                                    <div class="property-img-wrap">
                                                        <img
                                                            src="{{$propertiesData->is_company ? $propertiesData->full_company_logo : ($propertiesData->full_property_logo ? $propertiesData->full_property_logo :asset('images/property-review-img-1.png'))}}">
                                                        @if(isset($cat_slug) && $cat_slug)
                                                            <div class="img-tag">{{strtoupper($cat_slug)}}</div>

                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="properties-top-right">
                                                    <p class="property-title">
                                                        @if($propertiesData->project_name)
                                                            <a href={{isset($cat_slug) && $cat_slug ? url('property-review/'.$cat_slug.'/'.$propertiesData->slug) : url('property-review/'.$propertiesData->slug)}}>
                                                                {{$propertiesData->project_name}}
                                                            </a>

                                                        @endif

                                                        @if($propertiesData->company && $propertiesData->company->id !== 3 && !$propertiesData->project_name)
                                                            @if($propertiesData->project_name)
                                                                by
                                                            @endif
                                                            @if(isset($cat_slug) && $cat_slug)
                                                                <a href="{{url('property/'.$cat_slug.'/'.$propertiesData->company->slug)}}">{{$propertiesData->company->company_name}}</a>
                                                            @else
                                                                <a href="{{url('property/'.$propertiesData->company->slug)}}">{{$propertiesData->company->company_name}}</a>
                                                            @endif

                                                        @endif
                                                    </p>
                                                    <p>Total
                                                        reviews {{$propertiesData->review_count ? $propertiesData->review_count : count($propertiesData->reviews)}}</p>
                                                    <div class="rating-wrap">
                                                        <span>Ratings:</span>
                                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($propertiesData->overall_rating)}}">
                                                            @foreach(range(0,4) as $number)
                                                                <li><a href=""><img
                                                                            src="{{asset('images/rating-img.png')}}"></a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        <a href="javascript:;" class="view-rating">View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="properties-bottom">
                                                <div class="properties-rating-list">
                                                    <div class="rating-list">
                                                        @foreach($propertiesData->rating_number as $ratingNumber => $starRating)
                                                            <div class="rating-list-single">
                                                                <div class="star-counter">
                                                                    <span>{{$ratingNumber}}</span><img
                                                                        src="{{asset('images/rating-img-black.png')}}"/>
                                                                </div>
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
                                            </div>
                                        </div>

                                        <div class="author-detail">
                                            <div class="author-top">

                                            </div>
                                            <div class="author-btm">

                                                {{-- <a href="javascript:;" class="show-more">Show more</a>--}}
                                                @if($propertiesData->is_company)
                                                    <div class="main-rating-new">
                                                        <div class="rating-info" style="display: none;">
                                                            <div class="rating-left">
                                                                <div
                                                                    class="avarage-rating">{{$propertiesData->overall_rating}}</div>
                                                                <div class="rating-wrap">
                                                                    <ul class="general-star-rating overall-rate-color-{{ratingClass($propertiesData->overall_rating)}}">
                                                                        @foreach(range(0,4) as $number)
                                                                            <li><a href=""><img
                                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                                <p>Total
                                                                    reviews {{$propertiesData->review_count ? $propertiesData->review_count : count($propertiesData->reviews)}}</p>
                                                            </div>
                                                            <div class="rating-right">
                                                                <div class="rating-list">

                                                                    @foreach($propertiesData->rating_number as $ratingNumber => $starRating)
                                                                        <div class="rating-list-single">
                                                                            <div class="star-counter">
                                                                                <span>{{$ratingNumber}}</span><img
                                                                                    src="{{asset('images/rating-img-black.png')}}"/>
                                                                            </div>
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
                                                        </div>
                                                        <div class="custom-rating-wrap">
                                                            <div class="more-details-section">
                                                                @if(isset($cat_slug) && $cat_slug)
                                                                    <a
                                                                        href="{{$propertiesData->is_company ? url('property/'.$cat_slug.'/'.$propertiesData->company->slug) : url('property-review/'.$cat_slug.'/'.$propertiesData->slug)}}">
                                                                        <h6>Show More <i
                                                                                class="fa fa-plus-circle"></i></h6>
                                                                    </a>
                                                                @else
                                                                    <a
                                                                        href="{{$propertiesData->is_company ? url('property/'.$propertiesData->company->slug) : url('property-review/'.$propertiesData->slug)}}">
                                                                        <h6>Show More <i
                                                                                class="fa fa-plus-circle"></i></h6>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            @foreach((count($propertiesData->company_properties) > 3 ? array_slice($propertiesData->company_properties,0,3) : $propertiesData->company_properties) as $propertyName=>$ratingData)
                                                                <div class="rating-wrap-main">
                                                                    <a href={{$cat_slug ? url('property-review/'.$cat_slug.'/'.$ratingData['slug']) : url('property-review/'.$ratingData['slug'])}}>{{$propertyName}}</a>
                                                                    <div class="rating-wrap">
                                                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($ratingData['rate'])}}">
                                                                            @foreach(range(0,4) as $number)
                                                                                <li><a href=""><img
                                                                                            src="{{asset('images/rating-img.png')}}"></a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="main-rating-new">
                                                        <div class="rating-info" style="display: none;">
                                                            <div class="rating-left">
                                                                <div
                                                                    class="avarage-rating">{{$propertiesData->overall_rating}}</div>
                                                                <div class="rating-wrap">
                                                                    <ul class="general-star-rating overall-rate-color-{{ratingClass($propertiesData->overall_rating)}}">
                                                                        @foreach(range(0,4) as $number)
                                                                            <li><a href=""><img
                                                                                        src="{{asset('images/rating-img.png')}}"></a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                                <p>Total
                                                                    reviews {{$propertiesData->review_count ? $propertiesData->review_count : count($propertiesData->reviews)}}</p>
                                                            </div>
                                                            <div class="rating-right">
                                                                <div class="rating-list">

                                                                    @foreach($propertiesData->rating_number as $ratingNumber => $starRating)
                                                                        <div class="rating-list-single">
                                                                            <div class="star-counter">
                                                                                <span>{{$ratingNumber}}</span><img
                                                                                    src="{{asset('images/rating-img-black.png')}}"/>
                                                                            </div>
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
                                                        </div>

                                                        <div class="custom-rating-wrap">
                                                            <div class="more-details-section">
                                                                @if(isset($cat_slug) && $cat_slug)
                                                                    <a
                                                                        href="{{$propertiesData->is_company ? url('property/'.$cat_slug.'/'.$propertiesData->company->slug) : url('property-review/'.$cat_slug.'/'.$propertiesData->slug)}}">
                                                                        <h6>Show More <i
                                                                                class="fa fa-plus-circle"></i></h6>
                                                                    </a>
                                                                @else
                                                                    <a
                                                                        href="{{$propertiesData->is_company ? url('property/'.$propertiesData->company->slug) : url('property-review/'.$propertiesData->slug)}}">
                                                                        <h6>Show More <i
                                                                                class="fa fa-plus-circle"></i></h6>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            @foreach((count($propertiesData->ratings_by_title) > 3 ? array_slice($propertiesData->ratings_by_title,0,3) : $propertiesData->ratings_by_title) as $queName=>$rating)
                                                                @if($rating > 0)
                                                                    <div class="rating-wrap-main">
                                                                        <div class="rating-wrap-main-in">
                                                                            <p>{{$queName}}</p>
                                                                            <div class="rating-wrap">
                                                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($rating)}}">
                                                                                    @foreach(range(0,4) as $number)
                                                                                        <li><a href=""><img
                                                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tags-wrap">
                                        <ul>
                                            @isset($propertiesData->review_tags)
                                                @foreach($propertiesData->review_tags as $tag)
                                                    <li><a href="javascript:void(0)">{{$tag}}</a></li>
                                                @endforeach
                                            @endisset
                                        </ul>
                                    </div>
                                    @isset($propertiesData->last_reviews)
                                        <div class="listing-reviews-wrap">
                                            <h5>List of recent reviews : </h5>
                                            <ul class="listing-reviews">
                                                @foreach($propertiesData->last_reviews as $review)
                                                    <li>{{$review['title']}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endisset
                                </div>
                                <div class="btn-wrap">
                                    <a class="btn" href={{url('review-step-1?'.(isset($cat_slug) ? $cat_slug : ''))}}>Write
                                        Review</a>
                                    @if(isset($cat_slug) && $cat_slug)
                                        <a class="btn btn-bordered"
                                           href="{{$propertiesData->is_company ? url('property/'.$cat_slug.'/'.$propertiesData->company->slug) : url('property-review/'.$cat_slug.'/'.$propertiesData->slug)}}">More
                                            Info</a>
                                    @else
                                        <a class="btn btn-bordered"
                                           href="{{$propertiesData->is_company ? url('property/'.$propertiesData->company->slug) : url('property-review/'.$propertiesData->slug)}}">More
                                            Info</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        @foreach($properties as $propertiesData)
                            <div class="property-wise-reviews-wrap company-info property-wise-reviews-wrap-custom">
                                <div class="property-wise-reviews-single">
                                    <div class="property-img-wrap">
                                        <img
                                            src="{{$propertiesData->is_company ? $propertiesData->full_company_logo : ($propertiesData->full_property_logo ? $propertiesData->full_property_logo :asset('images/property-review-img-1.png'))}}">
                                        @if(isset($cat_slug) && $cat_slug)
                                            <div class="img-tag">{{strtoupper($cat_slug)}}</div>

                                        @endif
                                    </div>
                                    <div class="property-detail">
                                        <div class="property-reviews">
                                            <div class="author-btm">
                                                {{--                                                    <p>--}}
                                                {{--                                                        <a onclick="event.stopPropagation();"--}}
                                                {{--                                                           href="{{url('property-review/'.$property->slug)}}">{{$property->project_name}}</a>--}}
                                                {{--                                                    </p>--}}
                                                @if($propertiesData->project_name)
                                                    <a href={{isset($cat_slug) && $cat_slug ? url('property-review/'.$cat_slug.'/'.$propertiesData->slug) : url('property-review/'.$propertiesData->slug)}}>
                                                        {{$propertiesData->project_name}}
                                                    </a>

                                                @endif

                                                @if($propertiesData->company && $propertiesData->company->id !== 3 && !$propertiesData->project_name)
                                                    @if($propertiesData->project_name)
                                                        by
                                                    @endif
                                                    @if(isset($cat_slug) && $cat_slug)
                                                        <a href="{{url('property/'.$cat_slug.'/'.$propertiesData->company->slug)}}">{{$propertiesData->company->company_name}}</a>
                                                    @else
                                                        <a href="{{url('property/'.$propertiesData->company->slug)}}">{{$propertiesData->company->company_name}}</a>
                                                    @endif

                                                @endif
                                            </div>
                                            <div class="rating-wrap">
                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($propertiesData->overall_rating)}}">
                                                    @foreach(range(0,4) as $number)
                                                        <li><a><img
                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <a href="javascript:;" class="show-more"><i class="fa fa-chevron-down"
                                                                                            aria-hidden="true"></i></a>
                                            </div>
                                        </div>
                                        <div class="total-reviews">
                                            <div class="title">Total Reviews</div>
                                            <div
                                                class="review-count">{{$propertiesData->review_count ? $propertiesData->review_count : count($propertiesData->reviews)}}</div>
                                        </div>
                                        <div class="btn-wrap">
                                            {{--                                                <a class="btn btn-bordered" onclick="event.stopPropagation();"--}}
                                            {{--                                                   href="{{url('property-review/'.$property->slug)}}">More Info</a>--}}
                                            @if(isset($cat_slug) && $cat_slug)
                                                <a class="btn btn-bordered"
                                                   href="{{$propertiesData->is_company ? url('property/'.$cat_slug.'/'.$propertiesData->company->slug) : url('property-review/'.$cat_slug.'/'.$propertiesData->slug)}}">More
                                                    Info</a>
                                            @else
                                                <a class="btn btn-bordered"
                                                   href="{{$propertiesData->is_company ? url('property/'.$propertiesData->company->slug) : url('property-review/'.$propertiesData->slug)}}">More
                                                    Info</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="more-details-section">
                                    <div class="content-panel">
                                        @if($propertiesData->is_company)
                                            <div class="more-details-section">
                                                <div class="content-panel">
                                                    <div class="rating-wrapper-in">
                                                        @foreach($propertiesData->company_properties as $propertyName=>$ratingData)
                                                            <div class="rating-wrap">
                                                                <a href={{$cat_slug ? url('property-review/'.$cat_slug.'/'.$ratingData['slug']) : url('property-review/'.$ratingData['slug'])}}>{{$propertyName}}</a>
                                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($ratingData['rate'])}}">
                                                                    @foreach(range(0,4) as $number)
                                                                        <li><a href="javascript:void(0)"><img
                                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="more-details-section">
                                                <div class="content-panel">
                                                    <div class="rating-wrapper-in">
                                                        @foreach($propertiesData->ratings_by_title as $title=> $titleRate)
                                                            <div class="rating-wrap">
                                                                <p>{{$title}}</p>
                                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($titleRate)}}">
                                                                    @foreach(range(0,4) as $number)
                                                                        <li><a href="javascript:void(0)"><img
                                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif



                    {{$properties->appends(request()->input())->links()}}
                    @if(!(count($properties) > 0))
                        <div class="fa-2x"> No Record Found..</div>
                    @endif
                </div>


            </div>
        </div>
    </div>
@endsection
