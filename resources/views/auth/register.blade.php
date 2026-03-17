@extends('layouts.app-layout')

@section('content')
    <div class="sign-in-wrap">
        <div class="container">
            <div class="sing-in-box">

                <div class="join-comm-wrap" style="background-image: url(images/login-content-bg-img.jpg);">
                    <h3>Already have an account? Log in.</h3>
                    <p>To keep connected with us Please log in with your personal info</p>
                    <a class="btn yellow-btn" href="{{route('login')}}">Sign In</a>
                </div>
                <div class="form-wrap">
                    <div>
                        <h3>Few steps ahead to join us</h3>
                        <div class="social-media-wrap">
                            <ul>
                                <li class="fb-icon">
                                    <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                                </li>
                                <li class="gplus-icon">
                                    <a href="#"><i class="fa fa-google" aria-hidden="true"></i></a>
                                </li>
                            </ul>
                        </div>
                        <h6>Or use your email for registration</h6>
                        <form method="POST" id="register_form" action="{{route('register')}}">
                            @csrf
                            <div class="form-row">
                                <label for="firstname_form_element">Your First Name</label>
                                <input type="text" id="firstname_form_element" name="firstname" value="{{old('firstname')}}"
                                       placeholder="First Name">
                                <span class="text-danger">{{ $errors->first('firstname') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="lastname_form_element">Your Last Name</label>
                                <input type="text" id="lastname_form_element" name="lastname" value="{{old('lastname')}}"
                                       placeholder="Last Name">
                                <span class="text-danger">{{ $errors->first('lastname') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="display_name_form_element">Your Display Name</label>
                                <input type="text" id="display_name_form_element" name="display_name"
                                       value="{{old('display_name')}}"
                                       placeholder="Display Name">
                                <span class="text-danger">{{ $errors->first('display_name') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="email_form_element">Your Email</label>
                                <input type="text" id="email_form_element" name="email" value="{{old('email')}}"
                                       placeholder="johnsmith@gmail.com">
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="password_form_element">Password</label>
                                <input type="password" id="password_form_element" name="password" value=""
                                       placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>
                            <div class="form-row chkbox-wrap">
                                <label>
                                    <input type="checkbox" name="agreement">
                                    {{--                                <span>I would like to opt for <a href="{{route('privacy-policy')}}"><strong>privacy policy</strong></a></span>--}}
                                    <span>Sign me up.  I agree to receive news, notification about my reviews.  For further information please read our
                                        <strong><a
                                                href="{{route('privacy-policy')}}">Privacy Policy.</a></strong>
                                    </span>
                                </label>
                                <div>
                                    <span class="text-danger">{{ $errors->first('agreement') }}</span>
                                </div>
                            </div>
                            <div id="review-form-field"></div>

                            <div class="form-row">
                                <input type="submit" id="submit_btn" value="Sign Up">
                            </div>
                        </form>
                        <div class="form-footer">
                            <p>Already Have an account ? Please <a href="{{route('login')}}">Sign In</a></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        $(document).ready(function () {
            let APP_URL = {!! json_encode(url('/')) !!};
            // let messageText = '<p>Please verify your account by clicking on link sent to your email address.</p>';
            let messageText = "<p>Thanks for joining the ratemyestate community. Now that you've created an account, you will shortly receive an email requiring you to verify your email. Once verified you'll be able to share your newly posted review with others.</p>";

            let reviewInserted = {!!json_encode(session()->get('new_review')) !!};
            let userSaved = {!! json_encode(session()->get('user_saved')) !!};

            if (reviewInserted) {
                // delete review cookie and unset new_review from session
                deleteCookie('review');
                @php
                    session()->forget('new_review');
                @endphp
                // end
                // messageText += '<p>Your review has been successfully saved and will be pending until it is approved by admin.</p>';
            }

            if (userSaved) {
                @php
                    session()->forget('user_saved');
                @endphp
                Swal.fire({
                    title: 'Registered successfully',
                    html: messageText,
                    showCloseButton: false,
                    showCancelButton: false,
                    confirmButtonText: 'Go To Homepage', // go to homepage
                    cancelButtonText: 'Go To Login' // go to login
                }).then(res => {
                    if (res['value']) {
                        // redirect to home
                        window.location.href = APP_URL + '/';
                    }
                    if (res['dismiss'] && res['dismiss'] === 'cancel') {
                        window.location.href = APP_URL + '/login';
                    }
                });
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

            function deleteCookie(name) {
                document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }

            $("#register_form").submit(function (e) {
                const formData = getCookie('review');
                if (formData) {
                    $("<input />").attr("type", "hidden")
                        .attr("name", "review_form_data")
                        .attr("value", formData)
                        .appendTo("#review-form-field");
                }
                return true;
            });


        })


    </script>
@endsection
