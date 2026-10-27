@extends('layouts.app-layout')

@php
    $listingCategory = !empty($cat_slug) ? reverseSlug($cat_slug) : null;
    $listingSearch = request('search_key') ?: request('search_company');

    if ($listingCategory) {
        $listingTitle = $listingCategory . ' Reviews | ' . seoSiteName();
        $listingDescription = 'Browse verified ' . strtolower($listingCategory) . ' reviews and ratings from real Australian property buyers on ' . seoSiteName() . '.';
    } elseif ($listingSearch) {
        $listingTitle = $listingSearch . ' - Project Reviews | ' . seoSiteName();
        $listingDescription = 'See reviews and ratings matching "' . $listingSearch . '" from real property buyers on ' . seoSiteName() . '.';
    } else {
        $listingTitle = 'Property & Real Estate Reviews | ' . seoSiteName();
        $listingDescription = 'Compare Australian property developers, builders and new estates with honest, verified reviews and ratings on ' . seoSiteName() . '.';
    }
@endphp

@section('meta_title', $listingTitle)
@section('meta_description', $listingDescription)

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{ !empty($sectionData['banner']['image']) ? absoluteStorageUrl($sectionData['banner']['image']) : asset('images/homepage/HomepageHero.jpg') }});">
        <div class="banner-caption">
            @if(!empty($sectionData['banner']['title']))
                <h2>{{$sectionData['banner']['title']}}</h2>
            @else
{{-- CMS fallback (kept for later restore)
<h2>Reviews Listing</h2>
            --}}
