@extends('layouts.app-layout')

@section('meta_title', 'Write a Review | ' . seoSiteName())
@section('meta_robots', 'noindex, nofollow')

@section('extra-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet"/>
    <style>
        .w-100 {
            width: 100%;
        }
        #post_code[readonly] {
            background-color: #f3f3f3;
            cursor: default;
        }
        .basic-property-form .select2-container {
            width: 100% !important;
            margin-top: 7px;
        }
        .basic-property-form .select2-container--default .select2-selection--single {
            height: 46px !important;
            padding: 8px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px !important;
            display: flex;
            align-items: center;
        }
        .basic-property-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            line-height: 28px;
            color: #14171F;
        }
        .basic-property-form .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #999;
        }
        .basic-property-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            top: 1px !important;
            right: 8px;
        }
        .basic-property-form .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 20px;
        }
        .basic-property-form .review-form-section .form-row .form-field input#post_code {
            margin-top: 7px;
            height: 46px;
            padding: 8px 15px;
            box-sizing: border-box;
        }
    </style>
@endsection
@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/about-banner.jpg')}});">
        <div class="banner-caption">
            <h2>Write your Review</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Write your review</span>
            </div>
        </div>
    </div>

    <div class="review-steps-section">
        <div class="container">
            <div class="review-steps-box">
                <div class="steps-wrap">
                    <ul>
                        <li class="single-step {{Request::is('review-step-1')?'active':''}}"><a
                                href="{{route('review-step-1')}}"><h5>Step 1<span>Basic Details</span>
                                </h5></a></li>
                        <li class="single-step {{Request::is('review-step-2')?'active':''}}"><h5>Step
                                2<span>Your Review </span>
                            </h5></li>
                        <li class="single-step {{Request::is('review-step-3')?'active':''}}"><h5>Step
                                3<span>Submit Review</span></h5></li>
                    </ul>
                </div>
                <div class="cat-review-section">
                    <div class="container">
                        <div class="section-title">
                            <h2>Write your review by Categories</h2>
                        </div>
                        <form method="post" action="{{route('review-save-one')}}">
                            @csrf
                            {{--                        <form class="section-content" method="GET" action="{{route('home-cat-review')}}" id="formId">--}}
{{--                            {{request()->keys() && request()->keys()[0] ? request()->keys()[0] : ''}}--}}
<div class="review-step-wrap">
    <div class="review-step-scroll">
                            @foreach($property_category as $category)
                                <div
                                    class="single-box {{(old('_token') && $category['id']==old('categories')) || (request()->session()->get('review-category')== $category['id']) || (request()->keys() && request()->keys()[0]===$category['slug']) ? 'active' : '' }}">
                                    <input type="radio" name="categories"
                                           class="radiobox_{{$category['id']}} ads_Checkbox"
                                           data-placement="{{$category['question']}}"
                                           value="{{$category['id']}}"
                                        {{(old('_token') && $category['id']==old('categories')) || (request()->session()->get('review-category')==$category['id']) || (request()->keys() && request()->keys()[0]===$category['slug'])? 'checked' : '' }}>
                                    <div class="cat-content-wrap">
                                        <div class="icon-wrap">
                                            <img
                                                src="{{$category['full_cat_image'] ? $category['full_cat_image'] : asset('images/cat-icon1.png')}}">
                                        </div>
                                        <h4>{{$category['name']}}</h4>
                                        <div class="content-wrap">

                                            <p>{{$category['description'] ? $category['description'] :  'There are many variations of passages of Lorem Ipsum available'}}</p>
                                            <a>
                                                <img src="images/grey-arrow.png">
                                                <img class="hover-img" src="images/white-arrow.png">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                            </div>
                            <div>
                                <span class="text-danger">{{ $errors->first('categories') }}</span>
                            </div>
                            {{--                        </form>--}}
                            <div class="basic-property-detail">
                                {{--                        <form>--}}
                                <div class="basic-property-form">
                                    <div class="form-row">
                                        <label id="company_label">Who was the project company name?</label>
                                        <select class="w-100" id="company_id" name="company_id"
                                                data-placement="{{old('_token') ? old('company_id') : session()->get('review-company-id')}}">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <label>What is the project name?</label>
                                        <select class="w-100" id="project_id" name="project_id"
                                                data-placement="{{old('_token') ? old('project_id') : session()->get('review-property-id')}}">
                                            <option value=""></option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('project_id') }}</span>
                                    </div>
                                    <div class="form-row">
                                        <label>Year of completion? (optional)</label>
                                        <input type="text" name="year_of_completion" maxlength="4"
                                               placeholder="Enter Year of Completion"
                                               value="{{old('_token') ? old('year_of_completion') : session()->get('review-year-complete')}}">
                                        <span class="text-danger">{{ $errors->first('year_of_completion') }}</span>
                                    </div>
                                    <div class="form-row radio-container custom-radio-style">
                                        <label>Buyer Type</label>
                                        @foreach($buyertype['option_select'] as $buyerOption)
                                            <div class="form-field">
                                                <input type="radio" name="buyer_type" value="{{$buyerOption['id']}}"
                                                    {{ old('buyer_type') == $buyerOption['id'] || session()->get('buyer-type') == $buyerOption['id'] ? 'checked' : ''}}>
                                                <span>{{$buyerOption['value_text']}}</span>
                                            </div>
                                        @endforeach

                                    </div>

                                    <span class="text-danger">{{ $errors->first('buyer_type') }}</span>


                                    <div class="review-form-section review-pd-0">
                                        <div class="form-wrap">
                                            <div class="form-row">
                                                <label>Where was the land development?</label>
                                                <div class="form-field">
                                                    <label>State</label>
                                                    <select class="w-100" id="state_id"
                                                            value="{{old('state')}}"
                                                            data-placement="{{old('_token') ? old('state') : session()->get('review-state')}}"></select>
                                                    <input type="hidden" name="state" id="state_hidden"
                                                           value="{{old('_token') ? old('state') : session()->get('review-state')}}">
                                                    <span class="text-danger">{{ $errors->first('state') }}</span>
                                                </div>
                                                <div class="form-field">
                                                    <label>Suburb</label>
                                                    <select class="w-100" id="suburb"
                                                            data-placement="{{old('_token') ? old('suburb') : session()->get('review-suburb')}}"></select>
                                                    <input type="hidden" name="suburb" id="suburb_name"
                                                           value="{{old('_token') ? old('suburb') : session()->get('review-suburb')}}">
                                                    <span class="text-danger">{{ $errors->first('suburb') }}</span>
                                                </div>
                                                <div class="form-field">
                                                    <label>Postcode</label>
                                                    <input type="text" name="post_code" placeholder="Postcode"
                                                           id="post_code" readonly
                                                           value="{{old('_token') ? old('post_code') : request()->session()->get('review-post-code')}}">
                                                    <input type="hidden" name="locality_id" id="locality_id"
                                                           value="{{old('_token') ? old('locality_id') : session()->get('review-locality-id')}}">
                                                    <span class="text-danger">{{ $errors->first('post_code') }}</span>
                                                    <span class="text-danger">{{ $errors->first('locality_id') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <input type="submit" name="" value="Next">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script src="{{ asset('js/locality-select.js') }}"></script>
    <script>
        $(document).ready(function () {
            var uri = window.location.toString();
            if (uri.indexOf("?") > 0) {
                var clean_uri = uri.substring(0, uri.indexOf("?"));
                window.history.replaceState({}, document.title, clean_uri);
            }
        })

        {{--        @php--}}
        {{--            $cat_id = request()->session()->get('category_id');--}}
        {{--            request()->session()->forget('category_id');--}}
        {{--        @endphp--}}
        function deleteCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }

        deleteCookie('review');
        var APP_URL =
            {!! json_encode(url('/')) !!}
        const catId = {{ $cat_id ?? 0}};
        if (catId != 0) {
            $('.radiobox_' + catId).parent().addClass("active");
            $('.radiobox_' + catId).prop("checked", true);
        }

        function savedSelectId($el) {
            const value = $el.attr('data-placement');
            return value ? String(value).trim() : '';
        }

        function selectedCategoryId() {
            const selected = $("input:radio[name='categories']:checked");
            return selected.length > 0 ? selected.val() : '';
        }

        function initCompanySelect(results) {
            const $company = $('#company_id');
            if ($company.hasClass('select2-hidden-accessible')) {
                $company.select2('destroy');
            }
            $company.empty().append(new Option('', '', false, false));
            $company.select2({
                allowClear: true,
                placeholder: 'Select company',
                width: '100%',
                dropdownParent: $(document.body),
                language: {
                    noResults: function () {
                        return "No Results Found <a onclick='addCompany()'>(add)</a>";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                data: results || []
            });
        }

        function initProjectSelect() {
            const $project = $('#project_id');
            if ($project.hasClass('select2-hidden-accessible')) {
                $project.select2('destroy');
            }
            $project.empty().append(new Option('', '', false, false));
            $project.select2({
                allowClear: true,
                placeholder: 'Select Project Name',
                width: '100%',
                dropdownParent: $(document.body),
                language: {
                    noResults: function () {
                        if ($('#company_id').val()) {
                            return "No Results Found <a onclick='addProperty(selectedCategoryId())'>(add)</a>";
                        }
                        return "No Results Found";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                ajax: {
                    delay: 250,
                    url: "{{ route('autocomplete.fetchProperty') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            company_id: $('#company_id').val(),
                            categories: selectedCategoryId(),
                            cookie_property: JSON.parse(getCookie('prop_cookie')),
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results,
                        };
                    }
                }
            });
        }

        function restoreProjectSelection(propertyId) {
            if (!propertyId) {
                return;
            }
            $.ajax({
                url: "{{ route('autocomplete.fetchProperty') }}",
                method: 'POST',
                dataType: 'json',
                data: {
                    q: '',
                    page: '',
                    company_id: $('#company_id').val(),
                    categories: selectedCategoryId(),
                    cookie_property: JSON.parse(getCookie('prop_cookie')),
                },
                success: function (res) {
                    const match = (res.results || []).find(function (row) {
                        return String(row.id) === String(propertyId);
                    });
                    if (!match) {
                        return;
                    }
                    const option = new Option(match.text, match.id, true, true);
                    $('#project_id').append(option);
                    const selectedData = $('#project_id').select2('data')[0];
                    if (selectedData) {
                        selectedData.state_id = match.state_id;
                        selectedData.suburb = match.suburb;
                        selectedData.post_code = match.post_code;
                        selectedData.state = match.state;
                    }
                    $('#project_id').trigger('change');
                }
            });
        }

        $('.ads_Checkbox').click(function () {
            $('input:not(:checked)').parent().removeClass("active");
            $('input:checked').parent().addClass("active");
            if ($('input:checked').data('placement') != '') {
                $('#company_label').text($('input:checked').data('placement'));
            }
            $('#company_id').val(null).trigger('change');
        });

        $('#company_id').change(function () {
            $('#project_id').val(null).trigger('change');
            initProjectSelect();
        });

        $('#project_id').on('change', function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                return;
            }
            const selected = $(this).select2('data')[0] || {};
            applyLocationFromProperty(selected);
        });

        initCompanySelect([]);
        initProjectSelect();

        $.ajax({
            delay: 250,
            method: "POST",
            url: "{{ route('autocomplete.fetchCompany') }}",
            dataType: 'json',
            data: {
                q: '',
                page: null,
                cookie_company: JSON.parse(getCookie('company_cookie')),
            },
        }).done(function (data) {
            initCompanySelect(data.results);

            const selectedCompanyId = savedSelectId($('#company_id'));
            if (selectedCompanyId) {
                $('#company_id').val(selectedCompanyId).trigger('change');
                restoreProjectSelection(savedSelectId($('#project_id')));
            } else {
                $('#company_id').val(null).trigger('change');
            }
        });

        function addCompany() {
            const newCompany = $("#company_id").data('select2').$dropdown.find("input").val();
            if (newCompany) {
                Swal.fire({
                    title: 'Are you sure ?',
                    text: "Create new company " + newCompany,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'SAVE',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                type: "POST",
                                url: APP_URL + '/review/save-company',
                                data: {company_name: newCompany},
                                success: (res) => {
                                    if (res.status && res.status === 'success') {
                                        resolve(res);
                                    }
                                }, error: function (error) {
                                    let msg = 'A company with this name already exists. Please select it from the list instead of creating a duplicate.';
                                    try {
                                        const errorObj = JSON.parse(error.responseText);
                                        if (errorObj['errors'] && errorObj['errors']['company_name']) {
                                            msg = errorObj['errors']['company_name'][0];
                                        } else if (errorObj['message']) {
                                            msg = errorObj['message'];
                                        }
                                    } catch (e) { /* keep default */ }
                                    reject(msg);
                                }
                            })
                        })
                            .then(res => res)
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                )
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((value) => {
                    if (value['value']) {
                        const res = value['value'];
                        var newOption = new Option(res.text, res.id, false, true);
                        setCookie('company_cookie', JSON.stringify({id: res.id, text: res.text}), 1);
                        $('#company_id').append(newOption).trigger('change');
                    }
                });
            }
        }

        function addProperty(cat_id) {
            const newProperty = $("#project_id").data('select2').$dropdown.find("input").val();
            if (newProperty) {

                Swal.fire({
                    title: 'Are you sure ?',
                    text: "Create new project " + newProperty,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'SAVE',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                type: "POST",
                                url: APP_URL + '/review/save-property',
                                data: {
                                    project_name: newProperty,
                                    company_id: $('#company_id').val()
                                },
                                success: (res) => {
                                    if (res.status && res.status === 'success') {
                                        resolve(res);
                                    }
                                }, error: function (error) {
                                    reject(error.statusText);
                                }
                            })
                        })
                            .then(res => res)
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error}`
                                )
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((value) => {
                    if (value['value']) {
                        const res = value['value'];
                        var newOption = new Option(res.text, res.id, false, true);
                        setCookie('prop_cookie', JSON.stringify({id: res.id, text: res.text}), 1);
                        $('#project_id').append(newOption).trigger('change');
                    }
                });
            }
        }

        function setCookie(name, value, hours) {
            var expires = "";
            if (hours) {
                var date = new Date();
                date.setTime(date.getTime() + Number(hours) * 3600 * 1000);
                expires = "; expires=" + date.toGMTString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }


        // State / Suburb / Postcode is handled by the shared locality component,
        // which resolves the selection to a locality_id from the reference table.
        // `var` rather than `const`: applyLocationFromProperty is hoisted and the
        // project change handler can fire before this line runs.
        var localitySelect = RmeLocalitySelect.init({
            apiBase: APP_URL + '/api',
            stateEl: '#state_id',
            stateValueEl: '#state_hidden',
            suburbEl: '#suburb',
            suburbValueEl: '#suburb_name',
            postcodeEl: '#post_code',
            localityIdEl: '#locality_id',
            initial: {
                stateId: savedSelectId($('#state_id')),
                suburb: $("#suburb").data('placement') ? String($("#suburb").data('placement')) : '',
                postcode: $("#post_code").val() || ''
            }
        });

        // Picking a project fixes the location, so the fields mirror it and lock.
        function applyLocationFromProperty(data) {
            if (!localitySelect) {
                return;
            }
            localitySelect.applyFromProperty(data || {});
        }
    </script>
    <script>
        $('.single-box input[type="radio"]').click(function () {
            $('input:not(:checked)').parent().removeClass("active");
            $('input:checked').parent().addClass("active");
        });
    </script>
@endsection
