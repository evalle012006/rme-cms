@extends('layouts.app-layout')

@section('extra-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .w-100 {
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <div class="bg-main inner-banner" style="background-image: url(images/about-banner.jpg);">
        <div class="banner-caption">
            <h2>Write a Review</h2>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Write a review</span>
            </div>
        </div>
    </div>
    <div class="review-steps-section">
        <div class="container">
            <div class="review-steps-box">
                <div class="steps-wrap">
                    <ul>
                        <li class="single-step {{Request::is('review-step-1')?'active':''}}"><a
                                href="{{route('nav-review-step1')}}"><h5>Step 1<span>Basic Details</span>
                                </h5></a></li>
                        <li class="single-step {{Request::is('review-step-2')?'active':''}}"><a
                                href="{{route('review-step-2')}}"><h5>Step 2<span>Your Review </span>
                                </h5></a></li>
                        <li class="single-step {{Request::is('review-step-3')?'active':''}}"><a
                                href="{{route('review-step-3')}}"><h5>Step
                                    3<span>Submit Review</span></h5></a></li>
                    </ul>
                </div>
                <div class="review-form-section">

                    <div class="form-wrap">
                        <form method="post" action="{{route('review-save-two-new')}}" enctype=multipart/form-data>
                            @csrf

                            <div class="form-row">
                                <label>Who was the property company name?</label>
                                <select class="w-100" id="company_id"></select>
                            </div>
                            <div class="form-row">
                                <label>What was the Project/Estate Name?</label>
                                <select class="w-100" id="project_id" name="property_id"></select>
                                <span class="text-danger">{{ $errors->first('property_id') }}</span>
                            </div>
                            <div class="form-row">
                                <label>Year of completion? (optional)</label>
                                <input type="text" name="year_of_completion" maxlength="4" placeholder="Enter Year of Completion" value="{{old('year_of_completion')}}">
                            </div>
                            <div class="form-row radio-container custom-radio-style">
                                <label>Buyer Type</label>
                                @foreach($buyertype['option_select'] as $buyerOption)
                                    <div class="form-field">
                                        <input type="radio" name="buyer_type" value="{{$buyerOption['id']}}"
                                            {{ old('buyer_type') == $buyerOption['id'] ? 'checked' : ''}}>
                                        <span>{{$buyerOption['value_text']}}</span>
                                    </div>
                                @endforeach

                            </div>
                            <span class="text-danger">{{ $errors->first('buyer_type') }}</span>
                            <div class="form-row">
                                <label>Where was the land development?</label>
                                <div class="form-field">
                                    <label>State</label>
                                    <select class="w-100" id="state_id" name="state"></select>
                                    <span class="text-danger">{{ $errors->first('state') }}</span>
                                </div>
                                <div class="form-field">
                                    <label>Postcode</label>
                                    <input type="text" name="post_code" placeholder="Type Postcode" value="{{old('post_code')}}">
                                    <span class="text-danger">{{ $errors->first('post_code') }}</span>
                                </div>
                                <div class="form-field">
                                    <label>Suburb</label>
                                    <input type="text" name="suburb" placeholder="Suburb" value="{{old('suburb')}}">
                                    <span class="text-danger">{{ $errors->first('suburb') }}</span>
                                </div>
                            </div>



                            <div class="form-row">
                                <label>Title of Review</label>
                                <input type="text" name="title" placeholder="Please Enter the title" maxlength="150"
                                       value="{{old('title')}}">
                                <span class="text-danger">{{ $errors->first('title') }}</span>
                            </div>
                            <div class="form-row rate-exp-wrap">
                                <label>Rate Your Experience</label>

                                <ul>
                                    @foreach($propertyRatings['property_allowed_rating'] as $ratings)
                                        <li>
                                            <div class="icon-wrap"><img
                                                    src="{{$ratings['full_ratingconf_icon'] ? $ratings['full_ratingconf_icon'] : asset('images/loc-icon.png')}}">
                                            </div>
                                            <div class="loc-detail">
                                                <h6>{{$ratings['name']}}</h6>
                                                <span>{{$ratings['description'] ? $ratings['description'] : 'How would you rate the location from work and the daily needs?' }}</span>
                                            </div>
                                            <div class="review-col">
                                                <ul>
                                                    <li>
                                                        <input class="rating-radio"
                                                               id="rating_{{$ratings['id']}}_1"
                                                               type="radio"
                                                               name="rating[{{$ratings['id']}}]"
                                                               data-rating-id="{{$ratings['id']}}" value="1">
                                                        <label for="rating1">1</label>
                                                    </li>
                                                    <li>
                                                        <input class="rating-radio"
                                                               id="rating_{{$ratings['id']}}_2"
                                                               type="radio"
                                                               name="rating[{{$ratings['id']}}]"
                                                               data-rating-id="{{$ratings['id']}}" value="2">
                                                        <label for="rating2">2</label>
                                                    </li>
                                                    <li>
                                                        <input class="rating-radio"
                                                               id="rating_{{$ratings['id']}}_3"
                                                               type="radio"
                                                               name="rating[{{$ratings['id']}}]"
                                                               data-rating-id="{{$ratings['id']}}" value="3">
                                                        <label for="rating3">3</label>
                                                    </li>
                                                    <li>
                                                        <input class="rating-radio"
                                                               id="rating_{{$ratings['id']}}_4"
                                                               type="radio"
                                                               name="rating[{{$ratings['id']}}]"
                                                               data-rating-id="{{$ratings['id']}}" value="4">
                                                        <label for="rating4">4</label>
                                                    </li>
                                                    <li>
                                                        <input class="rating-radio"
                                                               id="rating_{{$ratings['id']}}_5"
                                                               type="radio"
                                                               name="rating[{{$ratings['id']}}]"
                                                               data-rating-id="{{$ratings['id']}}" value="5">
                                                        <label for="rating5">5</label>
                                                    </li>

                                                    {{--                                                                                                        <li class="active"><a><img src="{{asset('images/rating-img.png')}}"></a>--}}
                                                    {{--                                                                                                        </li>--}}
                                                    {{--                                                                                                        <li class="active"><a><img src="{{asset('images/rating-img.png')}}"></a>--}}
                                                    {{--                                                                                                        </li>--}}
                                                    {{--                                                                                                        <li class="active"><a><img src="{{asset('images/rating-img.png')}}"></a>--}}
                                                    {{--                                                                                                        </li>--}}
                                                    {{--                                                                                                        <li class="active"><a><img src="{{asset('images/rating-img.png')}}"></a>--}}
                                                    {{--                                                                                                        </li>--}}
                                                    {{--                                                                                                        <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>--}}
                                                </ul>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="overall-rating">
                                    Overall Ratings
                                    <div class="review-col">
                                        <ul>
                                            <li>
                                                <input class="rating-overall-radio"
                                                       id="rating_overall_1"
                                                       type="radio"
                                                       name="rating_overall_1"
                                                       data-rating-overall-id="1" value="1">
                                                <label for="rating1">1</label>
                                            </li>
                                            <li>
                                                <input class="rating-overall-radio"
                                                       id="rating_overall_2"
                                                       type="radio"
                                                       name="rating_overall_2"
                                                       data-rating-overall-id="2" value="2">
                                                <label for="rating2">2</label>
                                            </li>
                                            <li>
                                                <input class="rating-overall-radio"
                                                       id="rating_overall_3"
                                                       type="radio"
                                                       name="rating_overall_3"
                                                       data-rating-overall-id="3" value="3">
                                                <label for="rating3">3</label>
                                            </li>
                                            <li>
                                                <input class="rating-overall-radio"
                                                       id="rating_overall_4"
                                                       type="radio"
                                                       name="rating_overall_4"
                                                       data-rating-overall-id="4" value="4">
                                                <label for="rating4">4</label>
                                            </li>
                                            <li>
                                                <input class="rating-overall-radio"
                                                       id="rating_overall_5"
                                                       type="radio"
                                                       name="rating_overall_5"
                                                       data-rating-overall-id="5" value="5">
                                                <label for="rating5">5</label>
                                            </li>


                                            {{--                                                                                        <li class="active yellow"><a><img--}}
                                            {{--                                                                                                    src="{{asset('images/rating-img.png')}}"></a>--}}
                                            {{--                                                                                        </li>--}}
                                            {{--                                                                                        <li class="active yellow"><a><img--}}
                                            {{--                                                                                                    src="{{asset('images/rating-img.png')}}"></a>--}}
                                            {{--                                                                                        </li>--}}
                                            {{--                                                                                        <li class="active yellow"><a><img--}}
                                            {{--                                                                                                    src="{{asset('images/rating-img.png')}}"></a>--}}
                                            {{--                                                                                        </li>--}}
                                            {{--                                                                                        <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>--}}
                                            {{--                                                                                        <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>--}}
                                        </ul>
                                    </div>
                                </div>
                                @if($errors->first('rating'))
                                    <span class="text-danger">rate atleast one question</span>
                                @endif
                            </div>
                            <div class="recom-row radio-container form-row">
                                <label>Would you recommend this company?</label>
                                <div class="form-field">
                                    <input type="radio" name="recommendation" value="1" class="btn" checked
                                        {{--                                           @if(old('recommendation') ?? request()->session()->get('review-recommendation')) checked @endif--}}
                                    >
                                    <span>Yes</span>
                                </div>
                                <div class="form-field">
                                    <input type="radio" name="recommendation" value="0" class="btn yellow-btn"
                                        {{--                                           @if(!old('recommendation') ?? !request()->session()->get('review-recommendation')) checked @endif--}}
                                    >
                                    <span>No</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('recommendation') }}</span>
                            </div>

                            <div class="form-row">
                                <label>Comment Tags</label>
                                <div class="selected-tags">

                                </div>
                                <select class="w-100" id="tags" name="tags[]" multiple="multiple"></select>
                                <span class="text-danger">{{ $errors->first('tags') }}</span>
                            </div>
                            <div class="form-row">
                                <label>Comment</label>
                                <textarea placeholder="Type your Commnet" name="comment"
                                          maxlength="300">{{ old('comment') }}</textarea>
                            </div>
                            <div class="form-row">
                                <label>Upload your Proof of Purchase</label>
                                <input type="file" name="review_docs" accept=".doc , .docx, .pdf , .jpg">
                                <span class="text-danger">{{ $errors->first('review_docs') }}</span>
                            </div>
                            <div class="form-row">
                                <div class="user-img-wrap">
                                    <img id="user-profile-image">
                                    {{--                                    <img id="user-profile-image"--}}
                                    {{--                                         width="200px"--}}
                                    {{--                                         height="160px"--}}
                                    {{--                                         src="Error.src"--}}
                                    {{--                                         alt="file upload"/>--}}
                                </div>
                                <div class="upload-photo">
                                    <a href="javascript:;" class="btn yellow-btn">Upload Photo
                                        <input type='file'
                                               name="review_image"
                                               class="btn yellow-btn"
                                               onchange="readURL(this);"/></a>
                                </div>
                            </div>
                            <div class="form-row privacy-policy-wrap">
                                <label>
                                  <input type="checkbox" name="is_agree" @if(old('is_agree')) checked @endif>
                                <span>I agree to the ratemyestate <a href="{{route('terms-of-use')}}">Terms and Conditions</a> and <a
                                        href="{{route('privacy-policy')}}">Privacy Policy</a></span>
                                </label>

                            </div>
                            <div>
                                <span
                                    class="text-danger">{{ $errors->first('is_agree') ? 'You must agree term and condition' : '' }}</span>
                            </div>
                            <div class="form-row form-footer">
{{--                                <a href="{{route('nav-review-step1')}}" class="btn">Go Back</a>--}}
                                {{--                                <button class="btn">Preview</button>--}}
                                <input type="submit" name="submit" value="submit" class="btn">

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
    <script>
            @if(session('error'))
        let msg = '<?= session('error') ?>'
        Swal.fire({
            title: '<strong>Login Required</strong>',
            html:
                '<a href="{{route('login')}}">LOGIN</a> ',
            showCloseButton: true,
            showConfirmButton: false
        })

        @endif
    </script>
    <script>
            @php
                $prObj = collect($propertyRatings['property_allowed_rating'])->map(function ($pr){
                return ['id'=>$pr['id'],'value'=>0];
            });
            @endphp

        const prObj = {!! $prObj->toJson() !!};

        $('#tags').select2({
            // allowClear: true,
            maximumSelectionLength: 5,
            placeholder: {
                id: "",
                placeholder: "Leave blank to ..."
            },
            ajax: {
                delay: 250,
                url: "{{ route('autocomplete.fetchTags') }}",
                method: 'POST',
                dataType: 'json',
                processResults: function (data, params) {
                    return {
                        results: data.results,
                    };
                }
            }
        });
        $('#state_id').select2({
            allowClear: true,
            placeholder: {
                name: "",
                placeholder: "Leave blank to ..."
            },
            ajax: {
                delay: 250,
                url: "{{ route('autocomplete.fetchStates') }}",
                method: 'POST',
                dataType: 'json',
                processResults: function (data, params) {
                    return {
                        results: data.results,
                    };
                }
            }
        });
        $('.rating-radio').on('change', function () {
            const ratingId = $(this).data('rating-id');
            const val = $(this).val();

            $(this).parent().parent().find('li').removeClass('color_1 color_2 color_3 color_4 color_5');
            $('.review-col').children().find('li').removeClass('color_overall_1 color_overall_2 color_overall_3 color_overall_4 color_overall_5')
            $('.overall-rating').find('ul').removeClass();
            let total = 0;
            let totalItems = 0;

            for (const index in prObj) {
                if (prObj[index].id === +ratingId) {
                    prObj[index].value = +val;
                }

                total += prObj[index].value;

                if (prObj[index].value > 0) {
                    totalItems++;
                }
            }

            const average = total / totalItems;
            let ceilAvg = Math.ceil(average);

            jQuery('.overall-rating').find('ul').addClass('color_overall_' + ceilAvg);

            while (ceilAvg > 0) {
                $('#rating_overall_' + ceilAvg).parent().addClass('color_overall_' + ceilAvg);
                ceilAvg--;
            }
            let iterator = +val;


            while (iterator > 0) {
                $('#rating_' + ratingId + '_' + iterator).parent().addClass('color_' + val);
                iterator--;
            }
        });


            $('#company_id').select2({
                allowClear: true,
                placeholder: {
                    id: "",
                    placeholder: "Leave blank to ..."
                },
                "language": {
                    "noResults": function(){
                            return "No Results Found <a onclick='addCompany()'>(add)</a>";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                ajax: {
                    delay: 250,
                    url: "{{ route('autocomplete.fetchCompany') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: function (params) {

                        return {

                            q: params.term, // search term
                            page: params.page,
                            cookie_company:  JSON.parse(getCookie('company_cookie')),
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results,
                        };
                    }
                }
            });
            $('#project_id').select2({
                allowClear: true,
                placeholder: {
                    id: "",
                    placeholder: "Leave blank to ..."
                },
                "language": {
                    "noResults": function(){
                        var selected = $("input:radio[name='categories']:checked");
                        selectedValue = '';
                        if (selected.length > 0) {
                            selectedValue = selected.val();
                        }
                        if($('#company_id').val() != null) {
                            return "No Results Found <a onclick='addProperty(selectedValue)'>(add)</a>";
                        }else {
                            return "No Results Found";
                        }
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
                        // var arr = [];
                        // var i = 0;
                        // $('.ads_Checkbox:checked').each(function () {
                        //     arr[i++] = $(this).val();
                        // });
                        var selected = $("input:radio[name='categories']:checked");
                        selectedValue = '';
                        if (selected.length > 0) {
                            selectedValue = selected.val();
                        }
                        return {

                            q: params.term, // search term
                            page: params.page,
                            company_id: $('#company_id').val(),
                            categories: selectedValue,
                            cookie_property:  JSON.parse(getCookie('prop_cookie')),
                        };
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results,
                        };
                    }
                }
            });

            function addCompany() {
                Swal.fire({
//            title: 'Sign Up',
                    text: "Enter Name To Add Property Developer",
                    input: 'text',
                    animation: false,
                    inputPlaceholder: 'Enter Property Developer',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'SAVE'
                }).then((value) => {
                    if (value['value'] == "") {

                    }
                    if (value['value']) {
                        var APP_URL = {!! json_encode(url('/')) !!}
                        $.ajax({
                            type: "POST",
                            url: APP_URL + '/review/save-company',
                            data: {company_name: value['value']},
                            success: (res) => {
                                if (res.status && res.status === 'success') {
                                    Swal.fire('Success', 'Successfully', 'success');
                                    var newOption = new Option(res.text, res.id, false, true);
                                    setCookie('company_cookie', JSON.stringify({id: res.id, text: res.text}), 1);
                                    $('#company_id').append(newOption).trigger('change');
                                }
                            }, error: function (error) {
                                Swal.fire('Error', error.responseJSON.message, 'error')
                            }
                        })
                    }
                });

            }

            function addProperty(cat_id) {
                Swal.fire({
//            title: 'Sign Up',
                    text: "Enter Name To Add Project/Estate",
                    input: 'text',
                    animation: false,
                    inputPlaceholder: 'Enter Project/Estate Name',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'SAVE'
                }).then((value) => {
                    if (value['value'] == "") {

                    }

                    if (value['value']) {

                        var APP_URL = {!! json_encode(url('/')) !!}
                        $.ajax({
                            type: "POST",
                            url: APP_URL + '/review/save-property',
                            data: {
                                project_name: value['value'],
                                company_id: $('#company_id').val()
                            },
                            success: (res) => {
                                if (res.status && res.status === 'success') {
                                    Swal.fire('Success', 'Successfully', 'success');
                                    var newOption = new Option(res.text, res.id, false, true);

                                    setCookie('prop_cookie', JSON.stringify({id: res.id, text: res.text}), 1);
                                    $('#project_id').append(newOption).trigger('change');
                                }
                            }, error: function (error) {
                                Swal.fire('Error', error.responseJSON.message, 'error')
                            }
                        })
                    }
                });

            }
            function setCookie(name,value,hours) {
                var expires = "";
                if (hours) {
                    var date = new Date();
                    date.setTime(date.getTime() + Number(hours) * 3600 * 1000);
                    expires = "; expires=" + date.toGMTString();
                }
                document.cookie = name + "=" + (value || "")  + expires + "; path=/";
            }
            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                }
                return null;
            }
    </script>

@endsection
