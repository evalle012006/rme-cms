@extends('layouts.app-layout')

@section('meta_title', 'Contact Us | ' . seoSiteName())
@section('meta_description', 'Get in touch with the ' . seoSiteName() . ' team. Contact us for support, media enquiries, or to list your property development company.')

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{ $sectionData['banner']['image'] ? absoluteStorageUrl($sectionData['banner']['image']) : asset('images/homepage/HomepageHero.jpg') }});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h1>{{$sectionData['banner']['title']}}</h1>
            @else
{{-- CMS fallback (kept for later restore)
<h1>Contact Us</h1>
            --}}
@endif
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Contact Us</span>
            </div>
        </div>
    </div>

    <div class="map-section">
        {{--        <div class="map-wrap">--}}
        {{--            <iframe--}}
        {{--                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3086.6884699277725!2d-76.74441608463714!3d39.317984579507964!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c8195c881dce97%3A0xdddaa22a78e8b1e6!2s6704%20Whitestone%20Rd%2C%20Baltimore%2C%20MD%2021207%2C%20USA!5e0!3m2!1sen!2sin!4v1577773519509!5m2!1sen!2sin"--}}
        {{--                width="600" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>--}}
        {{--        </div>--}}
        <div class="container">
            <div class="info-box">
                <div class="form-wrap">
                    <h3>Send Us a Message</h3>
                    <form action="{{route('contact-save')}}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-field">
                                <label>First Name</label>
                                <input type="text" name="firstname" placeholder="John">
                                <span class="text-danger">{{$errors->first('firstname')}}</span>
                            </div>
                            <div class="form-field">
                                <label>Last Name</label>
                                <input type="text" name="lastname" placeholder="Smith">
                                <span class="text-danger">{{$errors->first('lastname')}}</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-field">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="Johnsmith@ratemyestate.com">
                                <span class="text-danger">{{$errors->first('email')}}</span>
                            </div>
                            <div class="form-field">
                                <label>Subject</label>
                                <input type="text" name="subject" placeholder="Business Talk">
                                <span class="text-danger">{{$errors->first('subject')}}</span>
                            </div>
                        </div>
                        <div class="form-row msg-row">
                            <div class="form-field">
                                <label>Message</label>
                                <textarea name="message"
                                          placeholder="Hello, Do you have a moment to talk about...."></textarea>
                                <span class="text-danger">{{$errors->first('message')}}</span>
                            </div>
                            <div class="form-field">
                                <input type="submit">
                            </div>
                        </div>
                    </form>
                    @if (\Session::has('message'))
                        <div class="alert alert-success">
                            <ul>
                                <li><h3>{!! \Session::get('message') !!}</h3></li>
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="info-wrap">
                    <h3>We are Located At:</h3>

                    <div class="info-content">
                        <ul>
                            {{--                            <li class="address">6704 Whitestone Road Woodlawn, MD 21207</li>--}}
                            {{--                            <li class="phone"><a href="tel:+(61) 872 5104 904">+(61) 872 5104 904</a></li>--}}
                            {{--                            <li class="fax">410.685.0066</li>--}}
                            <li class="mail"><a href="mailto:admin@ratemyestate.com.au">admin@ratemyestate.com.au</a>
                            </li>
                        </ul>
                        <div class="social-media-wrap">
                            <ul>
                                <li><a href="https://www.facebook.com/share/1Un93B9z23/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" aria-label="Rate My Estate on Facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                <li><a href="https://www.instagram.com/ratemyestate.com.au/" target="_blank" rel="noopener noreferrer" aria-label="Rate My Estate on Instagram"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                                {{--                                <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>--}}
                                {{--                                <li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>--}}
                                {{--                                <li><a href="#"><i class="fa fa-rss" aria-hidden="true"></i></a></li>--}}
                            </ul>
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
        @if(session('contact_us'))
        Swal.fire({
            icon: 'success',
            text: '<?= session('contact_us') ?>'
        })
        @elseif(session('contact_us_error'))
        Swal.fire({
            icon: 'error',
            text: '<?= session('contact_us_error') ?>'
        })
        @endif
    </script>
@endsection
