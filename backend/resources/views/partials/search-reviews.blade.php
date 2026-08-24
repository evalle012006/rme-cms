<div class="content-wrap-row search-reviews">
    @foreach($reviews as $review)
        <div class="section-content m-t-15">
            <div class="review-box-col">
                <div class="img-wrap">
                    <img
                        src="{{$review->full_review_image ? $review->full_review_image :asset('images/property-review-img-1.png')}}"
                        alt="review image">
                </div>
                <div class="author-detail">
                    <div class="author-top">
                        <div class="rating-wrap">
                            <div class="rating-left">
                                <div class="rating-wrap">
                                    <ul class="general-star-rating overall-rate-color-{{ratingClass($review->avg_rating)}}">
                                        @foreach(range(0,4) as $number)
                                            <li>
                                                <a href="javascript:void(0)">
                                                    <img src="{{asset('images/rating-img.png')}}"
                                                         alt="">
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                            <h6>{{$review->users->display_name}}</h6>
                        </div>
                    </div>
                    <div class="author-btm">
                        <p>
                            <strong>{{$review->users->display_name}}</strong>
                            reviewed <a
                                href="{{url('property-review/'.$review->property->slug)}}">{{$review->property->project_name}}</a>
                        </p>
                    </div>

                    <div class="review-text">
                        <p>{{$review->comment}}</p>
                    </div>
                    <div class="tags-wrap">
                        <ul>
                            @foreach($review->reviewTags as $reviewTag)
                                <li><a>{{$reviewTag->name}}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="btn-wrap">
                        @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                            <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                        @endif
                        <a class="btn btn-bordered"
                           href="{{url('property-review/'.$review->property->slug)}}">More Info</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@if(count($reviews)===0)
    <div>No Review found</div>
@endif


@if(count($reviews) < $totalReviews)
    <button class="btn m-t-15" id="review-show-more">
        Show More
    </button>
@endif
