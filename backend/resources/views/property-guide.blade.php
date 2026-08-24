@extends('layouts.app-layout')

@section('meta_title', 'Property Guide | ' . seoSiteName())
@section('meta_description', seoText($sectionData['banner']['description'] ?? 'A step-by-step property guide to help Australian buyers research, compare and choose with confidence on ' . seoSiteName() . '.'))

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{ $sectionData['banner']['image'] ? absoluteStorageUrl($sectionData['banner']['image']) : asset('images/homepage/HomepageHero.jpg') }});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h1>{{$sectionData['banner']['title']}}</h1>
            @else
{{-- CMS fallback (kept for later restore)
<h1>Property Guide</h1>
            --}}
@endif
        </div>
    </div>
    <div class="product-view-wrapper">
        <div class="product-view">
            <div class="list-side">
                <div class="list-title">
                    Best Results for: <span class="filter_name">All</span>
                </div>
                <div class="product-list" id="product-list">
                    <p>No projects found</p>
                </div>
            </div>
            <div class="map-side">
                <div id="pinned-map"></div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script type='text/javascript'
            src='https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=geometry,places&ver=5.4.1'>
    </script>
    <script type='text/javascript'
            src='https://cdn.sobekrepository.org/includes/gmaps-markerwithlabel/1.9.1/gmaps-markerwithlabel-1.9.1.min.js?ver=5.4.1'></script>

    <script>
        const groupedReviews = {!! json_encode($groupedReviews) !!};
        const suburbsLocations = {!! json_encode($suburbsLocations) !!};
        // zoom level at (and above) which we switch from suburb pins to individual property pins
        const PROPERTY_ZOOM = 13;
        // suburb pins (one per suburb)
        const mapMarkers = [];
        // property pins, created once per suburb and reused: { suburbName: [ { marker, propertyName, data, rating, propertyRoute, companyRoute } ] }
        const propertyMarkersBySuburb = {};


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
                new google.maps.LatLng(-33.8474, 151.2631));

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
                    map.setZoom(PROPERTY_ZOOM);
                }
            });
// end

        }


        $(document).ready(function () {


            // The location of Uluru
            var uluru = {lat: -22.5752, lng: 144.0848}; // australia, South Yarra - 3141

            // The map, centered at Uluru
            var map = new google.maps.Map(
                document.getElementById('pinned-map'), {zoom: 7, center: uluru});

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

            var icoProperty = new google.maps.MarkerImage(
                'images/location-mark.png',
                new google.maps.Size(35, 50),
                new google.maps.Point(0, 0),
                new google.maps.Point(17, 34),
                new google.maps.Size(35, 50)
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

                    // closing custom-rating-div

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
                if (zoomLevel >= PROPERTY_ZOOM) {
                    showPropertyView(map, bounds, icoProperty, infowindow);
                } else {
                    showSuburbView();
                }
            });

            // Low zoom: show every suburb pin, hide all property pins, reset the list.
            function showSuburbView() {
                mapMarkers.forEach(mainMarker => {
                    if (mainMarker.getMap() === null) {
                        mainMarker.setMap(map);
                    }
                });
                Object.keys(propertyMarkersBySuburb).forEach(suburbName => {
                    propertyMarkersBySuburb[suburbName].forEach(item => {
                        if (item.marker.getMap() !== null) {
                            item.marker.setMap(null);
                        }
                    });
                });
                $("#product-list").html('<p>No projects found</p>');
            }

            // High zoom: hide suburb pins in view and show their property pins; keep suburb pins visible outside the view.
            function showPropertyView(map, bounds, ico, infowindow) {
                const visibleProperties = [];
                mapMarkers.forEach(mainMarker => {
                    const suburbName = mainMarker.getTitle();
                    if (bounds.contains(mainMarker.getPosition())) {
                        if (mainMarker.getMap() !== null) {
                            mainMarker.setMap(null);
                        }
                        if (!propertyMarkersBySuburb[suburbName]) {
                            propertyMarkersBySuburb[suburbName] = createPropertyMarkers(suburbName, map, ico, infowindow);
                        }
                        propertyMarkersBySuburb[suburbName].forEach(item => {
                            if (item.marker.getMap() === null) {
                                item.marker.setMap(map);
                            }
                            visibleProperties.push(item);
                        });
                    } else {
                        if (mainMarker.getMap() === null) {
                            mainMarker.setMap(map);
                        }
                        if (propertyMarkersBySuburb[suburbName]) {
                            propertyMarkersBySuburb[suburbName].forEach(item => {
                                if (item.marker.getMap() !== null) {
                                    item.marker.setMap(null);
                                }
                            });
                        }
                    }
                });
                renderPropertyCards(visibleProperties);
            }

            // Build (once) the property pins for a suburb; markers are reused afterwards.
            function createPropertyMarkers(suburbName, map, ico, infowindow) {
                const propertyRatings = groupedReviews[suburbName]['property_ratings'];
                const markers = [];

                // Suburb centre (from the Suburbs table) is used as a fallback: the map decides
                // visibility by suburb, so a property whose own coordinates are missing or belong to
                // a different area (e.g. a default placeholder) would otherwise never appear in view.
                let suburbCenter = null;
                if (suburbsLocations[suburbName] && suburbsLocations[suburbName][0]) {
                    suburbCenter = {
                        lat: +suburbsLocations[suburbName][0]['latitude'],
                        lng: +suburbsLocations[suburbName][0]['longitude']
                    };
                }

                let fallbackCount = 0;
                Object.keys(propertyRatings).forEach(propertyName => {
                    const propertyData = propertyRatings[propertyName];
                    let lat = +propertyData['data']['lat'];
                    let lng = +propertyData['data']['long'];

                    // Use the property's own coordinates only when they are valid and actually near
                    // the suburb (~0.5°); otherwise anchor the pin to the suburb centre.
                    const nearSuburb = lat && lng && suburbCenter &&
                        Math.abs(lat - suburbCenter.lat) <= 0.5 &&
                        Math.abs(lng - suburbCenter.lng) <= 0.5;

                    if (!nearSuburb) {
                        if (!suburbCenter) {
                            return;
                        }
                        // nudge each fallback pin slightly so multiple properties don't stack exactly
                        lat = suburbCenter.lat + fallbackCount * 0.0009;
                        lng = suburbCenter.lng + fallbackCount * 0.0009;
                        fallbackCount++;
                    }

                    const marker = new MarkerWithLabel({
                        title: propertyName,
                        position: {lat, lng},
                        map: map,
                        icon: ico,
                        optimized: false,
                        labelContent: '',
                        labelClass: 'property-pin'
                    });

                    let propertyRoute = '{{route('property-review-slug',':property_slug')}}';
                    propertyRoute = propertyRoute.replace(':property_slug', propertyData['data']['slug']);

                    let companyRoute = '';
                    if (propertyData['data']['company']) {
                        companyRoute = '{{route('property.company',':company_slug')}}';
                        companyRoute = companyRoute.replace(':company_slug', propertyData['data']['company']['slug']);
                    }

                    google.maps.event.addListener(marker, 'click', function () {
                        const previewPopup = '<div>' +
                            '<p><a href=' + propertyRoute + '>' + propertyName + '</a></p>' +
                            '</div>';
                        infowindow.setContent(previewPopup);
                        infowindow.open(map, marker);
                    });

                    markers.push({
                        marker: marker,
                        propertyName: propertyName,
                        data: propertyData,
                        rating: propertyData['rating'],
                        propertyRoute: propertyRoute,
                        companyRoute: companyRoute
                    });
                });
                return markers;
            }

            // Rebuild the left-hand list from the currently visible property pins, sorted by rating.
            function renderPropertyCards(visibleProperties) {
                if (visibleProperties.length === 0) {
                    $("#product-list").html('<p>No projects found</p>');
                    return;
                }

                visibleProperties.sort((a, b) => b.rating - a.rating);

                $("#product-list").empty();
                visibleProperties.forEach((item, index) => {
                    // keep the numbered pin label in sync with the card order
                    item.marker.set('labelContent', index + 1);
                    setPropertyInCards(item.data, index, item.propertyRoute, item.companyRoute);
                });
            }


            function setPropertyInCards(propertyData, index, propertyRoute, companyRoute) {
                var propertyView = '<div class="productresult_box">\n' +
                    '                        <div class="count-maker">' + (index + 1) + '</div>\n' +
                    '                        <div class="productresult_box_in">\n' +
                    '                            <div class="productresult_box_img"\n' +
                    '                                 style="background: url(' + (propertyData['data']['full_property_logo'] !== null ? propertyData['data']['full_property_logo'] : 'images/property-review-img-1.png') + ') no-repeat center / cover;">\n' +
                    '                            </div>\n' +
                    '                            <div class="product_content">\n' +
                    '                                <h3><a href=' + propertyRoute + '>' + propertyData["data"]["project_name"] + '</a></h3>\n' +
                    '                                <div class="product_content_info">\n';
                if (companyRoute) {
                    propertyView += '<p>Company name : <a href=' + companyRoute + '>' + propertyData["data"]["company"]["company_name"] + '</a></p>\n';
                }

                propertyView += '                                    <p>Overall Rating : </p>\n' +
                    '                                </div>\n' +
                    '                                <div class="product-rating">\n' +
                    '                                    <ul class="general-star-rating overall-rate-color-' + ratingClass(propertyData["rating"]) + '">\n' +
                    '                                        <li><a href=""><img src="images/rating-img.png"></a></li>\n' +
                    '                                        <li><a href=""><img src="images/rating-img.png"></a></li>\n' +
                    '                                        <li><a href=""><img src="images/rating-img.png"></a></li>\n' +
                    '                                        <li><a href=""><img src="images/rating-img.png"></a></li>\n' +
                    '                                        <li><a href=""><img src="images/rating-img.png"></a></li>\n' +
                    '                                    </ul>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>';

                $("#product-list").append(propertyView);
            }
        });
    </script>
@endsection
