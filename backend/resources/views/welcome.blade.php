@extends('layouts.app-layout')

@section('content')
    <div class="banner-section bg-main"
         style="background-image: url({{$sectionData['banner']['image'] ? checkUrl($sectionData['banner']['image']) : 'images/homepage/HomepageHero.jpg'}});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h2>{{$sectionData['banner']['title']}}</h2>
            @else
                <h2>Seek Reviews. Buy with <span>Confidence.</span></h2>
            @endif
            <div class="banner-srch-wrap">
                <form action="{{route('property_search')}}">

                    <div class="form-row">
                        <input type="text" name="search_key" id="search_key" placeholder="Search brand or projects">
                        <input type="hidden" name="search_company" id="search_company">
                        <input type="submit" name="" value="Search">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="review-section">
        <div class="review-stat-section">
            <div class="container">
                <div class="section-title">
                    @if($sectionData['review_ratio']['title'])
                        <h2>{{$sectionData['review_ratio']['title']}}</h2>
                    @else
                        <h2>Buying a home doesn’t
                            have to be a mystery.</h2>
                    @endif
                </div>
                <div class="section-content">
                    <div class="single-stat-col">
                        @if($sectionData['review_ratio']['other_params'] && isset($sectionData['review_ratio']['other_params']['trust_online_ratio']) && $sectionData['review_ratio']['other_params']['trust_online_ratio'])
                            <h2>{{$sectionData['review_ratio']['other_params']['trust_online_ratio']}}<span>%</span>
                            </h2>
                        @else
                            <h2>88<span>%</span>
                            </h2>
                        @endif
                        <h5>Trust online reviews</h5>
                    </div>
                    <div class="single-stat-col">
                        @if($sectionData['review_ratio']['other_params'] && isset($sectionData['review_ratio']['other_params']['validate_reviews_ratio']) && $sectionData['review_ratio']['other_params']['validate_reviews_ratio'])
                            <h2>{{$sectionData['review_ratio']['other_params']['validate_reviews_ratio']}}<span>%</span>
                            </h2>
                        @else
                            <h2>89<span>%</span></h2>
                        @endif

                        <h6>Validate advise</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="write-review-section" style="background-image: url(images/write-review-bg.png);">
            <div class="container">
                <div class="img-wrap">
                    <img
                        src="{{$sectionData['why_write_review']['image'] ? checkUrl($sectionData['why_write_review']['image']) :asset('images/homepage/Homepage_module2.jpg')}}">
                </div>
                <div class="content-wrap">
                    @if($sectionData['why_write_review']['title'])
                        <h5>{{$sectionData['why_write_review']['title']}} </h5>
                    @else
                        <h5>WHY WRITE A REVIEW. Be part of a community that is doing good. </h5>
                    @endif

                    @if($sectionData['why_write_review']['body'])
                        {!! $sectionData['why_write_review']['body'] !!}
                    @else
                        <ul>
                            <li>Voice your opinion and help others make informed decisions
                            </li>
                            <li>Reward builders and developers who performed to your expectations and beyond.
                            </li>
                            <li>Influence builders and developers improve their product, service and customer
                                experience.
                            </li>
                        </ul>
                    @endif
                    @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                        <div class="btn-wrap">
                            <a href="{{route('review-step-1')}}" class="btn yellow-btn">Write A Review </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="recent-reviews-section recent-reviews-custom">
        <div class="container">
            <div class="content-box">
                <div class="section-title">
                    <div class="title-wrap">
                        <h2>Recent Reviews</h2>
                        @if($sectionData['recent_reviews']['title'])
                            <h5>{{$sectionData['recent_reviews']['title']}}</h5>
                        @else
                            <h5>Buy with Confidence</h5>
                        @endif

                        @if($sectionData['recent_reviews']['body'])
                            {!! $sectionData['recent_reviews']['body'] !!}
                        @else
                            <p>Your home is one of the most expensive assets you will own. Do your research, know who
                                and 
                                what you’re dealing with.</p>
                        @endif

                    </div>
                    <div class="btn-wrap">
                        <a href="{{route('property-listing')}}">Show all Reviews</a>
                    </div>
                </div>
                <div class="section-content-slider desktop-view">
                    @foreach($recent_reviews as $reviews)
                        <div class="section-content section-content-custom">
                            <div class="review-box-col">
                                <div class="review-box-col-in">
                                    <div class="property-img-wrap" style="display: none;">
                                        <a href="{{url('review/'.$reviews['slug'])}}">
                                            <img
                                                src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                        </a>
                                        {{--                            <div class="img-tag">Homesite</div>--}}
                                    </div>
                                    <div class="author-detail-wrapper">
                                        <div class="author-detail">
                                            <div class="author-top">
                                                <div class="img-wrap">
                                                    {{--                                            <img--}}
                                                    {{--                                                src="{{$reviews['users']['full_profile_image'] ?$reviews['users']['full_profile_image'] :  asset('images/author-1.png')}}">--}}
                                                    <img
                                                        src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                                </div>
                                                <div class="rating-wrap">
                                                    <div class="rating-left">
                                                        <div class="rating-wrap">
                                                            <ul class="general-star-rating overall-rate-color-{{ratingClass($reviews['avg_rating'])}}">
                                                                @foreach(range(0,4) as $number)
                                                                    <li><a href=""><img
                                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                    </div>
                                                    <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>

                                                </div>
                                            </div>
                                            <div class="author-btm">
                                                <p><strong>{{$reviews['users']['display_name']}}</strong> reviewed <a
                                                        href="{{url('property-review/'.$reviews['property']['slug'])}}">{{$reviews['property']['project_name']}}</a>
                                                </p>
                                            </div>

                                            <div class="review-text">
                                                <p>
                                                    {{strlen($reviews['comment']) > 30 ? substr($reviews['comment'], 0, 30).'...': $reviews['comment']}}
                                                </p>
                                            </div>
                                        </div>
                                        @if(count($reviews['review_tags'])>0)
                                            <div class="tags-wrap">
                                                <ul>
                                                    @foreach($reviews['review_tags'] as $reviewTags)
                                                        <li><a>{{$reviewTags['name']}}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="review-box-col-new">
                                    <div class="btn-wrap">
                                        @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                                            <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                                        @endif
                                        <a class="btn btn-bordered"
                                           href="{{url('property-review/'.$reviews['property']['slug'])}}">More Info</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="section-content-slider mobile-view">
                    @foreach($recent_reviews as $reviews)
                        <div class="section-content section-content-custom">
                            <div class="review-box-col">
                                <div class="property-img-wrap" style="display: none;">
                                    <a href="{{url('review/'.$reviews['slug'])}}">
                                        <img
                                            src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                    </a>
                                    {{--                            <div class="img-tag">Homesite</div>--}}
                                </div>
                                <div class="author-detail-wrapper">
                                    <div class="author-detail">
                                        <div class="author-top">
                                            <div class="img-wrap">
                                                {{--                                            <img--}}
                                                {{--                                                src="{{$reviews['users']['full_profile_image'] ?$reviews['users']['full_profile_image'] :  asset('images/author-1.png')}}">--}}
                                                <img
                                                    src="{{$reviews['property'] && $reviews['property']['full_property_logo'] ?$reviews['property']['full_property_logo'] :asset('images/property-review-img-1.png')}}">
                                            </div>
                                            <div class="rating-wrap">
                                                <div class="rating-left">
                                                    <div class="rating-wrap">
                                                        <ul class="general-star-rating overall-rate-color-{{ratingClass($reviews['avg_rating'])}}">
                                                            @foreach(range(0,4) as $number)
                                                                <li><a href=""><img
                                                                            src="{{asset('images/rating-img.png')}}"></a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>

                                                </div>
                                                <h6>{{$reviews['users']['firstname'] .' '. $reviews['users']['lastname']}}</h6>

                                            </div>
                                        </div>
                                        <div class="author-btm">
                                            <p><strong>{{$reviews['users']['display_name']}}</strong> reviewed <a
                                                    href="{{url('property-review/'.$reviews['property']['slug'])}}">{{$reviews['property']['project_name']}}</a>
                                            </p>
                                        </div>

                                        <div class="review-text">
                                            <p>
                                                {{strlen($reviews['comment']) > 30 ? substr($reviews['comment'], 0, 30).'...': $reviews['comment']}}
                                            </p>
                                        </div>
                                    </div>
                                    @if(count($reviews['review_tags'])>0)
                                        <div class="tags-wrap">
                                            <ul>
                                                @foreach($reviews['review_tags'] as $reviewTags)
                                                    <li><a>{{$reviewTags['name']}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="review-box-col-new">
                                <div class="btn-wrap">
                                    @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                                        <a class="btn" href="{{url('review-step-1')}}">Write Review</a>
                                    @endif
                                    <a class="btn btn-bordered"
                                       href="{{url('property-review/'.$reviews['property']['slug'])}}">More Info</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(!count($recent_reviews) > 0)
                    <h4>No Recent Reviews Found..</h4>
                @endif
                @if ($message = \Session::get('error'))
                    <div class="alert alert-danger alert-block">
                        {{--                                <button type="button" class="close" data-dismiss="alert">×</button>--}}
                        <strong class="text-danger">{{ $message }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <section id="property-guide">
        <div class="chkout-section">
            <div class="section-title">
                <div class="container txtC">
                    <h2>Seek Reviews by Location</h2>
                </div>
            </div>
            <div class="section-content" id="pinned-map"></div>
        </div>
    </section>
    @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
        <div class="cat-review-section">
            <div class="container">
                <div class="section-title">
{{--                    <h2>Write a Review</h2>--}}
                    <h2>Have you just finished your build? Write a review</h2>
                </div>
                <form method="GET" action="{{route('home-cat-review')}}" id="formId">
                    <div class="section-content">
                        @foreach($property_category as $category)

                            <div class="single-box">
                                <input type="radio" name="categories" class="ads_Checkbox" id="category"
                                       value="{{$category['id']}}">
                                <div class="cat-content-wrap">
                                    <div class="icon-wrap">
                                        <img
                                            src="{{$category['full_cat_image'] ? $category['full_cat_image'] : asset('images/cat-icon1.png')}}">
                                    </div>
                                    <h4>{{$category['name']}}</h4>
                                    <div class="content-wrap">

                                        <p>{{$category['description'] ? $category['description'] :  'There are many variations of passages of Lorem Ipsum available'}}</p>
                                        <a>
                                            <img src="{{asset('images/grey-arrow.png')}}">
                                            <img class="hover-img" src="{{asset('images/white-arrow.png')}}">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    @endif
    <div class="our-blog-section left-space">
        <div class="section-title">
            <h6>Read our Blog</h6>
            @if($sectionData['our_blogs']['title'])
                <h2>{{$sectionData['our_blogs']['title']}}</h2>
            @else
                <h2>Property Coach</h2>
            @endif

            @if($sectionData['our_blogs']['body'])
                {!!$sectionData['our_blogs']['body']!!}
            @else
                <p>Stay updated with the latest property news, content and insights</p>
            @endif
        </div>
        <div class="section-content">
            <div class="btn-wrap">
                <a href="{{ route('property-coach') }}">Show all Stories</a>
            </div>
            <div class="blog-content-wrap">
                <div class="blog-post-slider">
                    @if($recent_blogs)
                        @foreach($recent_blogs as $recentBlogs)
                            <div class="single-blog-col">
                                <div class="img-wrap">
                                    <a href="{{url('/blog-detail/'.$recentBlogs['slug'])}}"><img
                                            src="{{$recentBlogs['full_feature_image']}}"></a>
                                </div>
                                <div class="content-wrap">
                                    <h4>
                                        <a href="{{url('/blog-detail/'.$recentBlogs['slug'])}}">{{trimTitle($recentBlogs['title'])}}</a>
                                    </h4>
                                    <div class="meta-wrap">
                                        <ul>
                                            <li class="calender"><img
                                                    src="{{url('images/calender-img.png')}}">{{\Carbon\Carbon::parse($recentBlogs['created_at'])->format('d M, Y')}}
                                            </li>
                                        </ul>
                                    </div>
                                    <p>{!! trimDescription($recentBlogs['description']) !!}</p>
                                    <a class="learn-more" href="{{url('/blog-detail/'.$recentBlogs['slug'])}}">Learn
                                        More</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-script')
    <script>
        $(':radio').click(function () {
            $('input:not(:checked)').parent().removeClass("active");
            $('input:checked').parent().addClass("active");
            $("#formId").submit();
        });
    </script>

    <script>


        const groupedReviews = {!! json_encode($groupedReviews) !!};
        const suburbsLocations = {!! json_encode($suburbsLocations) !!};
        const mapMarkers = [];
        const propertyMarkers = [];

        document.onfullscreenchange = function (event) {
            let target = event.target;
            let pacContainerElements = document.getElementsByClassName("pac-container");
            if (pacContainerElements.length > 0) {
                let pacContainer = document.getElementsByClassName("pac-container")[0];
                if (pacContainer.parentElement === target) {
                    $('.pac-container').removeClass('top-zero');
                    document.getElementsByTagName("body")[0].appendChild(pacContainer);
                } else {
                    $('.pac-container').addClass('top-zero');
                    target.appendChild(pacContainer);
                }
            }
        };

        function createControl(controlDiv, map) {
// Set CSS for the control border.
            var controlUI = document.createElement('div');
            controlUI.style.backgroundColor = '#fff';
            controlUI.style.marginTop = '10px';
            controlUI.style.border = '2px solid #fff';
            controlUI.style.borderRadius = '3px';
            controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
            controlUI.style.cursor = 'pointer';
            controlUI.style.marginBottom = '22px';
            controlUI.style.textAlign = 'center';
            controlUI.title = 'Click to rolUecenter the map';
            controlDiv.appendChild(controlUI);

// Set CSS for the control interior.
            var controlText = document.createElement('input');
            controlText.setAttribute('id', 'map-search');
            controlText.setAttribute('placeholder', 'search here ...');
            controlText.style.color = 'rgb(25,25,25)';
            controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
            controlText.style.fontSize = '16px';
            controlText.style.lineHeight = '38px';
            controlText.style.paddingLeft = '5px';
            controlText.style.paddingRight = '5px';
            controlText.style.width = '250px';
            controlUI.appendChild(controlText);

// Setup the click event listeners: simply set the map to Chicago.
// controlUI.addEventListener('click', function () {
//     map.setCenter(chicago);
// });
// initialise autocomplete field
            const defaultBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(-33.8902, 151.1759),
                new google.maps.LatLng(-33.8474, 151.2631)
            );

// const input = document.getElementById('map-search');
            const options = {
                bounds: defaultBounds,
                types: ['geocode'],
                componentRestrictions: {country: 'au'}
            };
            autocomplete = new google.maps.places.Autocomplete(controlText, options);
            autocomplete.addListener('place_changed', function (e) {
                if (autocomplete.getPlace().geometry) {
                    const {location} = autocomplete.getPlace().geometry;
                    const lat = location.lat();
                    const lng = location.lng();
                    map.setCenter({lat, lng});
                    map.setZoom(12);
                }
            });
// end

        }

        function initMap() {
            let centerPoint = {lat: -22.5752, lng: 144.0848};
// ask to allow location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    centerPoint = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                }, function () {
                    centerPoint = {lat: -22.5752, lng: 144.0848}; // australia, South Yarra - 3141
                }, {timeout: 60000});
            }
