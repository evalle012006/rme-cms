@extends('layouts.app-layout')

@section('meta_title', 'Forgot Password | ' . seoSiteName())
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="forgot-pwd-wrap">
        <div class="container">
            <div class="box-wrap">
                <div class="form-wrap">
                    <div class="img-wrap">
                        <img src="{{asset('images/frgt-pwd-img.png')}}">
                    </div>
                    <h4>Forgot Your Password?</h4>
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-row 123">
                            <label>Your Email</label>
                            <input type="email" name="email" required autocomplete="email" autofocus>
                        </div>
                        <span class="text-danger">{{$errors->first('email') ? $errors->first('email') : ''}}</span>
                        <div class="form-row">
                            <input type="submit" name="Reset Pwd" value="Reset Password">
                        </div>
                    </form>
                    <div class="form-footer">
                        <p>Go back to <a href="{{route('login')}}">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        {{--const res = {!! session('status') ?  json_encode(session('status')): '[]' !!};--}}
        {{--console.log(res);--}}
        @if(session('status'))
        Swal.fire(
            'Success',
            'email sent successfully!',
            'success'
        );

        @endif
    </script>
@endsection

