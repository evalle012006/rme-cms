@extends('layouts.app-layout')

@section('meta_title', 'My Profile | ' . seoSiteName())
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <div class="my-profile-section">
        @if($user)
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
                        <a href="javascript:;" class="btn yellow-btn">Upload New Photo
                            <input type='file'
                                   name="profile_image"
                                   id="profile_image"
                                   class="btn yellow-btn"
                            />
                        </a>
                    </div>
                    <div class="sidebar-menu">
                        <ul>
                            <li><a class="active-profile-tab" href="{{route('my-profile-page')}}"><img
                                        src="images/my-profile-icon.png">My
                                    Profile</a></li>
                            <li><a href="{{ route('my-review-page') }}"><img src="images/my-reviews-icon.png">My
                                    Reviews</a></li>
                        </ul>
                    </div>
                </div>


                <div class="profile-right-part">
                    <div class="profile-title">
                        <h3>My Profile</h3>
                        <div class="notification">
                            <img src="images/notification-icon.png">
                            <span class="noti-count">2</span>
                        </div>
                    </div>

                    <form method="POST" action="{{route('change-request')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="profile-content">
                            <h4>Change Display Name</h4>
                            <div class="btn-wrap">
                                <button type="submit"
                                        class="btn btn-bordered submit-name" {{$user->has_change_request ? 'disabled' : ''}}>
                                    Place
                                    Request
                                </button>
                            </div>
                            <div class="form-wrap">
                                @if($user->has_change_request)
                                    <p>*Your request has been placed for new display name :
                                        <strong>{{$user->requested_display_name}}</strong></p>
                                @endif

                                <div class="form-row">
                                    <div class="form-field form-group">
                                        <label>Display Name</label>
                                        <input type="hidden" name="id" id="hidden_id" value="{{$user->id}}">
                                        <input type="text" name="display_name" class="form-control"
                                               placeholder="john_doe"
                                               value="{{old('display_name') ? old('display_name') : $user->display_name}}"
                                            {{$user->has_change_request ? 'readonly' : ''}}
                                        >
                                        <span class="text-danger">{{ $errors->first('display_name') }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                    <form method="POST" action="{{route('update-profile')}}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="hidden_id" value="{{$user->id}}">

                        <div class="profile-content">

                            <h4>Basic Information</h4>
                            <div class="btn-wrap">
                                <a class="btn btn-bordered" href="{{route('root')}}">Cancel</a>
                                <button type="submit" class="btn">Save</button>
                            </div>
                            <div class="form-wrap">
                                <div class="form-row">
                                    <div class="form-field form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control"
                                               placeholder="claudia.mills@gmail.com"
                                               value="{{$user->email}}">
                                        <span class="text-danger">{{ $errors->first('email') }}</span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field form-group">
                                        <label>First Name</label>
                                        <input type="text" name="firstname" class="form-control" placeholder="Claudia"
                                               value="{{$user->firstname}}">
                                        <span class="text-danger">{{ $errors->first('firstname') }}</span>
                                    </div>
                                    <div class="form-field form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="lastname" class="form-control" placeholder="Mills"
                                               value="{{$user->lastname}}">
                                        <span class="text-danger">{{ $errors->first('lastname') }}</span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field form-group">
                                        <label>Phone</label>
                                        <input type="phone" name="mobile" class="form-control"
                                               placeholder="9876543210"
                                               value="{{$user->mobile}}">
                                        <span class="text-danger">{{ $errors->first('mobile') }}</span>
                                    </div>
                                    <div class="form-field form-group">
                                        <label>Location</label>
                                        <input type="text" name="location" class="form-control" placeholder="Queensland"
                                               value="{{$user->location}}">
                                        <span class="text-danger">{{ $errors->first('location') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        @endif
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        $(document).ready(function () {
            let msg = '';
            @if(session('profile-update'))
                msg = '<?= session('profile-update') ?>'
            Swal.fire({
                icon: 'success',
                title: 'Success...',
                text: msg
            })
            @elseif(session('request_placed'))
                msg = '<?= session('request_placed') ?>'
            Swal.fire({
                icon: 'success',
                title: 'Success...',
                text: msg
            })

            @elseif(session('request_placed_error'))
                msg = '<?= session('request_placed_error') ?>'
            Swal.fire({
                icon: 'warning',
                text: msg
            })
            @endif

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
                            url: "{{route('update-photo')}}",
                            type: "POST",
                            data: fData,
                            processData: false,
                            contentType: false,
                            success: function () {
                            }
                        });
                    }
                }
            })

            // function onChangeProfileImage(event) {
            //     readURL(event);
            //     console.log(new FormData(event));
            // }
        });

    </script>
@endsection

