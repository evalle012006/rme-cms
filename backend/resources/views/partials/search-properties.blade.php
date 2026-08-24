<div class="content-wrap-row search-reviews">
    @foreach($properties as $property)
        <div class="single-box m-t-15">
            <div class="single-box-in">
                <img
                    src="{{$property->is_company && $property->company ? $property->full_company_logo : ($property->full_property_logo ? $property->full_property_logo :asset('images/property-review-img-1.png'))}}"
                    alt="">
                <div class="property-img-wrap">
                    <h4>
                        @if($property->project_name)
                            <a href={{url('property-review/'.$property->slug)}}>
                                {{$property->project_name}}
                            </a>
                        @endif

                        @if($property->company && $property->company->id !== 3 && !$property->project_name)
                            @if($property->project_name)
                                by
                            @endif

                            <a href="{{url('property/'.$property->company->slug)}}">{{$property->company->company_name}}</a>
                        @endif
                    </h4>
                    <p>Total reviews {{$property->reviews_count}}</p>
                    <div class="btn-wrap">
                        <a class="btn" href={{url('review-step-1?')}}>
                            Write Review
                        </a>
                        <a class="btn btn-bordered"
                           href="{{$property->company && $property->is_company ? url('property/'.$property->company->slug) : url('property-review/'.$property->slug)}}">
                            More Info</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@if(count($properties)===0)
    <div>No Projects found</div>
@endif

@if(count($properties) < $totalProperties)
    <button class="btn m-t-15" id="property-show-more">
        Show More
    </button>
@endif
