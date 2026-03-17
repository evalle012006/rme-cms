@section('extra-style')
    @extends('layouts.app-layout')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" rel="stylesheet"/>
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
                            <li><a href="{{route('builder-dashboard')}}">
                                    <img class="side-icons" src="{{asset('images/dashboard-icon.svg')}}">Dashboard</a>
                            </li>
                            <li>
                                <a href="{{ route('builder-property') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">My Property</a>
                            </li>
                            <li>
                                <a class="active-profile-tab" href="{{ route('company-users') }}">
                                    <img class="side-icons" src="{{asset('images/user-icon.svg')}}">Company Users</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="profile-right-part background-none">

                    <div class="latest-property-post-section">
                        <div class="profile-title">
                            <h4>Company Users</h4>
                            <div class="text-right all-props">
                                <a href="javascript:void(0)" id="modalBtn">Add User</a>
                            </div>
                        </div>
                        <div class="company-bio-wrap company-user-wrap">
                            @foreach($companyUsers as $user)
                                <div class="card-outer">
                                    {{--                                    <img--}}
                                    {{--                                        class="user-img"--}}
                                    {{--                                        src="{{$user->full_profile_image ? $user->full_profile_image : url('images/property-review-img-1.png') }}"--}}
                                    {{--                                        alt="Avatar">--}}
                                    <div class="card-container">
                                        <h5><b>{{$user->firstname.' '.$user->lastname}}</b></h5>
                                        <p>{{$user->email}}</p>
                                        <button class="btn yellow-btn report-btn" data-id="{{$user->id}}">Remove
                                        </button>
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
                <h4>Add New User</h4>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        $(document).ready(function () {

            // show add user modal
            var modal = document.getElementById("userModal");
            var btn = document.getElementById("modalBtn");
            var span = document.getElementsByClassName("close")[0];

            btn.onclick = function () {
                modal.style.display = "block";
                $("body").addClass("popup-show");
            }

            span.onclick = function () {
                modal.style.display = "none";
                $("body").removeClass("popup-show");
            }
            $("#builder-form").validate({
                rules: {
                    firstname: 'required',
                    lastname: 'required',
                    email: {
                        required: true,
                        email: true,
                        remote: {
                            url: "validate-email",
                            type: 'post',
                            data: {
                                csrf_test_name: function () {
                                    return '';
                                }
                            },
                        }
                    },
                    password: {
                        required: true,
                        minlength: 6
                    }
                },
                messages: {
                    firstname: 'First name field is required.',
                    lastname: 'Last name field is required.',
                    email: {
                        required: 'Email field is required.',
                        email: 'Please enter valid email.',
                        remote: 'Email has already been taken.'
                    },
                    password: {
                        required: 'Password field is required.',
                        minlength: 'Password must have minimum 6 length'
                    }
                },
                errorPlacement: function (error, element) {
                    element.parent().append(error);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });

            // end
            var APP_URL =
                {!! json_encode(url('/')) !!}
                // report popup
                $(".report-btn").on('click', function () {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Are you sure ?',
                        input: 'textarea',
                        inputPlaceholder: 'Remove reason...',
                        confirmButtonText: 'Remove',
                        showLoaderOnConfirm: true,
                        preConfirm: (value) => {
                            const reason = value;
                            const postData = {
                                id,
                                reason
                            };
                            return new Promise((resolve, reject) => {
                                $.ajax({
                                    type: "POST",
                                    url: APP_URL + '/builder/report',
                                    data: postData,
                                    success: (res) => {
                                        if (res.status && res.status === 'success') {
                                            resolve(res);
                                            Swal.fire({
                                                icon: 'success',
                                                // title: 'Oops...',
                                                text: 'User Reported Successfully!',
                                            })
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
                    }).then(function (result) {
                        if (result.value) {
                            const reason = result.value;
                            const postData = {
                                id,
                                reason
                            };
                            console.log(postData);
                        }
                    })
                    // window.location.href = 'report/' + $(this).data('id');
                    // console.log($(this).data('id'));
                });
            // end

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                    $("body").removeClass("popup-show");
                }
            }
            // end
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

        })
    </script>
@endsection
