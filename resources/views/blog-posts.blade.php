@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <h2>Popular Blog Post</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Popular Blog Post</span>
            </div>
        </div>
    </div>

    <div class="popular-blogs-section">
        <div class="container">
            <div class="blogs-wrap">
                @foreach($blog_posts as $blogPost)
                    <div class="single-post-wrap build-cat">
                        <div class="post-cat">{{$blogPost->blogCategories[0]->name}}</div>
                        <div class="img-wrap">
                            <img src="{{$blogPost->full_feature_image}}">
                        </div>
                        <div class="blog-detail">
                            <h4><a href="{{url('/blog-detail/'.$blogPost->slug)}}" tabindex="0">{{$blogPost->title}}</a>
                            </h4>
                            <p>{!! trimDescription($blogPost->description) !!}</p>
                            <a class="learn-more" href="{{url('/blog-detail/'.$blogPost->slug)}}" tabindex="0">Learn
                                More</a>
                        </div>
                    </div>
                @endforeach
            </div>
            {{$blog_posts->links()}}
        </div>
    </div>
@endsection
