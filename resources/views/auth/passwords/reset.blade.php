@extends('layouts.app-layout')

@section('content')
    <div class="forgot-pwd-wrap">
        <div class="container">
            <div class="box-wrap">
                <div class="form-wrap">
                    <div class="img-wrap">
                        <img src="{{asset('images/frgt-pwd-img.png')}}">
                    </div>
                    <h4>{{ __('Reset Password') }}</h4>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-row m-b-15">
                            <label for="email">{{ __('E-Mail Address') }}</label>

                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ $email ?? old('email') }}" required autocomplete="email"
                                   autofocus>

                            @isset($message)
                                <span class="text-danger">{{$message}}</span>
                                {{--                            <span class="invalid-feedback" role="alert">--}}
                                {{--                                            <strong>{{ $message }}</strong>--}}
                                {{--                                        </span>--}}
                            @endisset
                        </div>

                        <div class="form-row m-b-15">
                            <label for="password">{{ __('Password') }}</label>
                            <input id="password" type="password"
                                   placeholder="6+ Characters"
                                   class="form-control @error('password') is-invalid @enderror" name="password" required
                                   autocomplete="new-password">
                            @error('password')
                            <span class="text-danger" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <label for="password-confirm">{{ __('Confirm Password') }}</label>
                            <input id="password-confirm" type="password" class="form-control"
                                   placeholder="6+ Characters"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="form-row">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
