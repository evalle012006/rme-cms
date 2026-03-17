@extends('layouts.app-layout')

@section('content')
    <div class="sign-in-wrap">
        <div class="container">
            <div class="sing-in-box">

                <div class="join-comm-wrap" style="background-image: url(../images/login-content-bg-img.jpg);">
                </div>
                <div class="form-wrap">
                    <div>
                        <h3>Few steps ahead to join us</h3>
                        <form method="POST" id="register_form" action="{{route('save-builder')}}"
                              enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="form-row">
                                <label for="company_form_element">Company Name</label>
                                <input type="text" id="company_form_element" name="company_name"
                                       value="{{old('company_name')}}"
                                       placeholder="Mirvac">

                                <span class="text-danger">
                                    {{$errors->first('company_name')}}
                                    <?php
                                    if ($errors->first('company_name') && strpos($errors->first('company_name'), 'required') == false) {
                                        echo '<a href="#" id="learn-more">Learn more</a>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="form-row">
                                <label for="email_form_element">Your Email</label>
                                <input type="text" id="email_form_element" name="email" value="{{old('email')}}"
                                       placeholder="johnsmith@gmail.com">
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="website_form_element">Website Url</label>
                                <input type="text" id="website_form_element" name="website_url"
                                       value="{{old('website_url')}}"
                                       placeholder="www.ratemyestate.com">
                                <span class="text-danger">{{ $errors->first('website_url') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="password_form_element">Password</label>
                                <input type="password" id="password_form_element" name="password" value=""
                                       placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="c_password_form_element">Confirm Password</label>
                                <input type="password" id="c_password_form_element" name="password_confirmation" value=""
                                       placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                            </div>
                            <div class="form-row">
                                <label>Upload photo</label>
                                <div class="user-img-wrap">
                                    @if(session()->exists('company-logo'))
                                        <img id="session-image" class="image-preview" height="150px" width="200px"
                                             src={{session()->get('company-logo')}} alt='image'>
                                    @endif
                                    <img id="user-profile-image" class="image-preview">
                                </div>
                                <div class="upload-photo">
                                    <button href="javascript:;" class="btn yellow-btn">Upload Photo
                                        <input type='file'
                                               name="company_logo"
                                               class="btn yellow-btn"
                                               onchange="readURL(this);"
                                        /></button>
                                </div>
                                <span class="text-danger">{{ $errors->first('company_logo') }}</span>
                            </div>
                            <div class="form-row chkbox-wrap">
                                <label>
                                    <input type="checkbox" name="agreement">
                                    {{--                                <span>I would like to opt for <a href="{{route('privacy-policy')}}"><strong>privacy policy</strong></a></span>--}}
                                    <span>I would like to opt for <strong><a
                                                href="{{route('privacy-policy')}}">Privacy Policy</a></strong> Marketing updates.</span>
                                </label>
                                <div><span class="text-danger">{{ $errors->first('agreement') }}</span></div>
                            </div>
                            <div id="review-form-field"></div>

                            <div class="form-row">
                                <input type="submit" id="submit_btn" value="Register Now">
                            </div>
                        </form>
                        <div>
                            <p>Already have an account ? Please <a href="{{route('login')}}">Log In</a></p>
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
            let userSaved = {!! json_encode(session()->get('user_saved')) !!};
            let messageText = '<p>Your company has been registered you will be able to login once it is approved by admin.</p>';

            if (userSaved) {
                @php
                    session()->forget('user_saved');
                @endphp
                Swal.fire({
                    title: 'Registered successfully',
                    html: messageText,
                    showCloseButton: true,
                    showCancelButton: true,
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

            const learnMoreText = 'Company you have entered is already registered in ratemyestate, If you want to register this company with your credentials than you can send request email to admin with your firstname, lastname, email and company name. Thank you.';
            $("#learn-more").click(function () {
                Swal.fire({
                    title: 'Register your company',
                    html: learnMoreText,
                    showCloseButton: true,
                    confirmButtonText: 'Send mail', // go to homepage
                }).then(res => {
                    if (res['value']) {
                        // redirect to home
                        window.location.href = "mailto:ratemyestate@info.com?subject=Company Registration Request";
                    }
                });
            })
        });
    </script>
@endsection
