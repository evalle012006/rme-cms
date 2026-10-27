@extends('layouts.app-layout')

@section('meta_title', 'Write a Review | ' . seoSiteName())
@section('meta_robots', 'noindex, nofollow')

@section('extra-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .w-100 {
            width: 100%;
        }

        .image-preview {
            height: 150px;
            width: 200px
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
                        <form method="post" action="{{route('review-save-two')}}" enctype="multipart/form-data" id="review-step-two-form">
                            @csrf
                            <input type="hidden" name="property_id"
                                   value="{{ request()->session()->get('review-property-id') }}">
                            <div class="form-row">
                                <label>Title of Review</label>
                                <input type="text" name="title" placeholder="Please Enter the title" maxlength="150"
                                       value="{{ old('title', request()->session()->get('review-title')) }}">
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
                                                    @foreach([1,2,3,4,5] as $number)
                                                        {{--                                                        <li class="{{isset(old('rating')[$ratings['id']]) && (int)old('rating')[$ratings['id']]>=$number ? 'color_'.old('rating')[$ratings['id']] : ''}}">--}}
                                                        <li class="@if(isset(old('rating')[$ratings['id']]) && (int)old('rating')[$ratings['id']]>=$number)
                                                            color_{{old('rating')[$ratings['id']]}}
                                                        @elseif(isset(session()->get('review-rating')[$ratings['id']]) && (int)session()->get('review-rating')[$ratings['id']]>=$number)
                                                            color_{{session()->get('review-rating')[$ratings['id']]}}
                                                        @else
                                                        @endif">
                                                            <input class="rating-radio"
                                                                   id="rating_{{$ratings['id']}}_{{$number}}"
                                                                   type="radio"
                                                                   name="rating[{{$ratings['id']}}]"
                                                                   data-rating-id="{{$ratings['id']}}"
                                                                   @if((isset(old('rating')[$ratings['id']]) && (int)old('rating')[$ratings['id']]>=$number) || (isset(session()->get('review-rating')[$ratings['id']]) && (int)session()->get('review-rating')[$ratings['id']]>=$number))
                                                                   checked=checked
                                                                   @endif

                                                                   value="{{$number}}">
                                                            <label for="rating{{$number}}">{{$number}}</label>
                                                        </li>
                                                    @endforeach
                                                    {{--
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
                                <div class="overall-rating custom-overall-rating">
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
                                        </ul>
                                    </div>
                                </div>
                                @if($errors->first('rating'))
                                    <span class="text-danger">rate atleast one question</span>
                                @endif
                            </div>
                            <div class="recom-row radio-container form-row">
                                <label>Would you recommend this company?</label>
                                @php
                                    $recommendationValue = old('recommendation', session()->get('review-recommendation', '1'));
                                @endphp
                                <div class="form-field">
                                    <input type="radio" name="recommendation" value="1" class="btn"
                                           @if((string)$recommendationValue === '1') checked="checked" @endif
                                    >
                                    <span>Yes</span>
                                </div>
                                <div class="form-field">
                                    <input type="radio" name="recommendation" value="0" class="btn yellow-btn"
                                           @if((string)$recommendationValue === '0') checked="checked" @endif
                                    >
                                    <span>No</span>
                                </div>
                                <span class="text-danger">{{ $errors->first('recommendation') }}</span>
                            </div>

                            <div class="form-row">
                                <label>Comment Tags</label>
                                <p>please describe your project/ estate or developer with key words or phrases ie: "Good
                                    location" "Everything I needed" "Constant Delays"</p>
                                <div class="selected-tags">

                                </div>
                                <select class="w-100" id="tags" name="tags[]" multiple="multiple"></select>
                                <span class="text-danger">{{ $errors->first('tags') }}</span>
                            </div>
                            <div class="form-row">
                                <label>Comment</label>
                                <p>please comment on the reason for your ratings - what was good or what could be
                                    improved</p>
                                <textarea placeholder="Type your Commnet" name="comment"
                                          maxlength="300">{{ old('comment', session()->get('review-comment')) }}</textarea>
                            </div>
                            <div class="form-row">
                                <label>Proof of purchase <span class="text-danger">*</span></label>
                                <p>Please upload a document which confirms your purchase; this should be name of the purchaser/ reviewer, the property being purchased and the company to which you bought/ built. We ask for this in order to verify your purchase. eg. sales advice or this could be the first page contract. This is not being published.</p>
                                <img class="image-preview" id="session-doc"
                                     alt="document">
                                <p id="doc-name"></p>
                                <div class="upload-photo">
                                    <a href="javascript:;" class="btn yellow-btn">Upload Document
                                        <input type="file" class="btn yellow-btn" name="review_docs" id="review_docs"
                                               accept=".doc,.docx,.pdf,image/*" onchange="onUploadDoc(this)"
                                               @if(!session()->exists('review-docs')) required @endif>
                                    </a>
                                </div>
                                <span class="text-danger" id="review-docs-error">{{ $errors->first('review_docs') }}</span>
                            </div>
                            <div class="form-row">
                                <label>Upload photo</label>
                                <p>Upload pictures related to your purchase/ build. eg, this is what the estate looks like or this is the finished product</p>
                                <div class="user-img-wrap">
                                    @if(session()->exists('review-image'))
                                        <img id="session-image" class="image-preview"
                                             src="{{ session()->get('review-image') }}" alt="image">
                                    @endif
                                    <img id="user-profile-image" class="image-preview">
                                </div>
                                <div class="upload-photo">
                                    <a href="javascript:;" class="btn yellow-btn">Upload Photo
                                        <input type='file'
                                               name="review_image"
                                               class="btn yellow-btn"
                                               onchange="readURL(this);"/></a>
                                </div>
                                <span class="text-danger" id="review-image-error">{{ $errors->first('review_image') }}</span>
                            </div>
                            <div class="form-row privacy-policy-wrap">
                                <label>
                                    <input type="checkbox" name="is_agree"
                                           @if(old('is_agree', session()->get('review-is-agree')) == 'on')
                                           checked="checked"
                                        @endif/>
                                    <span>I agree to the ratemyestate <a href="{{route('terms-of-use')}}">Terms and Conditions</a> and <a
                                            href="{{route('privacy-policy')}}">Privacy Policy</a></span>

                                </label>
                            </div>
                            <div>
                                <span
                                    class="text-danger">{{ $errors->first('is_agree') ? 'You must agree terms and conditions.' : '' }}</span>
                            </div>
                            <div class="form-row form-footer">
                                <a href="{{route('nav-review-step1')}}" class="btn btn-back">Go Back</a>
                                <button type="submit" name="form_action" value="submit" class="btn yellow-btn">Submit</button>
                                @if(!empty($isHomeland) || (isset($review_cat) && (int)$review_cat === 2))
                                    <button type="submit" name="form_action" value="next" class="btn">Next</button>
                                @endif
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

        // end

        function setCookie(name, value, hours) {
            var expires = "";
            if (hours) {
                var date = new Date();
                date.setTime(date.getTime() + Number(hours) * 3600 * 1000);
                expires = "; expires=" + date.toGMTString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function deleteCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }

        deleteCookie('review');

        @if(session('error'))

        const sessionData = {!! json_encode(session()->all()) !!}
        // store review form data in cookie in case of new user registration
        const sessionKeys = [
            'review-company-id',
            'review-property-id',
            'review-year-complete',
            'buyer-type',
            'review-category',
            'review-state',
            'review-post-code',
            'review-suburb',
            'review-locality-id',
            'review-image',
            'review-image-system',
            'review-docs',
            'review-docs-system',
            'review-title',
            'review-recommendation',
            'review-is-agree',
            'review-comment',
            'review-tags',
            'review-rating'
        ];

        const cookieData = {};
        sessionKeys.forEach(key => {
            if (sessionData[key]) {
                cookieData[key] = sessionData[key];
            }
        });

        setCookie('review', JSON.stringify(cookieData), 72);

        let msg = '<?= session('error') ?>'

        Swal.fire({
            title: '<strong>Account Required</strong>',
            html:
                '<a href="{{route('login')}}">LOGIN</a>' +
                '<p style="margin-top:15px;">Does not have an account? <a href="{{route('register')}}" style="color:#07AFA9;font-weight:600;text-decoration:underline;">Click here to Signup</a></p>',
            showCloseButton: true,
            showConfirmButton: false
        });

        @endif
    </script>
    <script>
        @php
            $prObj = collect($propertyRatings['property_allowed_rating'])->map(function ($pr){
            return ['id'=>$pr['id'],'value'=>0];
            });
        @endphp

        const prObj = {!! $prObj->toJson() !!};


        function addCommentTag() {
            // need to change if we add another multi selection dropdown
            const newTag = $("input.select2-search__field").val();
            Swal.fire({
                title: 'Are you sure ?',
                text: "Create new comment tag " + newTag,
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
                            url: "{{route('tag.add')}}",
                            data: {
                                name: newTag,
                                type: 'positive',
                                is_active: 1
                            },
                            success: (res) => {
                                if (res.status && res.status === 'success') {
                                    resolve(res);
                                }
                            }, error: function (error) {
                                const errorObj = JSON.parse(error.responseText);
                                if (errorObj['errors'] && errorObj['errors']['name']) {
                                    reject(errorObj['errors']['name'][0]);
                                } else {
                                    reject(error.statusText);
                                }
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
                    var newOption = new Option(res['data']['name'], res['data']['id'], false, true);
                    let oldSelectedTags = [];
                    if ($("#tags").val()) {
                        oldSelectedTags = $("#tags").val();
                    }
                    $('#tags').append(newOption).trigger('change');
                    $("#tags").val([...oldSelectedTags, res['data']['id']]);
                    $('#tags').trigger('change');
                }
            });
        }

        function onUploadDoc(input) {
            if (input.files && input.files[0]) {
                const fileType = input.files[0].type;
                const imageTypes = ['image/jpg', 'image/jpeg', 'image/png'];
                $('#doc-name').text(input.files[0].name);
                jQuery('#session-doc').show();
                $('#review-docs-error').text('');
                input.removeAttribute('required');
                if (imageTypes.includes(fileType)) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('#session-doc').attr({'src': e.target.result});
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {

                    jQuery('#session-doc').attr({'src': {!! json_encode(url('/').'/images/document.png') !!}});
                }
            }
        }

        $(function () {
            const proofRequiredMessage = 'Proof of purchase is required. Please upload a document that confirms your purchase.';
            let hasSessionProof = false;

            // set document image if any

            const reviewDoc = {!! json_encode(session()->get('review-docs')) !!};
            if (reviewDoc) {
                hasSessionProof = true;
                const docName = reviewDoc.substr(reviewDoc.lastIndexOf('/') + 1, reviewDoc.length);
                const docType = reviewDoc.substr(reviewDoc.lastIndexOf('.') + 1, reviewDoc.length);
                $('#doc-name').text(docName);
                if (['jpg', 'jpeg', 'png'].includes(docType.toLowerCase())) {
                    $('#session-doc').attr({'src': reviewDoc})
                } else {
                    const docPath = {!! json_encode(url('/').'/images/document.png') !!}
                    $('#session-doc').attr({'src': docPath});
                }
            } else {
                $('#session-doc').hide();
                $('#doc-name').text('');
            }
            // end

            $('#review-step-two-form').on('submit', function (e) {
                const input = document.getElementById('review_docs');
                const hasSelectedFile = input && input.files && input.files.length > 0;
                if (!hasSelectedFile && !hasSessionProof) {
                    e.preventDefault();
                    $('#review-docs-error').text(proofRequiredMessage);
                    if (input) {
                        input.setAttribute('required', 'required');
                        input.focus();
                    }
                    return false;
                }
            });

            {{--const oldImg = {!! json_encode(old('review_image')) !!};--}}
            const guestData = {!! json_encode(session()->all()) !!};

            let selectedRatings = {!! old('rating') ? json_encode(old('rating')) : '{}' !!};

            if (Object.keys(selectedRatings).length === 0 && guestData['review-rating']) {
                selectedRatings = guestData['review-rating'];
            }
            let ratingAnswered = 0;
            let totalRating = 0;
            if (Object.keys(selectedRatings).length > 0) {
                Object.keys(selectedRatings).forEach((ratingNumber) => {
                    const indexToChange = prObj.findIndex(item => item.id == ratingNumber);
                    if (indexToChange > -1) {
                        prObj[indexToChange]['value'] = +selectedRatings[ratingNumber];
                    }
                    ratingAnswered += 1;
                    totalRating += +selectedRatings[ratingNumber];
                });
                let ceilAvg = Math.round(totalRating / ratingAnswered);
                let rateClassSlug = (Math.round((totalRating / ratingAnswered) / 0.5) * 0.5).toFixed(1).replace('.0', '').replace('.5', '-5');
                jQuery('.overall-rating').find('ul').addClass('overall-rate-color-' + rateClassSlug);

                while (ceilAvg > 0) {
                    // $('#rating_overall_' + ceilAvg).parent().addClass('color_overall_' + ceilAvg);
                    ceilAvg--;
                }
            }
            $.ajax({
                url: "{{ route('autocomplete.fetchTags') }}",
                method: 'POST',
                dataType: 'json',
            }).done(function (data) {
                $('#tags').select2({
                    maximumSelectionLength: 5,
                    placeholder: 'please select one',
                    data: data.results,
                    language: {
                        "noResults": function () {
                            return "No Results Found <a onclick='addCommentTag()'>(add)</a>";
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    },
                });
                let selectedTags = {!! json_encode(old('tags', session()->get('review-tags', []))) !!};
                $("#tags").val(selectedTags);
                $('#tags').trigger('change');

            });
        });

        $('.rating-radio').on('change', function () {
            const ratingId = $(this).data('rating-id');
            const val = $(this).val();

            $(this).parent().parent().find('li').removeClass('color_1 color_2 color_3 color_4 color_5');
            $('.review-col').children().find('li').removeClass('color_overall_1 color_overall_2 color_overall_3 color_overall_4 color_overall_5');
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
            let ceilAvg = Math.round(average);
            let rateClassSlug = (Math.round(average / 0.5) * 0.5).toFixed(1).replace('.0', '').replace('.5', '-5');
            jQuery('.overall-rating').find('ul').addClass('overall-rate-color-' + rateClassSlug);

            while (ceilAvg > 0) {
                // $('#rating_overall_' + ceilAvg).parent().addClass('overall-rate-color' + rateClassSlug);
                ceilAvg--;
            }
            let iterator = +val;

            while (iterator > 0) {
                $('#rating_' + ratingId + '_' + iterator).parent().addClass('color_' + val);
                iterator--;
            }
        });

    </script>
@endsection
