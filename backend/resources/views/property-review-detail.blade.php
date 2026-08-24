@extends('layouts.app-layout')

@php
    $rmeCompanyName = $property['company']['company_name'] ?? null;
    $rmeReviewCount = is_array($property_review) ? count($property_review) : 0;
    $rmeSchemaReviews = [];
    if ($rmeReviewCount > 0) {
        foreach (array_slice(array_values($property_review), 0, 10) as $rmeReview) {
            $rmeSchemaReviews[] = array_filter([
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $rmeReview['users']['display_name']
                        ?? ($rmeReview['users']['firstname'] ?? 'Anonymous'),
                ],
                'datePublished' => !empty($rmeReview['created_at'])
                    ? \Carbon\Carbon::parse($rmeReview['created_at'])->toDateString() : null,
                'name' => $rmeReview['title'] ?? null,
                'reviewBody' => !empty($rmeReview['comment']) ? seoText($rmeReview['comment'], 300) : null,
                'reviewRating' => !empty($rmeReview['overall_rating']) ? [
                    '@type' => 'Rating',
                    'ratingValue' => (string)$rmeReview['overall_rating'],
                    'bestRating' => '5',
                    'worstRating' => '1',
                ] : null,
            ]);
        }
    }
@endphp

@section('meta_title', $property['project_name'] . ($rmeCompanyName ? ' by ' . $rmeCompanyName : '') . ' Reviews | ' . seoSiteName())
@section('meta_description', seoText(!empty($property['description']) ? $property['description'] : ('Read ' . $rmeReviewCount . ' verified reviews and ratings for ' . $property['project_name'] . ($rmeCompanyName ? ' by ' . $rmeCompanyName : '') . ' on ' . seoSiteName() . '.')))
@section('og_image', $property['full_property_logo'] ?: asset('images/logo.png'))

@section('structured_data')
    {!! seoJsonLd(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $property['project_name'],
        'image' => $property['full_property_logo'] ?: asset('images/logo.png'),
        'description' => !empty($property['description']) ? seoText($property['description'], 300) : null,
        'brand' => $rmeCompanyName ? ['@type' => 'Brand', 'name' => $rmeCompanyName] : null,
        'aggregateRating' => ($rmeReviewCount > 0 && $overall_rate > 0) ? [
            '@type' => 'AggregateRating',
            'ratingValue' => (string)$overall_rate,
            'reviewCount' => (string)$rmeReviewCount,
            'bestRating' => '5',
            'worstRating' => '1',
        ] : null,
        'review' => !empty($rmeSchemaReviews) ? $rmeSchemaReviews : null,
    ])) !!}
    {!! seoBreadcrumbJsonLd([
        ['name' => 'Home', 'url' => route('root')],
        ['name' => 'Reviews', 'url' => route('property-listing')],
        ['name' => $property['project_name'], 'url' => url()->current()],
    ]) !!}
@endsection

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
                                            <a class="filter-rating-link {{request()->get('filter_rating')==$ratingNumber ? 'active-rating': '' }}"
                                               href="{{ $starRating[1] == 0 ? 'javascript:void(0)' : (request()->get('filter_rating')==$ratingNumber ? request()->fullUrlWithQuery(['filter_rating' => null]) : request()->fullUrlWithQuery(['filter_rating' => $ratingNumber])) }}">
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
            <div class="top-10-reviews-wrap" id="property-reviews-section">
                <div class="section-title">
                    <h5 id="property-reviews-count">Reviews {{count($property_review)}}</h5>
                    <div class="filter-wrap">
                        <span>Filter By:</span>
                        <form id="property-review-filter-form"
                              action="{{ isset($cat) ? url('property-review/'.request()->segment(2).'/'.request()->segment(3)) : url('property-review/'.request()->segment(2)) }}"
                              method="GET">
                            @if(request()->filled('filter_rating'))
                                <input type="hidden" name="filter_rating" value="{{ request()->get('filter_rating') }}">
                            @endif
                            <input type="text" name="filter_by" value="{{request()->input('filter_by')}}"
                                   placeholder="Search by review">
                            <input type="submit" name="" value="Search">
                        </form>
                    </div>
                </div>
                <div id="property-reviews-results">
                    @include('partials.property-review-list')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        (function ($) {
            function loadPropertyReviews(url) {
                var $results = $('#property-reviews-results');
                $results.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (html) {
                        $results.html(html);
                        var count = $results.find('.section-content').length;
                        if ($results.find('h4').length && count === 0) {
                            $('#property-reviews-count').text('Reviews 0');
                        } else {
                            $('#property-reviews-count').text('Reviews ' + count);
                        }
                        if (window.history && window.history.pushState) {
                            window.history.pushState({propertyReviewAjax: true}, '', url);
                        }
                        var params = new URL(url, window.location.origin).searchParams;
                        var rating = params.get('filter_rating');
                        $('.filter-rating-link').removeClass('active-rating');
                        if (rating) {
                            $('.filter-rating-link').each(function () {
                                if ($(this).attr('href').indexOf('filter_rating=' + rating) !== -1) {
                                    $(this).addClass('active-rating');
                                }
                            });
                            $('#property-review-filter-form input[name="filter_rating"]').remove();
                            $('#property-review-filter-form').prepend(
                                '<input type="hidden" name="filter_rating" value="' + rating + '">'
                            );
                        } else {
                            $('#property-review-filter-form input[name="filter_rating"]').remove();
                        }
                        $('.content-panel').hide();
                    },
                    complete: function () {
                        $results.css('opacity', '1');
                    }
                });
            }

            $('#property-review-filter-form').on('submit', function (event) {
                event.preventDefault();
                var $form = $(this);
                var action = $form.attr('action');
                var query = $form.serialize();
                var url = action + (action.indexOf('?') === -1 ? '?' : '&') + query;
                loadPropertyReviews(url);
            });

            $(document).on('click', '.filter-rating-link', function (event) {
                event.preventDefault();
                var href = $(this).attr('href');
                if (href) {
                    loadPropertyReviews(href);
                }
            });

            window.addEventListener('popstate', function (event) {
                if (event.state && event.state.propertyReviewAjax) {
                    loadPropertyReviews(window.location.href);
                } else {
                    window.location.reload();
                }
            });
        })(jQuery);
    </script>
@endsection
