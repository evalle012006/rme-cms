@extends('layouts.app-layout')

@section('meta_title', 'Property Blog & Buying Guides | ' . seoSiteName())
@section('meta_description', 'Explore property buying guides, finance tips and real estate advice from the ' . seoSiteName() . ' Property Blog.')

@section('content')
    <div class="bg-main inner-banner" style="background-image: url({{asset('images/homepage/HomepageHero.jpg')}});">
        <div class="banner-caption">
            <h1>Popular Blog Post</h1>
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Popular Blog Post</span>
            </div>
        </div>
    </div>

    <div class="popular-blogs-section">
        <div class="container">
            <div id="blog-posts-results">
                @include('partials.blog-posts-list')
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        (function ($) {
            function loadBlogPosts(url) {
                var $results = $('#blog-posts-results');
                $results.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    success: function (html) {
                        $results.html(html);
                        if (window.history && window.history.pushState) {
                            window.history.pushState({blogPostsAjax: true}, '', url);
                        }
                    },
                    complete: function () {
                        $results.css('opacity', '1');
                    }
                });
            }

            $(document).on('click', '#blog-posts-results .pagination a', function (event) {
                event.preventDefault();
                var href = $(this).attr('href');
                if (href && href !== '#') {
                    loadBlogPosts(href);
                }
            });

            window.addEventListener('popstate', function (event) {
                if (event.state && event.state.blogPostsAjax) {
                    loadBlogPosts(window.location.href);
                } else {
                    window.location.reload();
                }
            });
        })(jQuery);
    </script>
@endsection
