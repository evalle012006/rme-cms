                        @foreach($user_review as $userReview)
                            <div class="single-review">
                                <div class="img-wrap">
                                    {{--                                    <a href="{{url('review/'.$userReview['slug'])}}"><img--}}
                                    {{--                                            src="{{$userReview['full_review_image'] ? $userReview['full_review_image']: 'images/property-review-img-1.png'}}"></a>--}}
                                    <a href="{{(int)$userReview->status === 1 ? url('review/'.$userReview['slug']) : "#"}}">
                                        <img
                                            src="{{isset($userReview->property) && isset($userReview->property->full_property_logo) ? $userReview->property->full_property_logo: 'images/property-review-img-1.png'}}"></a>
                                </div>
                                <div class="review-detail-content">
                                    <div class="author-top">
                                        <div class="rating-wrap fix-width-120">
                                            <ul class="general-star-rating overall-rate-color-{{ratingClass($userReview->avg_rating)}}">
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            </ul>
                                        </div>
                                        @if((int)$userReview->status === 0)
                                            <div class="setting-icon">
                                                <div class="btn-wrap">
                                                    <a class="btn" href="javascript:void(0)" id="modalBtn"
                                                       data-review="{{$userReview}}">
                                                        Edit
                                                    </a>
                                                    <a class="btn pending-btn" href="#">
                                                        Pending
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif((int)$userReview->status === 2)
                                            {{-- Hidden by an admin; not public and not counted in ratings. --}}
                                            <div class="setting-icon">
                                                <div class="btn-wrap">
                                                    <a class="btn pending-btn" href="#">
                                                        Inactive
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="views-count">
                                            <ul>
                                                {{--                                                <li class="views-number">{{views($userReview)->count()}} views</li>--}}
                                                {{--                                                <li class="useful-number"> 90 useful</li>--}}
                                                {{--                                                <li class="share-number"> 18 share</li>--}}
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="author-btm">
                                        <p>{{$userReview->title}}</p>
                                        <a href="{{url('property-review/'.$userReview['property']['slug'])}}">{{$userReview['property']['project_name']}}</a>
                                        @if($userReview['property']['company'])
                                            by <a
                                                href="{{url('property/'.$userReview['property']['company']['slug'])}}">{{$userReview['property']['company']['company_name']}}</a>
                                            </p>
                                        @endif

                                    </div>
                                    <div class="tags-wrap">
                                        <ul>
                                            @foreach($userReview->reviewTags as $reviewTag)
                                                <li><a href="#">{{$reviewTag->name}}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{$user_review->links()}}
                        @if(!(count($user_review) > 0))
                            <div class="fa-2x"> No Reviews Found..</div>
                        @endif
