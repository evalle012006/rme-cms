@extends('layouts.app-layout')

@section('meta_robots', 'noindex, nofollow')

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
                             src="{{ optional($user->company)->full_company_logo ?: asset('images/user-img.png') }}"
                             alt="Company logo"/>
                    </div>
                    <div class="user-detail">
                        <input type="hidden" id="hidden_id" value="{{ $user->company_id }}"/>

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
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">My Project</a>
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
                        <div class="my-properties-list" id="builder-my-property-results">
                            @include('partials.builder-my-property-list')
                        </div>


                        <div class="pending-properties">
                            <div class="title">
                                <h4>Pending Project</h4>
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
                            <label>Project Name</label>
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
                            <select class="w-100" id="add_project_state" name="state">
                                <option value=""></option>
                                @foreach($states as $state)
                                    <option value="{{ $state->name }}" data-id="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-50">
                            <label>Suburb</label>
                            <select class="w-100" id="add_project_suburb" disabled></select>
                            <input type="hidden" name="suburb" id="add_project_suburb_name">
                        </div>
                        <div class="col-50">
                            <label>Post code</label>
                            <input type="text" name="post_code" id="add_project_post_code" placeholder="2255" readonly>
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
                    <button type="submit" class="btn view-detail-link">Add Project</button>
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
                <h4>Change Project Logo</h4>
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
    <script>
        function loadBuilderMyProperties(url) {
            var $target = $('#builder-my-property-results');
            $target.css('opacity', '0.5');
            $.ajax({
                url: url,
                type: 'GET',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                success: function (html) {
                    $target.html(html);
                    if (window.history && window.history.pushState) {
                        window.history.pushState({builderMyPropertyAjax: true}, '', url);
                    }
                    if (typeof equalheight === 'function') {
                        equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
                    }
                },
                complete: function () {
                    $target.css('opacity', '1');
                }
            });
        }

        $(document).on('click', '#builder-my-property-results .pagination a', function (event) {
            event.preventDefault();
            var href = $(this).attr('href');
            if (href && href !== '#') {
                loadBuilderMyProperties(href);
            }
        });
    </script>
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
            // Accept website URLs with or without http(s):// (matches placeholder format)
            $.validator.addMethod("flexibleUrl", function (value, element) {
                if (this.optional(element)) {
                    return true;
                }
                var url = $.trim(value);
                if (!/^https?:\/\//i.test(url)) {
                    url = 'http://' + url;
                }
                return $.validator.methods.url.call(this, url, element);
            }, "Please enter a valid URL.");

            $("#property-form").validate({
                rules: {
                    project_name: 'required',
                    email: {
                        required: true,
                        email: true
                    },
                    website_url: {
                        flexibleUrl: true
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
                    website_url: {
                        flexibleUrl: 'Please enter a valid URL.'
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
                dropdownParent: $(document.body),
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

            const suburbsByStateUrl = "{{ route('autocomplete.fetchSuburbsByState') }}";

            function suburbNameFromId(id) {
                if (!id) return '';
                const separator = String(id).lastIndexOf('|');
                return separator === -1 ? String(id) : String(id).slice(0, separator);
            }

            function initAddProjectSelect($el, placeholder, disabled, results) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                const options = {
                    allowClear: true,
                    placeholder: placeholder,
                    width: '100%',
                    dropdownParent: $(document.body),
                    language: {
                        noResults: function () {
                            return 'No Results Found';
                        }
                    }
                };
                if (results) {
                    options.data = results;
                }
                $el.select2(options);
                $el.prop('disabled', !!disabled);
            }

            function initAddProjectSuburbSelect(placeholder, disabled, results) {
                const $el = $('#add_project_suburb');
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.empty().append(new Option('', '', false, false));
                initAddProjectSelect($el, placeholder, disabled, results || []);
            }

            function applyAddProjectSuburbSelection(data) {
                if (!data || !data.id) {
                    $('#add_project_suburb_name').val('');
                    $('#add_project_post_code').val('');
                    return;
                }
                const name = data.name || suburbNameFromId(data.id);
                $('#add_project_suburb_name').val(name);
                $('#add_project_post_code').val(data.postcode || '');
            }

            function loadAddProjectSuburbs(stateId) {
                if (!stateId) {
                    initAddProjectSuburbSelect('Select state first', true, []);
                    $('#add_project_suburb_name').val('');
                    $('#add_project_post_code').val('');
                    return;
                }

                initAddProjectSuburbSelect('Loading suburbs…', true, []);

                $.ajax({
                    url: suburbsByStateUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: { state_id: stateId },
                }).done(function (data) {
                    initAddProjectSuburbSelect('Select Suburb', false, data.results || []);
                    $('#add_project_suburb').val(null).trigger('change');
                }).fail(function () {
                    initAddProjectSuburbSelect('Select Suburb', false, []);
                });
            }

            window.resetAddProjectLocation = function () {
                $('#add_project_state').val(null).trigger('change');
                $('#add_project_suburb_name').val('');
                $('#add_project_post_code').val('');
                initAddProjectSuburbSelect('Select state first', true, []);
            }

            initAddProjectSelect($('#add_project_state'), 'Select State', false);
            initAddProjectSuburbSelect('Select state first', true, []);

            $('#add_project_suburb').on('change', function () {
                const selected = $(this).select2('data')[0];
                applyAddProjectSuburbSelection(selected);
            });

            $('#add_project_state').on('change', function () {
                const stateId = $(this).find('option:selected').data('id') || '';
                $('#add_project_suburb_name').val('');
                $('#add_project_post_code').val('');
                loadAddProjectSuburbs(stateId);
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
                            success: function (res) {
                                if (res && res.full_company_logo) {
                                    $("#user-profile-image").attr('src', res.full_company_logo);
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to update company logo'
                                });
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
            if (typeof window.resetAddProjectLocation === 'function') {
                window.resetAddProjectLocation();
            }
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
                if (typeof window.resetAddProjectLocation === 'function') {
                    window.resetAddProjectLocation();
                }
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

