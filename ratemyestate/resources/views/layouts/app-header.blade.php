<header id="header">
    <div class="header-top">
        <div class="container">
            <div class="logo-wrap">
                <a href="{{url('/')}}"><img src="{{asset('images/logo.png')}}"></a>
            </div>
            <div class="contact-info-header">
                <ul>
                    <li>
                        <a href="mailto:admin@ratemyestate.com.au">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                            admin@ratemyestate.com.au
                        </a>
                    </li>
                </ul>
                <a class="btn btn-bordered" href="{{url('login').'?company'}}">+ For Companies</a>
            </div>
        </div>
    </div>
    <div class="header-main">
        <div class="container">
            <div class="nav-menu">
                <a class="menulinks"><i></i></a>
                <ul class="mainmenu">
                    <li><a href="{{route('root')}}">Home </a></li>
                    <li><a href="{{route('about-page')}}">About </a></li>
                    {{--                    <li class="property-guide"><a href="#property-guide">Property Guide</a></li>--}}
                    <li class="property-guide"><a href="{{route('property-guide')}}">Property Guide</a></li>
                    {{--                    <li><a href="#">Location </a></li>--}}
                    <li><a href="{{route('property-coach')}}">Property Coach </a></li>
                    <li><a href="{{route('contact-page')}}">Contact</a></li>
                    <li>
                        <a href="{{route('property-listing')}}">Reviews</a>
                        <ul class="sub-menu">
                            @foreach(getPropertyCategory() as $cat)
                                <li><a href="{{route('property-listing-with-slug',['slug'=>$cat['slug']])}}">{{$cat['name']}}</a></li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="header-btn">
                @if(!auth()->user() || (auth()->user() && auth()->user()->hasRole('reviewer')))
                    <div class="review-dropdwn">
                        <a class="yellow-btn btn no-arrow" href="{{route('review-step-1')}}">Write a review</a>
                    </div>
                @endif
                @auth
                    <div class="review-dropdwn">
                        <a class="yellow-btn btn"
                           href="{{route('root')}}">{{auth()->user()->name ?? ucfirst(auth()->user()->firstname)}}</a>
                        <ul class="dropdown">
                            <li>
                                <a href="{{auth()->user()->hasRole('reviewer') ? route('my-profile-page') : route('builder-dashboard')}}">
                                    My Profile</a></li>
                            <li><a href="{{route('logout')}}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">Logout</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="btn btn-bordered" href="{{route('login')}}">Login</a>
                @endauth
            </div>
            <div class="srch-wrap">
                <form action="{{route('general-search')}}">
                    <input type="text" name="q" id="search_key" title="search" placeholder="Search">
                </form>
            </div>
        </div>
    </div>
</header>