@endif
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Reviews Listing</span>
            </div>
        </div>
    </div>
    <div class="review-listing-section property-review-listing">
        <div class="container">
            <div class="review-box-wrap">
                <h3>Reviews Listing</h3>
                <div class="view-filter">
                    <h4>View by:</h4>
                    <div class="view-row">
                        @if(strpos(request()->url(),'property_search')!==false)
                            <a class="view-type-link" data-view-type="2" href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>2]))}}">
                                <div class="4-grid">
                                    <img src="{{asset('images/4-grid.png')}}">
                                </div>
                            </a>
                            <a class="view-type-link" data-view-type="1" href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>1]))}}">
                                <div class="9-grid">
                                    <img src="{{asset('images/9-grid.png')}}">
                                </div>
                            </a>
                            <a class="view-type-link" data-view-type="3" href="{{route('property_search',array_merge(request()->except('view_type'),['view_type'=>3]))}}">
                                <div class="list-view">
                                    <img src="{{asset('images/list.png')}}">
                                </div>
                            </a>
                        @else
                            @empty($cat_slug)
                                <a class="view-type-link" data-view-type="2" href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>2]))}}">
                                    <div class="4-grid">
                                        <img src="{{asset('images/4-grid.png')}}">
                                    </div>
                                </a>
                                <a class="view-type-link" data-view-type="1" href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>1]))}}">
                                    <div class="9-grid">
                                        <img src="{{asset('images/9-grid.png')}}">
                                    </div>
                                </a>
                                <a class="view-type-link" data-view-type="3" href="{{route('property-listing',array_merge(request()->except('view_type'),['view_type'=>3]))}}">
                                    <div class="list-view">
                                        <img src="{{asset('images/list.png')}}">
                                    </div>
                                </a>
                            @else
                                <a class="view-type-link" data-view-type="2" href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>2],['slug'=>$cat_slug]))}}">
                                    <div class="4-grid">
                                        <img src="{{asset('images/4-grid.png')}}">
                                    </div>
                                </a>
                                <a class="view-type-link" data-view-type="1" href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>1],['slug'=>$cat_slug]))}}">
                                    <div class="9-grid">
                                        <img src="{{asset('images/9-grid.png')}}">
                                    </div>
                                </a>
                                <a class="view-type-link" data-view-type="3" href="{{route('property-listing-with-slug',array_merge(request()->except('view_type'),['view_type'=>3],['slug'=>$cat_slug]))}}">
                                    <div class="list-view">
                                        <img src="{{asset('images/list.png')}}">
                                    </div>
                                </a>
                            @endempty
                        @endif
                    </div>
                </div>
                <div class="filter-row">
                    <form id="property-filter-form"
                        action="{{isset($cat_slug) ? route('property_search_slug',$cat_slug) : route('property_search')}}"
                        method="GET">
                        <input type="hidden" name="view_type" value="{{request()->get('view_type',1)}}">
                        <div class="sort-filter">
                            <h4>Filter by:</h4>

                            <div class="filter-wrap">
                                <div class="cat-filter">
                                </div>

                                <div class="filter-srch-row">
                                    <input type="text" name="filter_by" value="{{request()->input('filter_by')}}"
                                           placeholder="Search by Suburb">
                                    {{--                                    <input type="hidden" name="is_slug"--}}
                                    {{--                                           value="{{request()->segment(2) ?? request()->input('is_slug')}}">--}}
                                    <input type="text" name="search_key" value="{{request()->input('search_key')}}"
                                           placeholder="Search by Project Name">
                                    <input type="text" name="search_company"
                                           value="{{request()->input('search_company')}}"
                                           placeholder="Search by Company Name">
                                    <input type="submit" name="" value="Search">

                                </div>
                                <div class="advance-srch-drpdwn">
                                    <h6>Advanced Search</h6>
                                    <div class="filter-dropdown">
                                        <div class="sort-filter-col">
                                            @if(!isset($cat_slug) || $cat_slug=='')
                                                <div class="cat-filter">
                                                    <h6>Sort Category</h6>
                                                    @foreach($property_category as $propertyCat)
                                                        <label>
                                                            <input type="checkbox" name="category_type[]"
                                                                   value="{{$propertyCat['id']}}" {{ setSortCategorySelected($propertyCat['id']) }}
                                                            >
                                                            <span>{{$propertyCat['name']}}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="rating-filter">
                                                <h6>Sort By Ratings</h6>
                                                @foreach(array_reverse(range(1,5)) as $number)
                                                    <label>
                                                        <input type="checkbox" name="rating[]"
                                                               value={{$number}} {{request()->input('rating') ?( array_search($number,request()->input('rating'))!==false ? 'checked' : '' ): ''}}>
                                                        <span><img
                                                                src="{{url('images/'.$number.'-star-rating.png')}}"></span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="review-tag-filter">
                                                <h6>Sort By Review Tags</h6>
                                                <ul>
                                                    @foreach($tags as $tag)
                                                        <li>
                                                            <label>
                                                                <input type="checkbox" name="tags[]"
                                                                       value={{$tag->id}} {{request()->input('tags') ?( array_search($tag->id,request()->input('tags'))!==false ? 'checked' : '' ): ''}}>
                                                                <span>{{$tag->name}}</span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{--                {{$tags}}--}}
                <div id="list1" class="box-listing ">
                    @include('partials.property-listing-results')
                </div>


            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        (function ($) {
            function refreshListingEqualHeight() {
                if (typeof equalheight === 'function') {
                    equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
                }
            }

            // common.js hides .content-panel once on first load. Pagination,
            // filters, and view changes replace #list1, so collapse the new rows too.
            function collapseListingDetails($list) {
                $list.find('.content-panel').hide();
            }

            function loadListingResults(url) {
                var $list = $('#list1');
                $list.css('opacity', '0.5');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (html) {
                        $list.html(html);
                        collapseListingDetails($list);
                        if (window.history && window.history.pushState) {
                            window.history.pushState({listingAjax: true}, '', url);
                        }
                        refreshListingEqualHeight();
                    },
                    complete: function () {
                        $list.css('opacity', '1');
                    }
                });
            }

            function buildUrlWithViewType(viewType) {
                var url = new URL(window.location.href);
                url.searchParams.set('view_type', viewType);
                return url.pathname + url.search;
            }

            $('#property-filter-form').on('submit', function (event) {
                event.preventDefault();
                var $form = $(this);
                var action = $form.attr('action');
                var query = $form.serialize();
                var url = action + (action.indexOf('?') === -1 ? '?' : '&') + query;
                loadListingResults(url);
            });

            $(document).on('click', '.view-filter .view-type-link', function (event) {
                event.preventDefault();
                var viewType = $(this).data('view-type');
                if (!viewType) {
                    return;
                }
                $('#property-filter-form input[name="view_type"]').val(viewType);
                loadListingResults(buildUrlWithViewType(viewType));
            });

            $(document).on('click', '#list1 .pagination a', function (event) {
                event.preventDefault();
                var href = $(this).attr('href');
                if (href && href !== '#') {
                    loadListingResults(href);
                }
            });

            // Decorative star links with empty href would jump to top
            $(document).on('click', '#list1 a[href=""]', function (event) {
                event.preventDefault();
            });

            window.addEventListener('popstate', function (event) {
                if (event.state && event.state.listingAjax) {
                    loadListingResults(window.location.href);
                } else {
                    window.location.reload();
                }
            });
        })(jQuery);
    </script>
@endsection
