@extends('layouts.app-layout')

@section('meta_title', ($blog_detail->seo_title ?: $blog_detail->title) . ' | ' . seoSiteName())
@section('meta_description', seoText($blog_detail->seo_description ?: $blog_detail->description))
@if($blog_detail->seo_keywords)
    @section('meta_keywords', $blog_detail->seo_keywords)
@endif
@section('og_type', 'article')
@section('og_image', $blog_detail->full_feature_image ?: asset('images/logo.png'))

@section('structured_data')
    {!! seoJsonLd(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $blog_detail->title,
        'description' => seoText($blog_detail->seo_description ?: $blog_detail->description, 300),
        'image' => $blog_detail->full_feature_image ?: asset('images/logo.png'),
        'datePublished' => optional($blog_detail->blog_date)->toDateString(),
        'dateModified' => optional($blog_detail->updated_at)->toDateString(),
        'author' => [
            '@type' => 'Person',
            'name' => $blog_detail->author_name ?: seoSiteName(),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => seoSiteName(),
            'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo.png')],
        ],
        'mainEntityOfPage' => url()->current(),
    ])) !!}
    {!! seoBreadcrumbJsonLd([
        ['name' => 'Home', 'url' => route('root')],
        ['name' => 'Property Blog', 'url' => route('property-blog')],
        ['name' => $blog_detail->title, 'url' => url()->current()],
    ]) !!}
@endsection

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <div class="blog-inner-title">
                <h1>{{$blog_detail->title}}</h1>
            </div>
        </div>
    </div>

    <div class="post-detail-section">
        <div class="container">
            <div class="post-detail-wrap">
                <div class="post-cat green">
                    {{$blog_detail->blogCategories[0]->name}}
                </div>
                <div class="post-img-wrap">
                    <img src="{{$blog_detail->full_feature_image}}" alt="{{$blog_detail->title}}" loading="lazy">
                </div>
                <div class="post-content-wrap">
                    <div class="post-title">
                        <div class="author-detail">
                            @if($blog_detail->user->full_profile_image)
                                <img src="{{$blog_detail->user->full_profile_image}}" alt="author">
                            @else
                                <img src="{{asset('images/author-img-grey.png')}}" alt="author">
                            @endif
                        </div>
                        <div class="author-content">
                            {{--                            <p>Written by {{$blog_detail->user->firstname.' '. $blog_detail->user->lastname}}</p>--}}
                            <p>Written by {{$blog_detail->author_name ?: 'anonymous'}}</p>
                            <span>Posted on {{parseDate($blog_detail->blog_date)}}</span>
                        </div>
{{--                        <a href="#" class="btn">Subscribe</a>--}}
                    </div>
                    <div class="post-body">
                        {!! $blog_detail->description !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="our-blog-section left-space">
        <div class="section-title">
            <h6>Read our Blog</h6>
            <h2>Recent Property Blog</h2>
            <p>It is a long established fact that a reader will be distracted by the readable content of a page of using
                Lorem Ipsum is that it has a more-or-less normal distribution</p>
        </div>
        <div class="section-content">
            <div class="btn-wrap">
                <a href="{{ route('blog-posts') }}">Show all Stories</a>
            </div>
            <div class="blog-content-wrap">
                <div class="blog-post-slider">
                    @if($recent_blogs)
                        @foreach($recent_blogs as $recentBlogs)
                            <div class="single-blog-col">
                                <div class="img-wrap">
                                    <img src="{{$recentBlogs['full_feature_image']}}">
                                </div>
                                <div class="content-wrap">
                                    <h4>
                                        <a href="{{url('/blog-detail/'.$recentBlogs['slug'])}}">{{trimTitle($recentBlogs['title'])}}</a>
                                    </h4>
                                    <div class="meta-wrap">
                                        <ul>
                                            <li class="calender"><img src="{{url('images/calender-img.png')}}">20 Apr,
                                                2019
                                            </li>
                                            <li class="comment"><img src="{{url('images/comment-icon.png')}}">10
                                                Comments
                                            </li>
                                        </ul>
                                    </div>
                                    <p>{!! trimDescription($recentBlogs['description']) !!}</p>
                                    <a class="learn-more" href="{{url('/blog-detail/'.$recentBlogs['slug'])}}">
                                        Learn More
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
