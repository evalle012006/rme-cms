@section('extra-style')
    @extends('layouts.app-layout')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet"/>
    <style>
        .w-100 {
            width: 100%;
        }
    </style>
@endsection
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
                            <li><a class="active-profile-tab" href="{{route('builder-dashboard')}}">
                                    <img class="side-icons" src="{{asset('images/dashboard-icon.svg')}}">Dashboard</a>
                            </li>
                            <li>
                                <a href="{{ route('builder-property') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">My Property</a>
                            </li>
                            <li>
                                <a href="{{ route('company-users') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">Company Users</a>
                            </li>
                        </ul>
                    </div>
                </div>


                <div class="profile-right-part background-none">
                    <div class="latest-property-post-section">
                        <div class="profile-title">
                            <h4>Company Bio</h4>
                            <div class="text-right all-props">
                                <a href="javascript:void(0)" id="company-bio-edit">Edit</a>
                                <button class="btn btn-bordered" id="company-bio-submit">Submit</button>
                            </div>
                        </div>
                        <div class="company-bio-wrap">
                            <p id="company-bio">
                                {{$company->bio}}
                            </p>
                            <input type="hidden" id="company-id" value="{{$company->id}}"/>
                            <textarea id="company-bio-input">{{$company->bio}}</textarea>
                        </div>
                    </div>



                    <div class="total-reviews-month-week-section">
                        <div class="profile-title">
                            <h4>Total Reviews Month And Week Wise</h4>
                            <div class="text-right all-props">
                                <div class="form-row radio-container custom-radio-style">
                                    <input id="DigitalBush" type="text" class="digital-bush"/>
                                </div>
                            </div>
                            <div class="counter-wrap">
                                <div class="counter-card">
                                    <p>Month : <span id="month-name">{{\Carbon\Carbon::now()->monthName}}</span>
                                    </p>
                                    <p>
                                        Review Count : <span id="monthly-count">{{$monthlyCount}}</span>
                                    </p>
                                </div>
                                <div class="counter-card">
                                    <p>
                                        Year : <span id="year-name">{{\Carbon\Carbon::now()->year}}</span>
                                    </p>
                                    <p>
                                        Review Count : <span id="yearly-count">{{$yearlyCount}}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="total-reviews-month-week-section">
                        <div class="profile-title">
                            <h4>Total Reviews Date Wise</h4>
                            <div class="text-right all-props">
                                <div class="form-row radio-container custom-radio-style">
                                    <input id="date-range" type="text" class="digital-bush"/>
                                </div>
                            </div>
                            <div class="counter-wrap">
                                <div class="counter-card">
                                    <p>Date : <span id="start-date">test</span> to <span id="end-date">test</span>
                                    </p>
                                    <p>
                                        Review Count : <span id="date-range-count">{{$dateRangeCount}}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="latest-property-post-section">
                        <div class="profile-title">
                            <h4>Latest Property Post</h4>
                            <div class="text-right all-props">
                                <a class="mr-30" href="{{route('property-listing')}}">Compare Reviews</a>
                                <a href="{{url('builder/my-property')}}">View All Properties</a>
                            </div>
                        </div>
                        <div class="latest-property-post-wrap">
                            @if(count($latestProperties) ===  0)
                                <div class="no-data"><p>No Data Found</p></div>
                            @endif
                            @foreach($latestProperties as $property)
                                <div class="latest-property-single">
                                    <div class="property-img-wrap">
                                        <a href="#">
                                            <img
                                                src="{{$property->full_property_logo}}">
                                        </a>
                                    </div>
                                    <div class="property-detail">
                                        <div class="rating-wrap">
                                            <ul class="general-star-rating overall-rate-color-{{ratingClass($property->overall_rating)}}">
                                                @foreach(range(0,4) as $number)
                                                    <li><a href=""><img
                                                                src="{{asset('images/rating-img.png')}}"></a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="author-btm">
                                            <p>
                                                <a href="{{url('property-review/'.$property->slug)}}">{{$property->project_name}}</a>
                                            </p>
                                        </div>
                                        <div class="tags-wrap">
                                            <ul>
                                                @foreach($property->review_tags as $tag)
                                                    <li><a>{{$tag}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="btn-wrap">
                                            <a class="btn btn-bordered"
                                               href="{{url('property-review/'.$property->slug)}}">More Info</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="amenities-wise-reviews-section">
                        <div class="profile-title">
                            <h4>Rating Attributes</h4>
                            <div class="text-right all-props">
                                Total Reviews : <p id="amenity-review-count">{{$totalReviews}}</p>
                            </div>
                        </div>
                        <div class="filter-row">
                            <form
                                id="rating-form"
                                action="#"
                                method="GET"
                            >
                                <div class="sort-filter">
                                    <div class="filter-wrap">
                                        <select class="w-100 rating-field" id="project_id" name="project_id">
                                            <option value="">-- all projects --</option>
                                            @foreach($propsOptions as $option)
                                                <option value="{{$option->id}}">{{$option->project_name}}</option>
                                            @endforeach
                                        </select>

                                        <select class="rating-field" id="rating_state">
                                            <option value="">-- all states --</option>
                                            @foreach($states as $state)
                                                <option value="{{$state->id}}">{{$state->name}}</option>
                                            @endforeach
                                        </select>
                                        {{--                                        <input type="text" name="xyz" class="rating-field" id="rating_suburb"--}}
                                        {{--                                               placeholder="Search by Suburb">--}}

                                        <select class="rating-field w-100" id="rating_suburb">
                                            {{--                                            <option value="">-- all suburbs --</option>--}}
                                        </select>


                                        {{--                                        <input type="text" class="rating-field" id="rating_state"--}}
                                        {{--                                               placeholder="Search by State">--}}
                                        <select id="rating_category" class="rating-field">
                                            <option value="">-- all categories --</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="rating-search" id="rating-search">Search</button>
                                </div>
                            </form>
                        </div>
                        <div class="amenities-wise-reviews-wrap">

                            <div class="amenities-graph">
                                <div id="results-section">
                                    @foreach($reviewQuestions as $question=>$row)
                                        <div class="amenities-graph-single">
                                            <div class="graph-title">{{$question}}</div>
                                            <div class="progressbar-wrap">
                                                <div
                                                    style=" width: {{isset($amenityWiseRatings[$question]) ? $amenityWiseRatings[$question] : 0.0}}%; float: left; background-color: {{$row['color_code']}};"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="stars-wrap">
                                    <div class="star star1">1Star</div>
                                    <div class="star star2">2Star</div>
                                    <div class="star star3">3Star</div>
                                    <div class="star star4">4Star</div>
                                    <div class="star star5">5Star</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="property-wise-reviews-section" id="last-section">
                        <div class="profile-title">
                            <h4>Property Wise Reviews</h4>
                            <div class="text-right all-props">
                                Total Reviews : {{$totalReviews}}
                            </div>
                        </div>
                        <div class="filter-row">
                            <form
                                action="{{route('builder-dashboard')}}"
                                method="GET">
                                <div class="sort-filter">
                                    <h4>Filter by:</h4>
                                    <div class="filter-wrap">
                                        <div class="filter-srch-row">
                                            <select class="rating-field" name="state">
                                                <option value="" {{request()->input('state')=='' ?'selected':'' }}>--
                                                    all states
                                                    --
                                                </option>
                                                @foreach($states as $state)
                                                    <option
                                                        value="{{$state->name}}" {{request()->input('state')==$state->name ?'selected':'' }}>{{$state->name}}</option>
                                                @endforeach
                                            </select>
                                            {{--                                            <input type="text" name="suburb"--}}
                                            {{--                                                   value="{{request()->input('suburb')}}"--}}
                                            {{--                                                   placeholder="Search by Suburb">--}}

                                            <select class="rating-field w-100" id="property_suburb" name="suburb">
                                            </select>
                                            {{--                                            <input type="text" name="zipcode"--}}
                                            {{--                                                   value="{{request()->input('zipcode')}}"--}}
                                            {{--                                                   placeholder="Search by zipcode">--}}
                                            <input type="text" name="project_name"
                                                   value="{{request()->input('project_name')}}"
                                                   placeholder="Search by Project Name">
                                            <input type="submit" name="" value="Search">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="property-wise-reviews-wrap">

                            @if(count($allPropertyReviews) ===  0)
                                <div class="no-data"><p>No Data Found</p></div>
                            @else

                                @foreach($allPropertyReviews as $property)
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
                                                   href="{{url('property-review/'.$property->slug)}}">More Info</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="property-wise-reviews-section" id="last-section">
                        <div class="profile-title">
                            <h4>Export Property Reviews</h4>
                        </div>
                        <div class="filter-row">
                            <form
                                action="{{route('builder-dashboard')}}"
                                method="GET">
                                <div class="sort-filter">
                                    <h4>Search project:</h4>
                                    <div class="filter-wrap filter-wrap-export">
                                        <div class="filter-srch-row">
                                            {{--                                            <input type="text" name="export_property"--}}
                                            {{--                                                   value="{{request()->input('export_property')}}"--}}
                                            {{--                                                   placeholder="Search by project">--}}
                                            <select class="w-100 rating-field" id="project_id_export"
                                                    name="project_id_export">
                                                <option value="">-- select option --</option>
                                                @foreach($propsOptions as $option)
                                                    <option
                                                        value="{{$option->id}}" {{\request()->input('project_id_export')==$option->id ? 'selected' : ''}}>{{$option->project_name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="submit" name="" value="Search">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="property-wise-reviews-wrap">
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
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div id="userModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content addnew-property">
            <div class="modal-header">
                <span class="close closeAdvertise">
                    <img src="{{asset('images/close-icon.png')}}" alt="">
                </span>
                <h4>Add New Advertise</h4>
            </div>
            <div class="modal-body">
                <form action="{{route('add-builder-user')}}" method="POST" id="builder-form"
                      enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <div class="property-form">
                        <div class="property-form">
{{--                            <div class="col-50">--}}
{{--                                <label>Profile Photo</label>--}}
{{--                                <div class="user-img-wrap">--}}
{{--                                    <img id="advertise-image" class="image-preview">--}}
{{--                                </div>--}}
{{--                                <div class="upload-photo">--}}
{{--                                    <a href="javascript:;" class="btn yellow-btn">Upload Photo--}}
{{--                                        <input type='file'--}}
{{--                                               name="profile_image"--}}
{{--                                               class="btn yellow-btn"--}}
{{--                                               onchange="readImageUrl(this);"--}}
{{--                                        /></a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-50">
                                <label>Firstname</label>
                                <input type="text" name="firstname" placeholder="John"/>
                            </div>
                            <div class="col-50">
                                <label>Lastname</label>
                                <input type="text" name="lastname" placeholder="Doe"/>
                            </div>
                        </div>
                        <div class="property-form">
                            <div class="col-50">
                                <label>Mobile</label>
                                <input type="text" name="mobile" placeholder="9876543210"/>
                            </div>
                            <div class="col-50">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="john@mail.com">
                            </div>
                        </div>
                        <div class="property-form">
                            <div class="col-50">
                                <label>Password</label>
                                <input type="password" name="password" placeholder="********">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn view-detail-link">Add User</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    {{--    <script src="https://cdnjs.com/libraries/Chart.js"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>


    <script src="https://cdn.rawgit.com/digitalBush/jquery.maskedinput/1.4.1/dist/jquery.maskedinput.min.js"></script>
    <script src="https://kidsysco.github.io/jquery-ui-month-picker/MonthPicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    {{--  date range picker --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    {{-- end --}}
    <script>
        $(document).ready(function () {
            let msg = '';
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const reviewQuestions = {!! json_encode($reviewQuestions) !!};
            const dragToEnd = {!! json_encode($dragToEnd) !!};

            if (dragToEnd) {
                $('html, body').animate({
                    scrollTop: $("#last-section").offset().top - 200
                }, 1000);
            }

            @if(session('profile-update'))
                msg = '<?= session('profile-update') ?>'
            Swal.fire({
                icon: 'success',
                title: 'Success...',
                text: msg
            })
            @endif
            const params = new URLSearchParams(window.location.search);
            const propertySuburb = params.get('suburb');

            if (propertySuburb) {
                var $newOption = $("<option selected='selected'></option>").val(propertySuburb).text(propertySuburb)
                $("#property_suburb").append($newOption).trigger('change');
            }


            $("#property_suburb").select2({
                ajax: {
                    url: "{{route('get-property-suburbs')}}",
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: function (params) {
                        return {
                            search: params.term, // search term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results,
                        };
                    },
                },
                allowClear: true,
                placeholder: '-- all suburbs --',

            });


            // for rating attribute

            $("#rating_suburb").select2({
                placeholder: '-- all suburbs --',
                ajax: {
                    url: "{{route('get-property-suburbs')}}",
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: function (params) {
                        return {
                            search: params.term, // search term
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results,
                        };
                    },
                },
                allowClear: true,

            });

            // end

            $("#project_id").select2();
            $("#project_id_export").select2();

            $('#rating-form').submit(function () {
                event.preventDefault();
                const property = $('#project_id').val();
                const suburb = $('#rating_suburb').val();
                const state = $('#rating_state').val();
                const category = $('#rating_category').val();
                $.ajax({
                    url: "{{route('amenities-by-project')}}",
                    type: 'POST',
                    data: JSON.stringify({property, suburb, state, category}),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'JSON',
                    success: function (res) {
                        if (res['data'] !== undefined) {
                            const data = res['data'];
                            let chartHtml = '';
                            Object.keys(reviewQuestions).forEach(question => {
                                chartHtml += '<div class="amenities-graph-single">' +
                                    '<div class="graph-title">' + question + '</div>' +
                                    '<div class="progressbar-wrap">' +
                                    '<div style=" width: ' + (data[question] ? data[question] : 0) + '%; float: left; background-color: ' + reviewQuestions[question]['color_code'] + ';"></div>' +
                                    '</div></div>';
                            });

                            $("#results-section").html(chartHtml);
                        }

                        if (res['review_count'] !== undefined) {
                            $("#amenity-review-count").text(res['review_count']);
                        }
                    }
                })
            });

            $("#start-date").text(moment().startOf('month').format('MMMM DD, YYYY'));
            $("#end-date").text(moment().format('MMMM DD, YYYY'));

            $("#date-range").daterangepicker({
                opens: 'left',
                startDate: moment().startOf('month'),
                endDate: moment(),
            }, function (start, end, label) {
                const start_date = start.format('YYYY-MM-DD HH:mm:ss');
                const end_date = end.format('YYYY-MM-DD HH:mm:ss');
                $.ajax({
                    url: "{{route('date-wise-counts')}}",
                    type: 'POST',
                    data: JSON.stringify({start_date, end_date}),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'JSON',
                    success: function (res) {
                        $("#start-date").text(start.format('MMMM DD, YYYY'));
                        $("#end-date").text(end.format('MMMM DD, YYYY'));
                        $("#date-range-count").text(res['count']);
                    }
                })
            });

            $('#DigitalBush').MonthPicker({
                UseInputMask: true
            });

            $("#DigitalBush").change(function (e) {
                const data = e.target.value.split('/');
                const currentYear = (new Date()).getFullYear();
                const currentMonth = (new Date()).getMonth() + 1;
                let month = data[0];
                let year = data[1];

                if (year > currentYear) {
                    month = currentMonth;
                    year = currentYear;
                    $("#DigitalBush").val(month + '/' + year);
                } else {
                    if (month > currentMonth) {
                        month = currentMonth;
                        year = currentYear;
                        $("#DigitalBush").val(month + '/' + year);
                    }
                }
                $.ajax({
                    url: "{{route('review-counts')}}",
                    type: 'POST',
                    data: JSON.stringify({month, year}),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'JSON',
                    success: function (res) {
                        $("#month-name").text(months[month - 1]);
                        $("#monthly-count").text(res['monthly']);
                        $("#year-name").text(year);
                        $("#yearly-count").text(res['yearly']);
                    }
                })
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

            $("#company-bio-submit").hide();
            $("#company-bio-input").hide();

            $("#company-bio-edit").click(function () {
                $("#company-bio-edit").hide();
                $("#company-bio").hide();
                $("#company-bio-submit").show();
                $("#company-bio-input").show();
            });

            $("#company-bio-submit").click(function () {
                $("#company-bio-submit").prop('disabled', true);
                const companyBio = $("#company-bio-input").val();
                const data = {
                    id: $("#company-id").val(),
                    bio: companyBio
                };
                jQuery.ajax({
                    url: "{{route('update-bio')}}",
                    type: "POST",
                    data: data,
                    dataType: 'JSON',
                    success: function (res) {
                        $("#company-bio-submit").prop('disabled', false);
                        if (res === 'success') {
                            $("#company-bio-edit").show();
                            $("#company-bio").show();
                            $("#company-bio").text(companyBio);
                            $("#company-bio-submit").hide();
                            $("#company-bio-input").hide();
                        }
                    }, error: function () {
                        $("#company-bio-submit").prop('disabled', false);
                    }
                });
            });
        })
        ;
    </script>
@endsection

