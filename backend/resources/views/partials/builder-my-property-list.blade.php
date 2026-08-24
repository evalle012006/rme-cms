                            @foreach($properties as $propertiesData)
                                <div class="single-box">
                                    <div class="single-box-height">
                                        <div class="property-img-wrap property-logo" data-id="{{$propertiesData->id}}" data-img="{{$propertiesData->full_property_logo}}">
                                            <img
                                                src="{{$propertiesData->full_property_logo}}">
                                        </div>
                                        <div class="author-detail">
                                            <div class="author-btm">
                                                <p class="property-title">
                                                    <a href="{{url('property-review/'.$propertiesData->slug)}}">{{$propertiesData->project_name}}</a>
                                                </p>

                                                <div class="main-rating-new">
                                                    <div class="rating-info">
                                                        <div class="rating-left">
                                                            <div
                                                                class="avarage-rating">{{$propertiesData->overall_rating_exp}}</div>
                                                            <div class="rating-wrap">
                                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($propertiesData->overall_rating)}}">
                                                                    @foreach(range(0,4) as $number)
                                                                        <li><a href=""><img
                                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <p>Total Reviews {{$propertiesData->review_count}}</p>
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
                                                        @if(count($propertiesData->ratings_by_title) > 3)
                                                            <div class="more-details-section">
                                                                <div class="show-more"><h6>Show More</h6></div>
                                                                <div class="content-panel">
                                                                    @foreach(array_slice($propertiesData->ratings_by_title,3,count($propertiesData->ratings_by_title)) as $queName=>$rating)
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
                                                                    <h6 class="show-less">Show Less</h6>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tags-wrap">
                                            <div class="tag-title">Review Tags :</div>
                                            <ul>
                                                @isset($propertiesData->review_tags)
                                                    @foreach($propertiesData->review_tags as $tag)
                                                        <li><a href="">{{$tag}}</a></li>
                                                    @endforeach
                                                @endisset
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="btn-wrap">
                                        <a class="btn" href="{{url('property-review/'.$propertiesData->slug)}}">More
                                            Info</a>
                                    </div>
                                </div>
                            @endforeach
                        {{$properties->appends(request()->input())->links()}}
