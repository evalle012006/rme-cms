                            @foreach($propertyDetailExport as $property)
                                <div class="property-wise-reviews-single">
                                    <div class="property-img-wrap">
                                        <img
                                            src="{{$property->full_property_logo}}">
                                    </div>
                                    <div class="property-detail">
                                        <div class="property-reviews">
                                            <div class="author-btm">
                                                <p>
                                                    <a href="{{url('property-review/'.$property->slug)}}">{{$property->project_name}}</a></p>
                                            </div>
                                            {{--                                            <div class="desc"><p>There are many variations of passages of Lorem Ipsum--}}
                                            {{--                                                    available, but the</p></div>--}}
                                            <div class="rating-wrap">
                                                <ul class="general-star-rating overall-rate-color-{{ratingClass($property->overall_rating)}}">
                                                    @foreach(range(0,4) as $number)
                                                        <li><a href=""><img
                                                                    src="{{asset('images/rating-img.png')}}"></a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>


                                        </div>
                                        <div class="total-reviews">
                                            <div class="title">Total Reviews</div>
                                            <div class="review-count">{{count($property->reviews)}}</div>
                                        </div>
                                        <div class="btn-wrap">
                                            <a class="btn btn-bordered"
                                               href="{{route('export-reviews',$property->id)}}">Export</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach