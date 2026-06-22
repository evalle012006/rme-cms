@extends('layouts.app-layout')

@section('content')

    <div class="my-profile-section">
        @if($user)
            <div class="container">
                <div class="profile-left-part builder-panel">
                    <a class="export-btn" href={{route('export-company')}}>
                        <button class="btn btn-bordered">Export</button>
                    </a>
                    <div class="user-img-wrap">
                        <img id="user-profile-image"
                             src="{{$user->company->logo ? url(Storage::url($user->company->logo)) : asset('images/user-img.png')}}"
                             alt="file upload"/>
                    </div>
                    <div class="user-detail">
                        <input type="hidden" id="hidden_id" value={{$user->company_id}} />

                        <h6>{{$user->firstname}}</h6>
                        <span>{{$user->email}}</span>
                    </div>
                    <div class="upload-photo">
                        <a href="javascript:;" class="btn yellow-btn">Upload New Logo
                            <input type='file'
                                   name="profile_image"
                                   id="profile_image"
                                   class="btn yellow-btn"
                            />
                        </a>
                    </div>
                    <div class="sidebar-menu">
                        <ul>
                            <li><a href="{{route('builder-dashboard')}}">
                                    <img class="side-icons" src="{{asset('images/dashboard-icon.svg')}}">Dashboard</a>
                            </li>
                            <li>
                                <a class="active-profile-tab" href="{{ route('builder-property') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">My Property</a>
                            </li>
                            <li>
                                <a href="{{ route('company-users') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">Company Users</a>
                            </li>
                        </ul>
                    </div>
                </div>


                <div class="profile-right-part">
                    <div class="my-properties">
                        <div class="title">
                            <h4>My Project Reviews</h4>
                        </div>
                        <div class="my-properties-list">
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
                        </div>
                        {{$properties->appends(request()->input())->links()}}


                        <div class="pending-properties">
                            <div class="title">
                                <h4>Pending Property</h4>
                            </div>
                            <div class="pending-properties-list">
                                <div class="pending-properties-wrap">
                                    @foreach($pendingProperties as $property)
                                        <div class="pending-properties-single">
                                            <div class="property-img property-logo" data-id="{{$property->id}}"  data-img="{{$property->full_property_logo}}">
                                                <img
                                                    src="{{$property->full_property_logo}}">
                                            </div>
                                            <div class="property-details">
                                                <h6>
                                                    <a href="#">{{$property->project_name}}</a>
                                                </h6>
                                                <p>{{$property->description}}</p>
                                                <p>{{$property->address ? $property->address.', ': ''}}
                                                    {{$property->city ? $property->city.', ':''}}
                                                    {{$property->state ? $property->state.', ' : ''}}
                                                    {{$property->country ? $property->country.', ' : ''}}
                                                    {{$property->post_code}}</p>
                                                {{--                                                <a class="view-detail-link" href="#">View Details</a>--}}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            {{$pendingProperties->appends(request()->input())->links()}}
                            <div class="btn-block">
                                <button class="btn view-detail-link" id="myBtn">Add New Project</button>
                            </div>
                        </div>

                        {{--                        <div class="pending-properties">--}}
                        {{--                            <div class="title">--}}
                        {{--                                <h4>Property Advertise</h4>--}}
                        {{--                            </div>--}}
                        {{--                            <div class="pending-properties-list">--}}
                        {{--                                <div class="pending-properties-wrap">--}}
                        {{--                                    @foreach($advertises as $advertise)--}}
                        {{--                                        <div class="pending-properties-single">--}}
                        {{--                                            <div class="property-img">--}}
                        {{--                                                <img--}}
                        {{--                                                    src="{{$advertise->full_image_path}}">--}}
                        {{--                                            </div>--}}
                        {{--                                            <div class="property-details">--}}
                        {{--                                                <h6>--}}
                        {{--                                                    <p>{{$advertise->title}}</p`>--}}
                        {{--                                                </h6>--}}
                        {{--                                                <p>{{$advertise->description}}</p>--}}
                        {{--                                            </div>--}}
                        {{--                                            @if($advertise->status===0)--}}
                        {{--                                                <button class="btn pending-btn">Pending</button>--}}
                        {{--                                            @endif--}}
                        {{--                                        </div>--}}
                        {{--                                    @endforeach--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                            {{$advertises->appends(request()->input())->links()}}--}}
                        {{--                            <div class="btn-block">--}}
                        {{--                                <button class="btn view-detail-link" id="myAdvertiseBtn">Add New Advertise</button>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Add Project Modal -->
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content addnew-property">
            <div class="modal-header">
                <span class="close">
                    <img src="{{asset('images/close-icon.png')}}" alt="">
                </span>
                <h4>Add New Project</h4>
            </div>
            <div class="modal-body">
                <form action="{{url('builder/create-property')}}" method="POST" id="property-form"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="developer_name" value="{{$company->id}}">
                    <div class="property-form">
                        <div class="col-50">
                            <label>Project Logo</label>
                            <div class="user-img-wrap">
                                <img id="property-image" class="image-preview">
                            </div>
                            <div class="upload-photo">
                                <a href="javascript:;" class="btn yellow-btn">Upload Logo
                                    <input type='file'
                                           name="logo"
                                           class="btn yellow-btn"
                                           onchange="readImageUrl(this,'property-image');"
                                    /></a>
                            </div>
                        </div>
                        <div class="col-50"></div>
                        <div class="col-50">
                            <label>Property Name</label>
                            <input type="text" name="project_name" placeholder="Homesite Glassed usa">
                        </div>
                        <div class="col-50">
                            <label>Email</label>
                            <input type="text" name="email" placeholder="johnsmith@gmail.com">
                        </div>
                        <div class="col-50">
                            <label>Phone Number</label>
                            <input type="text" name="phone" placeholder="+01 424 1250 1010">
                        </div>
                        <div class="col-50">
                            <label>Website</label>
                            <input type="text" name="website_url" placeholder="www.ratemyestate.com">
                        </div>
                        <div class="col-50 form-row">
                            <label>category</label>
                            <select class="w-100" id="cat_id" name="categories[]" multiple="multiple">
                            </select>
                        </div>
                        <div class="col-50">
                            <label>description</label>
                            <input type="text" name="description" placeholder="Lroem upsum dolor sit here.......">
                        </div>
                    </div>
                    <div class="property-form">
                        <div class="col-50">
                            <label>Address</label>
                            <input type="text" name="address" placeholder="123 street,...">
                        </div>
                        <div class="col-50">
                            <label>City</label>
                            <input type="text" name="city" placeholder="Melbourne">
                        </div>
                        <div class="col-50">
                            <label>State</label>
                            <input type="text" name="state" placeholder="Victoria">
                        </div>

                        <div class="col-50">
                            <label>Post code</label>
                            <input type="text" name="post_code" placeholder="2255">
                        </div>

                        <div class="col-50">
                            <label>Location (With google Map)</label>
                            <input type="text" id="google_address"
                                   placeholder="7222 Kingston Street Lynchburg, VA 24502">
                            <input type="hidden" name="lat" id="map_lat">
                            <input type="hidden" name="long" id="map_long">
                        </div>
                        <div class="col-50">
                            <label>Country</label>
                            <input type="text" name="country" placeholder="Australia">
                        </div>

                    </div>
                    <div class="property-map" id="pinned-map-adv">
                    </div>
                    <button type="submit" class="btn view-detail-link">Add Property</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Advertise Modal -->
    <div id="myAdvertiseModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content addnew-property">
            <div class="modal-header">
                <span class="close closeAdvertise">
                    <img src="{{asset('images/close-icon.png')}}" alt="">
                </span>
                <h4>Add New Advertise</h4>
            </div>
            <div class="modal-body">
                <form action="{{route('create-adv')}}" method="POST" id="advertise-form" enctype="multipart/form-data">
                    @csrf
                    <div class="property-form">
                        <div class="col-50">
                            <label>Image</label>
                            <div class="user-img-wrap">
                                <img id="advertise-image" class="image-preview">
                            </div>
                            <div class="upload-photo">
                                <a href="javascript:;" class="btn yellow-btn">Upload Photo
                                    <input type='file'
                                           name="image"
                                           class="btn yellow-btn"
                                           onchange="readImageUrl(this,'advertise-image');"
                                    /></a>
                            </div>
                        </div>
                        <div class="col-100">
                            <label>Title</label>
                            <input type="text" name="title" placeholder="Homesite Glassed usa">
                        </div>
                        <div class="col-100">
                            <label>Description</label>
                            <textarea name="description"></textarea>
                            {{--                            <input type="text" name="description" placeholder="Lroem upsum dolor sit here.......">--}}
                        </div>
                    </div>
                    <button type="submit" class="btn view-detail-link">Add Advertise</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Property Logo Modal -->
    <div id="propertyLogoModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content addnew-property">
            <div class="modal-header">
                <span class="close closeLogoModal">
                    <img src="{{asset('images/close-icon.png')}}" alt="">
                </span>
                <h4>Change Property Logo</h4>
            </div>
            <div class="modal-body">
                <form action="{{route('update-property-logo')}}" method="POST" id="advertise-form"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="property-form">
                        <input type="hidden" name="id" id="property-id">
                        <div class="col-100">
                            <label>Image</label>
                            <div class="user-img-wrap">
                                <img id="property-logo" class="image-preview">
                            </div>
                            <div class="upload-photo">
                                <a href="javascript:;" class="btn yellow-btn">Upload Logo
                                    <input type='file'
                                           name="logo"
                                           class="btn yellow-btn"
                                           onchange="readImageUrl(this,'property-logo');"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn view-detail-link">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initMap&libraries=geometry,places&ver=5.4">
    </script>
    <script>

        function initMap() {
            let centerPoint = {lat: -37.8407634, lng: 144.9829243};
            // ask to allow location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    centerPoint = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                }, function () {
                    centerPoint = {lat: -37.8407634, lng: 144.9829243}; // australia, South Yarra - 3141
                }, {timeout: 60000});
            }
            // end

            // Initialize google map
            var map = new google.maps.Map(
                document.getElementById('pinned-map-adv'), {zoom: 11, center: centerPoint});
            // end

            // Initiate marker
            var marker = new google.maps.Marker({
                position: centerPoint,
                map: map
            });
            $("#map_lat").val(centerPoint.lat);
            $("#map_long").val(centerPoint.lng);
            // end

            // initiate autocomplete
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
            autocomplete = new google.maps.places.Autocomplete(document.getElementById('google_address'), options);
            autocomplete.addListener('place_changed', function (e) {
                if (autocomplete.getPlace().geometry) {
                    const {location} = autocomplete.getPlace().geometry;
                    const lat = location.lat();
                    const lng = location.lng();
                    map.setCenter({lat, lng});
                    map.setZoom(12);
                    marker.setPosition({lat, lng});
                    $("#map_lat").val(lat);
                    $("#map_long").val(lng);
                }
            });
            // end
        }

        $(document).ready(function () {
            let msg = '';
            @if(session('profile-update'))
                msg = '<?= session('profile-update') ?>';
            Swal.fire({
                icon: 'success',
                title: 'Success...',
                text: msg
            });
            @endif
            $("#property-form").validate({
                rules: {
                    project_name: 'required',
                    email: {
                        required: true,
                        email: true
                    },
                    website_url: {
                        url: true
                    },
                    lat: 'required',
                    long: 'required',
                    'categories[]': {
                        required: true
                    }
                },
                messages: {
                    project_name: 'Project name is required',
                    email: {
                        required: 'Email is required',
                        email: 'Enter valid email address'
                    },
                    'categories[]': {
                        required: 'You must select one category'
                    }
                },
                errorPlacement: function (error, element) {
                    element.parent().append(error);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });
            $("#advertise-form").validate({
                rules: {
                    title: 'required',
                    description: 'required'
                },
                messages: {
                    title: 'Title Field is required',
                    description: 'Description field is required'
                },
                errorPlacement: function (error, element) {
                    element.parent().append(error);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });
            const categories = {!! json_encode($propertyCategory); !!};
            const options = categories.map(item => {
                return {'text': item['name'], 'id': item['id']};
            });

            $('#cat_id').select2({
                maximumSelectionLength: 5,
                placeholder: 'Select Category',
                data: options,
                language: {
                    "noResults": function () {
                        return "No Results Found";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
            $('#cat_id').on('select2:select', function (e) {
                $("#property-form").validate().element("#cat_id");
            });
            $('#cat_id').on('select2:unselect', function (e) {
                $("#property-form").validate().element("#cat_id");
            });

            $("#profile_image").change(function (e) {
                const filenameArr = (e.target.value).split('.');
                const extension = filenameArr[filenameArr.length - 1];
                if (!['png', 'jpg', 'jpeg'].includes(extension)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select either jpg or png file'
                    });
                } else {
                    readURL(e.target);
                    if (this.files[0]) {
                        const id = $("#hidden_id").val();
                        const fData = new FormData();
                        fData.append('id', id);
                        fData.append('profile_image', this.files[0]);
                        jQuery.ajax({
                            url: "{{route('update-logo')}}",
                            type: "POST",
                            data: fData,
                            processData: false,
                            contentType: false,
                            success: function () {
                            }
                        });
                    }
                }
            });
        });
        // Get the modal
        var modal = document.getElementById("myModal");
        var logoModal = document.getElementById("propertyLogoModal");
        // var modalAdvertise = document.getElementById("myAdvertiseModal");

        // Get the button that opens the modal
        var btn = document.getElementById("myBtn");
        // var btnAdvertise = document.getElementById("myAdvertiseBtn");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        var spanLogo = document.getElementsByClassName("closeLogoModal")[0];
        // var spanAdvertise = document.getElementsByClassName("closeAdvertise")[0];

        // When the user clicks the button, open the modal
        btn.onclick = function () {
            modal.style.display = "block";
            $("body").addClass("popup-show");
        }
        $(".property-logo").click(function () {
            logoModal.style.display = "block";
            $("body").addClass("popup-show");
            $("#property-id").val($(this).data('id'));
            $("#property-logo").attr({'src' : $(this).data('img')});
        })

        // btnAdvertise.onclick = function () {
        //     modalAdvertise.style.display = "block";
        //     $("body").addClass("popup-show");
        // }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
            $("body").removeClass("popup-show");
        }

        spanLogo.onclick = function () {
            logoModal.style.display = "none";
            $("body").removeClass("popup-show");
        }


        // spanAdvertise.onclick = function () {
        //     modalAdvertise.style.display = "none";
        //     $("body").removeClass("popup-show");
        // }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
                $("body").removeClass("popup-show");
            }

            if (event.target == logoModal) {
                logoModal.style.display = "none";
                $("body").removeClass("popup-show");
            }

            // if (event.target == modalAdvertise) {
            //     modalAdvertise.style.display = "none";
            //     $("body").removeClass("popup-show");
            // }
        }
    </script>
@endsection

