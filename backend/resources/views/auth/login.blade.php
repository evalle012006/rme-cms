@extends('layouts.app-layout')

@section('content')
    <div class="sign-in-wrap">
        <div class="container">
            <div class="sing-in-box">
                <div class="form-wrap">
                    <div class="form-wrap-in">
                        <h3>Login to ratemyestate.com.au</h3>
                        @if(!request()->has('company'))
                            <div class="social-media-wrap">
                                <ul>
                                    <li class="fb-icon"><a href="{{route('socialite-facebook')}}"><i class="fa fa-facebook"
                                                                                                     aria-hidden="true"></i></a>
                                    </li>
                                    <li class="gplus-icon"><a href="{{ route('socialite-google') }}"><i class="fa fa-google"
                                                                                                        aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <h6>Or use your email account</h6>
                        @endif
                        <form method="POST" action="{{route('login')}}">
                            @csrf
                            <div class="form-row">
                                <label for="email">Your Email</label>
                                <input id="email" type="email" name="email" value="" placeholder="Email Address">
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="password">Password</label>
                                <a href="{{route('password.request')}}">Forgot your password?</a>
                                <input id="password" type="password" name="password" value="" placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>
                            <div class="form-row">
                                <input type="submit" value="Sign In">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="join-comm-wrap" style="background-image: url(images/login-content-bg-img.jpg);">
                    <h3>Register for Business Portal</h3>
                    {{--                    <p>Enter your personal details and start your journey with us</p>--}}
                    <p>Sign up to be kept up to date on the upcoming business solutions.</p>
                    <a class="btn yellow-btn" href="{{route('register')}}">Register</a>
                    @if(request()->has('company'))
                        <a class="btn yellow-btn" href="{{url('builder/register')}}">Register Company</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        const verified = {!! json_encode(session()->get('verified')) !!};
        const loginCred = {!! json_encode(session()->get('login_cred')) !!};
        if (verified == false && verified !== null) {
            if (loginCred) {
                @php
                    session()->forget('verified');
                @endphp
                Swal.fire({
                    icon: 'warning',
                    text: 'Your email has not been verified',
                    title: 'login failed',
                    cancelButtonText: 'OK',
                    confirmButtonText: 'Resend Verification Email',
                    showCancelButton: true
                }).then(res => {
                    if (res.value) {
                        window.location.href = "/resend-verification";
                    }
                })
            } else {
                @php
                    session()->forget('verified');
                @endphp
                Swal.fire({
                    icon: 'warning',
                    text: 'Your email has not been verified',
                    title: 'login failed'
                });
            }
        }
    </script>
@endsection

