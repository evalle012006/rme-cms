@extends('layouts.app-layout')

@section('meta_title', 'My Reviews | ' . seoSiteName())
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
                    <div class="personal-reviews-wrap" id="my-review-results">
                        @include('partials.my-review-list')
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

            function loadMyReviews(url) {
                var $results = $('#my-review-results');
                $results.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (html) {
                        $results.html(html);
                        if (window.history && window.history.pushState) {
                            window.history.pushState({myReviewAjax: true}, '', url);
                        }
                    },
                    complete: function () {
                        $results.css('opacity', '1');
                    }
                });
            }

            $('#review_form').on('submit', function (event) {
                event.preventDefault();
                var action = $(this).attr('action');
                var query = $(this).serialize();
                var url = action + (action.indexOf('?') === -1 ? '?' : '&') + query;
                loadMyReviews(url);
            });

            $('#search_review').keypress(function (event) {
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if (keycode == '13') {
                    event.preventDefault();
                    $("#review_form").submit();
                }
            });

            $(document).on('click', '#my-review-results .pagination a', function (event) {
                event.preventDefault();
                var href = $(this).attr('href');
                if (href && href !== '#') {
                    loadMyReviews(href);
                }
            });

            window.addEventListener('popstate', function (event) {
                if (event.state && event.state.myReviewAjax) {
                    loadMyReviews(window.location.href);
                } else {
                    window.location.reload();
                }
            });

            // show add user modal
            var modal = document.getElementById("reviewModal");
            var span = document.getElementsByClassName("close")[0];

            $(document).on('click', '#modalBtn', function () {
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
            });

            if (span) {
                span.onclick = function () {
                    modal.style.display = "none";
                    $("body").removeClass("popup-show");
                }
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
