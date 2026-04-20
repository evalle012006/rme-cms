@extends('layouts.app-layout')
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
    <div class="my-profile-section">
        <div class="container">
            <div class="profile-left-part">
                <div class="user-img-wrap">
                    <img id="user-profile-image"
                         src="{{$user->full_profile_image ? $user->full_profile_image : asset('images/author-img-grey.png')}}"
                         alt="file upload"/>
                </div>
                <div class="user-detail">
                    <h6>{{$user->firstname .' '.$user->lastname}}</h6>
                    <span>{{$user->email}}</span>
                </div>
                <div class="upload-photo">
                    {{--                    <a href="#" class="btn yellow-btn">Upload New Photo</a>--}}
                </div>
                <div class="sidebar-menu">
                    <ul>
                        <li><a href="{{route('my-profile-page')}}"><img src="images/my-profile-icon.png">My Profile</a>
                        </li>
                        <li><a class="active-profile-tab" href="#"><img src="images/my-reviews-icon.png">My Reviews</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="profile-right-part">
                <div class="profile-title">
                    <h3>My Reviews</h3>
                    <div class="srch-notification-wrap">
                        <div class="srch-wrap">
                            <form action="{{route('my-review-page')}}" method="GET" id="review_form">
                                <input type="text" id="search_review" name="q" placeholder="Search Reviews"
                                       value="{{request()->input('q')}}">
                            </form>
                        </div>
                        <div class="notification">
                            <img src="images/notification-icon.png">
                            <span class="noti-count">2</span>
                        </div>
                    </div>
                </div>
                <div class="profile-content">
                    <div class="personal-reviews-wrap">
                        @foreach($user_review as $userReview)
                            <div class="single-review">
                                <div class="img-wrap">
                                    {{--                                    <a href="{{url('review/'.$userReview['slug'])}}"><img--}}
                                    {{--                                            src="{{$userReview['full_review_image'] ? $userReview['full_review_image']: 'images/property-review-img-1.png'}}"></a>--}}
                                    <a href="{{$userReview->status ? url('review/'.$userReview['slug']) : "#"}}">
                                        <img
                                            src="{{isset($userReview->property) && isset($userReview->property->full_property_logo) ? $userReview->property->full_property_logo: 'images/property-review-img-1.png'}}"></a>
                                </div>
                                <div class="review-detail-content">
                                    <div class="author-top">
                                        <div class="rating-wrap fix-width-120">
                                            <ul class="general-star-rating overall-rate-color-{{ratingClass($userReview->avg_rating)}}">
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                                <li><a><img src="{{asset('images/rating-img.png')}}"></a></li>
                                            </ul>
                                        </div>
                                        @if($userReview->status===0)
                                            <div class="setting-icon">
                                                <div class="btn-wrap">
                                                    <a class="btn" href="javascript:void(0)" id="modalBtn"
                                                       data-review="{{$userReview}}">
                                                        Edit
                                                    </a>
                                                    <a class="btn pending-btn" href="#">
                                                        Pending
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="views-count">
                                            <ul>
                                                {{--                                                <li class="views-number">{{views($userReview)->count()}} views</li>--}}
                                                {{--                                                <li class="useful-number"> 90 useful</li>--}}
                                                {{--                                                <li class="share-number"> 18 share</li>--}}
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="author-btm">
                                        <p>{{$userReview->title}}</p>
                                        <a href="{{url('property-review/'.$userReview['property']['slug'])}}">{{$userReview['property']['project_name']}}</a>
                                        @if($userReview['property']['company'])
                                            by <a
                                                href="{{url('property/'.$userReview['property']['company']['slug'])}}">{{$userReview['property']['company']['company_name']}}</a>
                                            </p>
                                        @endif

                                    </div>
                                    <div class="tags-wrap">
                                        <ul>
                                            @foreach($userReview->reviewTags as $reviewTag)
                                                <li><a href="#">{{$reviewTag->name}}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{$user_review->links()}}
                        @if(!(count($user_review) > 0))
                            <div class="fa-2x"> No Reviews Found..</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="reviewModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content addnew-property">
            <div class="modal-header">
                <span class="close closeAdvertise">
                    <img src="{{asset('images/close-icon.png')}}" alt="">
                </span>
                <h4>Edit Review</h4>
            </div>
            <div class="modal-body">
                <form action="{{route('update-review')}}" method="POST" id="review-form" autocomplete="off">
                    @csrf
                    <input type="hidden" name="id" id="review_id"/>
                    <div class="property-form">
                        <div class="property-form">
                            <div class="col-50">
                                <label>Title</label>
                                <input type="text" name="title" id="review_title" placeholder="My Review"/>
                            </div>
                            <div class="col-50">
                                <label>Comment Tags</label>
                                <select class="w-100" id="tags" name="tags[]" multiple="multiple"></select>
                            </div>
                            <div class="col-100">
                                <label>Comment</label>
                                <textarea name="comment" id="review_comment"
                                          placeholder="comment about your experience..."></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn view-detail-link">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

    <script>
        $(document).ready(function () {

            $('#search_review').keypress(function (event) {
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if (keycode == '13') {
                    $("#review_form").submit();
                }
            });

            // show add user modal
            var modal = document.getElementById("reviewModal");
            var btn = document.getElementById("modalBtn");
            var span = document.getElementsByClassName("close")[0];

            btn.onclick = function () {
                modal.style.display = "block";
                var reviewData = $(this).data('review');
                var selectedTags = reviewData['review_tags'].map(item => {
                    return item['pivot']['tag_id'];
                });
                $("#tags").val(selectedTags);
                $('#tags').trigger('change');
                $("#review_title").val(reviewData['title']);
                $("#review_comment").val(reviewData['comment']);
                $("#review_id").val(reviewData['id']);
                $("body").addClass("popup-show");
            }

            span.onclick = function () {
                modal.style.display = "none";
                $("body").removeClass("popup-show");
            }
            $("#review-form").validate({
                rules: {
                    title: 'required',
                    'tags[]': 'required'
                },
                messages: {
                    title: 'Review title field is required.',
                    'tags[]': 'Comment tags is required.',
                },
                errorPlacement: function (error, element) {
                    element.parent().append(error);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });

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
                {{--let selectedTags = {};--}}
                {{--const formToken = {!! old('_token') ? json_encode(old('_token')) : '[]' !!};--}}
                {{--if (formToken.length > 0) {--}}
                {{--    selectedTags = {!! old('tags') ? json_encode(old('tags')) : '[]' !!};--}}
                {{--} else {--}}
                {{--    selectedTags = {!! session()->get('review-tags') ? json_encode(session()->get('review-tags')) : "[]" !!}--}}
                {{--}--}}
                {{--$("#tags").val(selectedTags);--}}
                {{--$('#tags').trigger('change');--}}

            });

        });

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
                        oldSelectedTags = $("#tags").val()
                    }
                    $('#tags').append(newOption).trigger('change');
                    $("#tags").val([...oldSelectedTags, res['data']['id']]);
                    $('#tags').trigger('change');
                }
            });
        }
    </script>
@endsection
