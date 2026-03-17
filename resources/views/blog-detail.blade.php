@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <div class="blog-inner-title">
                <h2>{{$blog_detail->title}}</h2>
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
                    <img src="{{$blog_detail->full_feature_image}}">
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
                            <p>Written by {{$blog_detail->author_name}}</p>
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
            <h2>Recent Property Coach</h2>
            <p>It is a long established fact that a reader will be distracted by the readable content of a page of using
                Lorem Ipsum is that it has a more-or-less normal distribution</p>
        </div>
        <div class="section-content">
            <div class="btn-wrap">
                <a href="#">Show all Stories</a>
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
