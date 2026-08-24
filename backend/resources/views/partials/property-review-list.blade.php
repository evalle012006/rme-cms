                @foreach($property_review as $propReview)
                    <div class="section-content">
                        <div class="top-single-review">
                            <div class="review-author">
                                <img class="profile-photo"
                                     src="{{$propReview['users']['profile_image'] ? absoluteStorageUrl($propReview['users']['profile_image']) : asset('images/author-img-grey.png')}}">
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