// end

// Initialize google map
            var map = new google.maps.Map(
                document.getElementById('pinned-map'), {zoom: 7, center: centerPoint});
// end

// initialize search html
            var searchDiv = document.createElement('div');
            var centerControl = new createControl(searchDiv, map);
            searchDiv.index = 1;
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(searchDiv);
// end

            var ico = new google.maps.MarkerImage(
                'images/marker.png',
                new google.maps.Size(70, 70),
                new google.maps.Point(0, 0),
                new google.maps.Point(17, 34),
                new google.maps.Size(55, 50)
            );

            var infowindow = new google.maps.InfoWindow();

            Object.keys(suburbsLocations).forEach((suburbName) => {
                var marker = new google.maps.Marker({
                    title: suburbName,
                    position: {
                        lat: +suburbsLocations[suburbName][0]['latitude'],
                        lng: +suburbsLocations[suburbName][0]['longitude']
                    },
                    map: map,
                    icon: ico,
                    optimized: false
                });
                mapMarkers.push(marker);
                google.maps.event.addListener(marker, 'click', function () {
                    let previewPopup = '';
                    const propertyRatings = groupedReviews[suburbName]['property_ratings'];
                    const propertyKeysArray = Object.keys(propertyRatings);

// count overall ratings
                    let totalRating = 0;
                    propertyKeysArray.forEach(propertyName => {
                        totalRating += +propertyRatings[propertyName]['rating'];
                    });
                    // const averageOfProperties = Math.round(totalRating / propertyKeysArray.length);
                    const averageOfProperties = roundOffRating((totalRating / propertyKeysArray.length));

// sort property by rating
                    propertyKeysArray.sort(function (a, b) {
                        return propertyRatings[b]['rating'] - propertyRatings[a]['rating'];
                    });
                    previewPopup += '<div class="map-rating-wrapper">' +
                        '<div class="custom-rating-wrap">';
                    (propertyKeysArray.slice(0, 5)).forEach(propertyName => {
                        let property_route = '{{route('property-review-slug',':property_slug')}}';
                        property_route = property_route.replace(':property_slug', propertyRatings[propertyName]['data']['slug']);
                        previewPopup += '<div class="rating-wrap-main">' +
                            '<p><a href=' + property_route + '>' + propertyName + '</a></p>' +
                            '<div class="rating-wrap">' +
                            '<ul class="general-star-rating overall-rate-color-' + ratingClass(propertyRatings[propertyName]['rating']) + '">' +
                            [1, 2, 3, 4, 5].map(() => {
                                return "<li><a href=''><img src='images/rating-img.png'></a></li>"
                            }) +
                            '</ul></div></div>';
                    });

// closign custom-rating-div


                    if (propertyKeysArray.length > 5) {
                        let route = '{{route("property_search",["view_type"=>1,"filter_by"=>'suburb_name'])}}';
                        route = route.replace('suburb_name', encodeURI(suburbName));
                        previewPopup += '<div class="rating-wrap-main text-center">' +
                            '<a href=' + route + '>show all ( +' + (propertyKeysArray.length - 5) + ' )</a>' +
                            '</div>';
                    }

                    previewPopup += '<div class="rating-wrap-main">' +
                        '<p><strong>Overall Rating</strong></p>' +
                        '<div class="rating-wrap">' +
                        '<ul class="general-star-rating overall-rate-color-' + ratingClass(averageOfProperties) + '">' +
                        [1, 2, 3, 4, 5].map(() => {
                            return "<li><a href=''><img src='images/rating-img.png'></a></li>"
                        }) +
                        '</ul></div>' +
                        '</div>';
                    '</div>';
                    previewPopup += '</div>';

                    infowindow.setContent(previewPopup);
                    infowindow.open(map, marker);
                });
            })

            google.maps.event.addListener(map, 'idle', function (e) {
                const zoomLevel = map.getZoom();
                const bounds = map.getBounds();
                if (zoomLevel >= 13) {
                    setPropertyMarkers(map, bounds, ico, infowindow);
                } else {
                    if (zoomLevel < 13) {
                        removePropertyMarkers(bounds, map);
                    }
                }
            });
        }

        function removePropertyMarkers(bounds, map) {
            mapMarkers.map(mainMarker => {
                if (mainMarker.getMap() == null) {
                    mainMarker.setMap(map)
                }
            });

            propertyMarkers.map(propertyMarker => {
                if (bounds.contains(propertyMarker.getPosition())) {
                    propertyMarker.setMap(null);
                }
            });
        }

        function setPropertyMarkers(map, bounds, ico, infowindow) {
            let visibleSuburbs = {};
            mapMarkers.forEach(mainMarker => {
                if (bounds.contains(mainMarker.getPosition())) {
                    const propertiesObject = groupedReviews[mainMarker.getTitle()]['property_ratings'];
                    Object.keys(propertiesObject).forEach((propertyName) => {
                        const position = {
                            lat: +propertiesObject[propertyName]['data']['lat'],
                            lng: +propertiesObject[propertyName]['data']['long']
                        };
                        if (position['lat'] && position['lng']) {
                            if (!checkPropertyMarkerExists(position)) {
                                visibleSuburbs = {...propertiesObject, ...visibleSuburbs};
                            }
                        }
                    })
                    mainMarker.setMap(null);
                }
            });

            Object.keys(visibleSuburbs).forEach(propertyName => {
                const position = {
                    lat: +visibleSuburbs[propertyName]['data']['lat'],
                    lng: +visibleSuburbs[propertyName]['data']['long']
                };
                const marker = new google.maps.Marker({
                    title: propertyName,
                    position: position,
                    map: map,
                    icon: ico,
                    optimized: false
                });
                propertyMarkers.push(marker);

                google.maps.event.addListener(marker, 'click', function () {
                    let property_route = '{{route('property-review-slug',':property_slug')}}';
                    property_route = property_route.replace(':property_slug', visibleSuburbs[propertyName]['data']['slug']);
                    const previewPopup = '<div>' +
                        '<p><a href=' + property_route + '>' + propertyName + '</a></p>' +
                        '</div>';
                    infowindow.setContent(previewPopup);
                    infowindow.open(map, marker);
                });
            });
        }

        function checkPropertyMarkerExists(position) {
            return propertyMarkers.some(item => {
                return position['lat'] === item.getPosition().lat() &&
                    position['lng'] === item.getPosition().lng() &&
                    item.getMap() !== null;
            })
        }

        $("#search_key").on('keyup', function () {
            $("#search_company").val($(this).val());
        })

    </script>

    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap&libraries=geometry,places&ver=5.4">
    </script>
@endsection

