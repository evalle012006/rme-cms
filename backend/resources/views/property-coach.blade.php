@extends('layouts.app-layout')

@section('meta_title', 'Property Blog - Real Estate Advice & Guides | ' . seoSiteName())
@section('meta_description', seoText($sectionData['banner']['description'] ?? 'Get expert property advice, buying guides and finance tips from the ' . seoSiteName() . ' Property Blog.'))

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{ $sectionData['banner']['image'] ? absoluteStorageUrl($sectionData['banner']['image']) : asset('images/homepage/HomepageHero.jpg') }});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h1>{{$sectionData['banner']['title']}}</h1>
            @else
{{-- CMS fallback (kept for later restore)
<h1>Property Blog</h1>
            --}}
@endif
            {{--            <h2>Property Blog</h2>--}}
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Property Blog</span>
            </div>
        </div>
    </div>

    <div class="blog-listing-section">
        <div class="container">
            <div class="popular-blogs-wrap">
                <div class="section-title">
                    <h3>Popular Blogs</h3>
                    <div class="btn-wrap">
                        <a href="{{ url('blog-posts') }}">Show All Blogs</a>
                    </div>
                </div>
                <div class="section-content">
                    <div class="posts-wrap">
                        @if($recent_blogs)
                            @foreach($recent_blogs as $recentBlogs)
                                <div class="single-post-wrap">
                                    <div class="img-wrap">
                                        <img src="{{$recentBlogs['full_feature_image']}}">
                                    </div>
                                    <div class="blog-detail">
                                        <h4>
                                            <a href="{{url('/blog-detail/'.$recentBlogs['slug'])}}">{{$recentBlogs['title']}}</a>
                                        </h4>
                                        <p>{!! trimDescription($recentBlogs['description']) !!}</p>
                                        <a class="learn-more" href="{{url('/blog-detail/'.$recentBlogs['slug'])}}"
                                           tabindex="-1">Learn More</a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="finance-section">
        <div class="container">
            <div class="section-title">
                <h3>Finance</h3>
                <div class="btn-wrap">
                    <a href="{{ url('blog-posts') }}">Show All Blogs</a>
                </div>
            </div>
            <div class="section-content">
                <div class="finance-post-wrap">
                    <div class="featured-post">
                        @if(count($recent_finance) > 0)
                            <div class="single-post-wrap">
                                <div class="img-wrap">
                                    <img src="{{$recent_finance[0]['full_feature_image']}}">
                                </div>
                                <div class="blog-detail">
                                    <h4>
                                        <a href="{{url('/blog-detail/'.$recent_finance[0]['slug'])}}">{{$recent_finance[0]['title']}}</a>
                                    </h4>
                                    <p>{!! trimDescription($recent_finance[0]['description']) !!}</p>
                                    <a class="learn-more" href="{{url('/blog-detail/'.$recent_finance[0]['slug'])}}"
                                       tabindex="-1">Learn More</a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="other-posts-wrap">
                        @foreach($recent_finance as $recentFinance)
                            @if(!$loop->first)
                                <div class="single-post-wrap">
                                    <div class="img-wrap">
                                        <img src="{{$recentFinance['full_feature_image']}}">
                                    </div>
                                    <div class="post-detail">
                                        <span>{{parseDate($recentFinance['blog_date'])}}</span>
                                        <h5>
                                            <a href="{{url('/blog-detail/'.$recentFinance['slug'])}}">{!! $recentFinance['title'] !!}</a>
                                        </h5>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="categories-section building-section">
        <div class="container">
            <div class="section-title">
                <h3>Building </h3>
                <div class="btn-wrap">
                    <a href="{{ url('blog-posts') }}">Show All Blogs</a>
                </div>
            </div>
            <div class="section-content">
                <div class="building-post-wrap section-content-slider">
                    @if($recent_building)
                        @foreach($recent_building as $recentBuilding)
                            <div class="single-build-post">
                                <img src="{{$recentBuilding['full_feature_image']}}">
                                <h6>
                                    <a href="{{url('/blog-detail/'.$recentBuilding['slug'])}}">{{$recentBuilding['title']}}</a>
                                </h6>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="blog-listing-section investment-section">
        <div class="container">
            <div class="popular-blogs-wrap">
                <div class="section-title">
                    <h3>Investment</h3>
                    <div class="btn-wrap">
                        <a href="{{ url('blog-posts') }}">Show All Blogs</a>
                    </div>
                </div>
                <div class="section-content">
                    <div class="posts-wrap">
                        @if($recent_investment)
                            @foreach($recent_investment as $recentInvestment)
                                <div class="single-post-wrap">
                                    <div class="img-wrap">
                                        <img src="{{$recentInvestment['full_feature_image']}}">
                                    </div>
                                    <div class="blog-detail">
                                        <h4>
                                            <a href="{{url('/blog-detail/'.$recentInvestment['slug'])}}">{{$recentInvestment['title']}}</a>
                                        </h4>
                                        <p>{!! trimDescription($recentInvestment['description']) !!}</p>
                                        <a class="learn-more" href="{{url('/blog-detail/'.$recentInvestment['slug'])}}"
                                           tabindex="-1">Learn More</a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="finance-section buying-section">
        <div class="container">
            <div class="section-title">
                <h3>Buying</h3>
                <div class="btn-wrap">
                    <a href="{{ url('blog-posts') }}">Show All Blogs</a>
                </div>
            </div>
            <div class="section-content">
                <div class="finance-post-wrap">
                    <div class="featured-post">
                        @if(count($recent_buying)>0)
                            <div class="single-post-wrap">
                                <div class="img-wrap">
                                    <img src="{{$recent_buying[0]['full_feature_image']}}">
                                </div>
                                <div class="blog-detail">
                                    <h4>
                                        <a href="{{url('/blog-detail/'.$recent_buying[0]['slug'])}}">{{$recent_buying[0]['title']}}</a>
                                    </h4>
                                    <p>{!! trimDescription($recent_buying[0]['description']) !!}</p>
                                    <a class="learn-more" href="{{url('/blog-detail/'.$recent_buying[0]['slug'])}}"
                                       tabindex="-1">Learn More</a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="other-posts-wrap">
                        @foreach($recent_buying as $recentBuying)
                            @if(!$loop->first)
                                <div class="single-post-wrap">
                                    <div class="img-wrap">
                                        <img src="{{$recentBuying['full_feature_image']}}">
                                    </div>
                                    <div class="post-detail">
                                        <span>{{parseDate($recentBuying['blog_date'])}}</span>
                                        <h5>
                                            <a href="{{url('/blog-detail/'.$recentBuying['slug'])}}">{{$recentBuying['title']}}</a>
                                        </h5>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
