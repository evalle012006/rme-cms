@extends('layouts.app-layout')

@section('meta_title', ($q ? 'Search results for "' . $q . '"' : 'Search') . ' | ' . seoSiteName())
@section('meta_description', $q ? ('Search results for "' . $q . '" across projects, reviews and articles on ' . seoSiteName() . '.') : ('Search projects, reviews and property guides on ' . seoSiteName() . '.'))
@section('meta_robots', 'noindex, follow')

@section('content')
    <div class="map-section map-section-new">
        <div class="container">
            <div class="map-section-title">
                <h1 class="search-page-headings">@if($q)Search results for "{{ $q }}"@else Search @endif</h1>
                <div class="srch-wrap">
                    <form action="{{route('general-search')}}">
                        <input type="text" name="q" id="search_key" title="search" placeholder="Search" value="{{$q}}">
                        <button>Search</button>
                    </form>
                </div>
            </div>
            <div class="content-wrap">
                <h3 class="search-page-headings">Projects</h3>
                <div id="search-properties-results">
                    @include('partials.search-properties')
                </div>
            </div>
            <div class="content-wrap reviews-wrapper">
                <h3 class="search-page-headings">Reviews</h3>
                <div id="search-reviews-results">
                    @include('partials.search-reviews')
                </div>
            </div>
            <div class="content-wrap blog-wrapper-content">
                <h3 class="search-page-headings">Blogs</h3>
                <div id="search-blogs-results">
                    @include('partials.search-blogs')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        $(document).ready(function () {
            const q = "{{\Request::get('q')}}";
            let pt = +"{{\Request::get('pt')}}";
            let rt = +"{{\Request::get('rt')}}";
            let bt = +"{{\Request::get('bt')}}";

            if (!pt) pt = 5;
            if (!rt) rt = 5;
            if (!bt) bt = 5;

            function currentUrl() {
                return "{{url('/search')}}?q=" + encodeURIComponent(q) + "&pt=" + pt + "&rt=" + rt + "&bt=" + bt;
            }

            function loadSearchSection(section, targetSelector) {
                var url = currentUrl() + "&section=" + section;
                var $target = $(targetSelector);
                $target.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (html) {
                        $target.html(html);
                        if (window.history && window.history.pushState) {
                            window.history.pushState({searchAjax: true}, '', currentUrl());
                        }
                    },
                    complete: function () {
                        $target.css('opacity', '1');
                    }
                });
            }

            $(document).on('click', '#property-show-more', function () {
                pt += 5;
                loadSearchSection('properties', '#search-properties-results');
            });
            $(document).on('click', '#review-show-more', function () {
                rt += 5;
                loadSearchSection('reviews', '#search-reviews-results');
            });
            $(document).on('click', '#blog-show-more', function () {
                bt += 5;
                loadSearchSection('blogs', '#search-blogs-results');
            });
        });
    </script>
@endsection
