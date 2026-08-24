@extends('layouts.app-layout')

@php
    $rmeProjectName = $review['property']['project_name'] ?? null;
    $rmeAuthorName = $review['users']['display_name'] ?? ($review['users']['full_name'] ?? 'Anonymous');
@endphp

@section('meta_title', ($review['title'] ?? 'Review') . ($rmeProjectName ? ' - ' . $rmeProjectName : '') . ' | ' . seoSiteName())
@section('meta_description', seoText($review['comment'] ?? ('Read a verified review' . ($rmeProjectName ? ' of ' . $rmeProjectName : '') . ' on ' . seoSiteName() . '.')))
@section('og_type', 'article')

@section('structured_data')
    {!! seoJsonLd(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Review',
        'name' => $review['title'] ?? null,
        'reviewBody' => !empty($review['comment']) ? seoText($review['comment'], 500) : null,
        'author' => ['@type' => 'Person', 'name' => $rmeAuthorName],
        'itemReviewed' => $rmeProjectName ? ['@type' => 'Product', 'name' => $rmeProjectName] : null,
        'reviewRating' => (!empty($user['overall_rating']) && $user['overall_rating'] > 0) ? [
            '@type' => 'Rating',
            'ratingValue' => (string)$user['overall_rating'],
            'bestRating' => '5',
            'worstRating' => '1',
        ] : null,
    ])) !!}
@endsection

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <div class="blog-inner-title">
                {{--                <h2>{{$review['title']}}</h2>--}}
            </div>
        </div>
    </div>

    <div class="post-detail-section">
        <div class="container">
            <div class="post-detail-wrap">
                <div class="post-img-wrap">
                    <img src="{{asset('images/property-review-img-1.png')}}">
                </div>
                <div class="post-content-wrap">
                    <div class="post-title">
                        <div class="author-detail">
                            @if($review['users'] && $review['users']['full_profile_image'])
                                <img src="{{$review['users']['full_profile_image']}}">
                            @else
                                <img src="{{asset('images/author-img-grey.png')}}">
                            @endif
                        </div>
                        <div class="author-content">
                            <p>Written by {{$review['users']['full_name']}}</p>
                        </div>
                    </div>
                    <div class="post-body">
                        Reviewed to <b>{{$review['property']['project_name']}}</b>
                    </div>
                </div>
                <div class="review-detail-watch-new">
                    <div class="show-btn"></div>
                    <div class="content-panel-new" style="">
                        <div class="rated-exp">
                            <h6>Rated Experience</h6>
                            <ul>
                                @foreach($user['ratings'] as $ratings)

                                    <li>
                                        <span><img
                                                src="{{$ratings['review_question']['full_ratingconf_icon'] ? $ratings['review_question']['full_ratingconf_icon'] : asset('images/loc-icon.png')}}"></span>
                                        <span>{{$ratings['review_question']['name']}}</span>
                                        <ul class="overall-rate-color-regular question_color_{{$ratings['rate']}}">
                                            <li><a href=""><img
                                                        src="{{asset('images/rating-img.png')}}"></a>
                                            </li>
                                            <li><a href=""><img
                                                        src="{{asset('images/rating-img.png')}}"></a>
                                            </li>
                                            <li><a href=""><img
                                                        src="{{asset('images/rating-img.png')}}"></a>
                                            </li>
                                            <li><a href=""><img
                                                        src="{{asset('images/rating-img.png')}}"></a>
                                            </li>
                                            <li><a href=""><img
                                                        src="{{asset('images/rating-img.png')}}"></a>
                                            </li>
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="overall-rating-wrap">
                                <h6>Overall Ratings</h6>
                                <ul class="overall-rate-color-regular overall-rate-color-{{ratingClass($user['overall_rating'])}}">
                                    <li><a href=""><img src="{{asset('images/rating-img.png')}}"></a>
                                    </li>
                                    <li><a href=""><img src="{{asset('images/rating-img.png')}}"></a>
                                    </li>
                                    <li><a href=""><img src="{{asset('images/rating-img.png')}}"></a>
                                    </li>
                                    <li><a href=""><img src="{{asset('images/rating-img.png')}}"></a>
                                    </li>
                                    <li><a href=""><img src="{{asset('images/rating-img.png')}}"></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="comment-tags-wrap">
                            <h6>Comment Tags</h6>
                            <div class="tags-wrap">
                                <ul>
                                    @foreach($user['review_tags'] as $reviewTags)
                                        <li><a>{{$reviewTags['name']}}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @if(!empty($user['comment']))
                            <div class="comment-wrap">
                                <h6>Comment</h6>
                                <p>{{$user['comment']}}</p>
                            </div>
                        @endif
                    </div>
                    <div class="review-footer">
                        <div class="useful-wrap">
                            <a href="#"><img src="http://127.0.0.1:8000/images/useful-icon.png">Useful</a>
                        </div>
                        <div class="share-wrap">
                            <a href="#"><img src="http://127.0.0.1:8000/images/share-icon-grey.png">Share</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
