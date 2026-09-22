@section('extra-style')
    @extends('layouts.app-layout')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet"/>
    <style>
        .w-100 {
            width: 100%;
        }

        .rating-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 5;
        }

        .rating-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(6, 148, 162, 0.2);
            border-top-color: #0694a2;
            border-radius: 50%;
            animation: rating-spin 0.8s linear infinite;
        }

        @keyframes rating-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
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
                            <li><a class="active-profile-tab" href="{{route('builder-dashboard')}}">
                                    <img class="side-icons" src="{{asset('images/dashboard-icon.svg')}}">Dashboard</a>
                            </li>
                            <li>
                                <a href="{{ route('builder-property') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">My Project</a>
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
                            <h4>Latest Project Post</h4>
                            <div class="text-right all-props">
                                <a class="mr-30" href="{{route('property-listing')}}">Compare Reviews</a>
                                <a href="{{route('builder-property')}}">View All Projects</a>
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

                                        <select class="rating-field w-100" id="rating_suburb" disabled>
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
                        <div class="amenities-wise-reviews-wrap" style="position: relative;">
                            <div id="rating-loader" class="rating-loader" style="display:none;">
                                <div class="rating-spinner"></div>
                            </div>

                            <div class="amenities-graph">
                                <div id="results-section">
                                    @foreach($reviewQuestions as $question=>$row)
                                        <div class="amenities-graph-single">
                                            <div class="graph-title">{{$question}}</div>
                                            <div class="progressbar-wrap">
                                                <div
                                                    style="width: {{isset($amenityWiseRatings[$question]) ? $amenityWiseRatings[$question] : 0}}%; float: left; background-color: {{!empty($row['color_code']) ? $row['color_code'] : '#0694a2'}};"></div>
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
                            <h4>Project Wise Reviews</h4>
                            <div class="text-right all-props">
                                Total Reviews : {{$totalReviews}}
                            </div>
                        </div>
                        <div class="filter-row">
                            <form id="builder-property-filter-form"
                                action="{{route('builder-dashboard')}}"
                                method="GET">
                                <div class="sort-filter">
                                    <h4>Filter by:</h4>
                                    <div class="filter-wrap">
                                        <div class="filter-srch-row">
                                            <select class="rating-field" id="property_state" name="state">
                                                <option value="" {{request()->input('state')=='' ?'selected':'' }}>--
                                                    all states
                                                    --
                                                </option>
                                                @foreach($states as $state)
                                                    <option
                                                        value="{{$state->name}}"
                                                        data-id="{{$state->id}}"
                                                        {{request()->input('state')==$state->name ?'selected':'' }}>{{$state->name}}</option>
                                                @endforeach
                                            </select>
                                            {{--                                            <input type="text" name="suburb"--}}
                                            {{--                                                   value="{{request()->input('suburb')}}"--}}
                                            {{--                                                   placeholder="Search by Suburb">--}}

                                            <select class="rating-field w-100" id="property_suburb" name="suburb" disabled>
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
                        <div class="property-wise-reviews-wrap" id="builder-property-reviews-results">
                            @include('partials.builder-property-reviews')
                        </div>
                    </div>

                    <div class="property-wise-reviews-section" id="last-section">
                        <div class="profile-title">
                            <h4>Export Project Reviews</h4>
                        </div>
                        <div class="filter-row">
                            <form id="builder-export-filter-form"
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
                        <div class="property-wise-reviews-wrap" id="builder-export-results">
                            @include('partials.builder-export-results')
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
            const suburbsByStateUrl = "{{ route('autocomplete.fetchSuburbsByState') }}";

            function uniqueSuburbsByName(results) {
                const seen = {};
                const unique = [];
                (results || []).forEach(function (row) {
                    const name = row.name || row.text;
                    if (!name || seen[name]) {
                        return;
                    }
                    seen[name] = true;
                    unique.push({ id: name, text: name });
                });
                return unique;
            }

            function initFilterSuburbSelect($el, placeholder, disabled, results) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.empty().append(new Option('', '', false, false));
                $el.select2({
                    allowClear: true,
                    placeholder: placeholder,
                    data: results || [],
                });
                $el.prop('disabled', !!disabled);
            }

            function loadFilterSuburbs($el, stateId, selectedSuburb) {
                if (!stateId) {
                    initFilterSuburbSelect($el, 'Select state first', true, []);
                    $el.val(null).trigger('change');
                    return;
                }

                initFilterSuburbSelect($el, 'Loading suburbs…', true, []);

                $.ajax({
                    url: suburbsByStateUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: { state_id: stateId },
                }).done(function (data) {
                    const results = uniqueSuburbsByName(data.results);
                    initFilterSuburbSelect($el, '-- All Suburbs --', false, results);
                    if (selectedSuburb) {
                        $el.val(selectedSuburb).trigger('change');
                    } else {
                        $el.val(null).trigger('change');
                    }
                }).fail(function () {
                    initFilterSuburbSelect($el, '-- All Suburbs --', false, []);
                });
            }

            initFilterSuburbSelect($('#rating_suburb'), 'Select state first', true, []);
            initFilterSuburbSelect($('#property_suburb'), 'Select state first', true, []);

            $('#rating_state').on('change', function () {
                loadFilterSuburbs($('#rating_suburb'), $(this).val());
            });

            $('#property_state').on('change', function () {
                const stateId = $(this).find('option:selected').data('id') || '';
                loadFilterSuburbs($('#property_suburb'), stateId);
            });

            const propertyStateId = $('#property_state option:selected').data('id') || '';
            const propertySuburb = params.get('suburb');
            if (propertyStateId) {
                loadFilterSuburbs($('#property_suburb'), propertyStateId, propertySuburb);
            }

            $("#project_id").select2();
            $("#project_id_export").select2();

            function loadBuilderPartial(url, targetSelector) {
                var $target = $(targetSelector);
                $target.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (html) {
                        $target.html(html);
                        if (window.history && window.history.pushState) {
                            var cleanUrl = url
                                .replace(/([?&])ajax_target=[^&]*/g, '$1')
                                .replace(/[?&]$/, '')
                                .replace(/\?&/, '?');
                            window.history.pushState({builderAjax: true}, '', cleanUrl);
                        }
                    },
                    complete: function () {
                        $target.css('opacity', '1');
                    }
                });
            }

            $('#builder-property-filter-form').on('submit', function (event) {
                event.preventDefault();
                var action = $(this).attr('action');
                var query = $(this).serialize() + '&ajax_target=property_reviews';
                var url = action + (action.indexOf('?') === -1 ? '?' : '&') + query;
                loadBuilderPartial(url, '#builder-property-reviews-results');
            });

            $('#builder-export-filter-form').on('submit', function (event) {
                event.preventDefault();
                var action = $(this).attr('action');
                var query = $(this).serialize() + '&ajax_target=export';
                var url = action + (action.indexOf('?') === -1 ? '?' : '&') + query;
                loadBuilderPartial(url, '#builder-export-results');
            });

            $('#rating-form').submit(function (event) {
                event.preventDefault();
                const property = $('#project_id').val();
                const suburb = $('#rating_suburb').val();
                const state = $('#rating_state').val();
                const category = $('#rating_category').val();
                const defaultBarColor = '#0694a2';
                $('#rating-loader').show();
                $.ajax({
                    url: "{{route('amenities-by-project')}}",
                    type: 'POST',
                    data: JSON.stringify({property, suburb, state, category}),
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'JSON',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    complete: function () {
                        $('#rating-loader').hide();
                    },
                    success: function (res) {
                        if (res['data'] !== undefined) {
                            const data = res['data'];
                            let chartHtml = '';
                            Object.keys(reviewQuestions).forEach(question => {
                                const barColor = (reviewQuestions[question] && reviewQuestions[question]['color_code'])
                                    ? reviewQuestions[question]['color_code']
                                    : defaultBarColor;
                                const width = data[question] ? data[question] : 0;
                                chartHtml += '<div class="amenities-graph-single">' +
                                    '<div class="graph-title">' + question + '</div>' +
                                    '<div class="progressbar-wrap">' +
                                    '<div style="width: ' + width + '%; float: left; background-color: ' + barColor + ';"></div>' +
                                    '</div></div>';
                            });

                            $("#results-section").html(chartHtml);
                        }

                        if (res['review_count'] !== undefined) {
                            $("#amenity-review-count").text(res['review_count']);
                        }
                    }
                });
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

