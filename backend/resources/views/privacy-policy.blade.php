@extends('layouts.app-layout')

@section('meta_title', 'Privacy Policy | ' . seoSiteName())
@section('meta_description', 'Read the ' . seoSiteName() . ' privacy policy to understand how we collect, use and protect your personal information.')

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{$sectionData['banner']['image'] ? checkUrl($sectionData['banner']['image']) : 'images/homepage/HomepageHero.jpg'}});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h1>{{$sectionData['banner']['title']}}</h1>
            @else
{{-- CMS fallback (kept for later restore)
<h1>Privacy Policy</h1>
            --}}
@endif
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Privacy Policy</span>
            </div>
        </div>
    </div>
    <div class="general-content-section custom-content-section">
        <div class="container">
            <div class="content-wrap">
                @if($sectionData['your_privacy']['title'])
                    <h4>{{$sectionData['your_privacy']['title']}}</h4>
                @else
{{-- CMS fallback (kept for later restore)
<h4>Our commitment to maintaining your privacy</h4>
                --}}
@endif

                @if($sectionData['your_privacy']['body'])
                    {!! $sectionData['your_privacy']['body'] !!}
                @else
{{-- CMS fallback (kept for later restore)
<p>Our Privacy Statement has been created we value our users and recognise their right to keep
                        Personal
                        Information private. Ratemyestate collects Personal Information and Personal Data from Site
                        visitors
                        to provide services to our users, and (as described below) for better customising our services
                        to
                        you. </p>
                    <p>Any Personal Information and Personal Data collected from you will be used by us in accordance
                        with
                        and as described in this Privacy Statement. This Privacy Statement has been prepared by
                        Ratemyestate. Ratemyestate owns and operates the website located at <a href="#">www.Ratemyestate.com.au</a>
                        (“the Site”) across desktop, mobile, tablet and apps and including any subdomains</p>
                --}}
@endif

                @if($sectionData['definitions']['title'])
                    <h4>{{$sectionData['definitions']['title']}}</h4>
                @else
{{-- CMS fallback (kept for later restore)
<h4>Definitions</h4>
                --}}
@endif

                @if($sectionData['definitions']['body'])
                    {!! $sectionData['definitions']['body'] !!}
                @else
{{-- CMS fallback (kept for later restore)
<p>Where used in this Privacy Statement, the terms “Personal Information”, and “Australian Privacy
                        Principle(s)” has the meaning attributed to those terms in the Privacy Act (Cth) 1988 (“Privacy
                        Act”).In this Privacy Statement:</p>
                    <ul>
                        <li>any reference to <strong>“Ratemyestate”, “we”, “us”</strong> and <strong>“our”</strong>
                            includes
                            a reference to Ratemyestate and each of Ratemyestate’s related bodies corporate (as that
                            term is
                            defined in the Corporations Act 2001 (Cth)).
                        </li>
                        <li><strong>"Advertiser"</strong> refers to individuals or entities that post ads on our Site
                        </li>
                        <li><strong>"Reviewer"</strong> refers to users of the Site and “Registered Users” refers to
                            reviewers who register on the Site;Reviewers, Advertisers and users who otherwise subscribe
                            for
                            Ratemyestate’s products, services or information offered via the Site are referred to
                            generically in this Statement as <strong>“visitors”, "users"</strong> or
                            <strong>"you"</strong>
                            unless the context provides that only Reviewers, Registered Users or Advertisers are being
                            referred to;
                        </li>
                        <li><strong>“GDPR”</strong> means the General Data Protection Regulation, being Regulation
                            2016/679
                            of the European Parliament and of the Council;
                        </li>
                        <li><strong>“Personal Data</strong> has the meaning attributed to that term in the GDPR;</li>
                    </ul>
                --}}
@endif

                @if($sectionData['collecting_info']['title'])
                    <h4>{{$sectionData['collecting_info']['title']}}</h4>
                @else
{{-- CMS fallback (kept for later restore)
<h4>Collecting other information on Ratemyestate visitors</h4>
                --}}
@endif

                @if($sectionData['collecting_info']['body'])
                    {!! $sectionData['collecting_info']['body'] !!}
                @else
{{-- CMS fallback (kept for later restore)
<p>Ratemyestate collects non-Personal Information on Site visits by users including, without
                        limitation,
                        which sections of the Site are most frequently visited, how often, and for how long. This data
                        is
                        always used as aggregated, non-Personal Information, and may be shared with Ratemyestate
                        partners to
                        provide them with information relating to how Ratemyestate visitors 'use' the Site.</p>
                    <h5>IP Addresses</h5>
                    <p>Ratemyestate’s web servers gather your IP address to assist with the diagnosis of problems or
                        support
                        issues with our services, and to monitor the use of our Site, including the monitoring of the
                        location of our users.</p>
                    <h5>Cookies</h5>
                    <p>Ratemyestate uses cookies and applets to provide Reviewers with a better browsing experience and
                        tailor advertisements from Ratemyestate or from our business partners that appear, (on the Site
                        or
                        other websites) to your browsing preferences and/or your Reviewer Profile, (as defined below).
                        These
                        cookies and applets collect non-Personal Information such as preferences and click activity for
                        measurement and for features such as remembering your details for 'Auto Login' (provided you
                        have
                        chosen this option). </p>
                    <p>Ratemyestate uses cookies and scripts in respect of Advertisers to assist Ratemyestate in
                        improving
                        website performance, making business decisions, aiding in error reporting, aiding online
                        payments
                        and testing new functionality. These cookies and scripts collect non-Personal Information such
                        as
                        preferences and click activity for measurement and for features such as remembering your details
                        for
                        'Auto Login' (provided you have chosen this option). </p>
                    <p>If you do not wish to have cookies active whilst you browse the Site you can "opt-out" by
                        modifying
                        the privacy options in your browser. However, doing so will prevent access to some areas of the
                        Site
                        and limit your use of some of the Ratemyestate services. </p>
                    <p>By opting out from ad targeting Ratemyestate and its third party providers will not use the
                        collected
                        information to target ads to you, however, the information collected may continue to be used by
                        Ratemyestate for, amongst other things, estimation of user numbers, advertising fraud detection
                        and
                        debugging. </p>
                    <h5>Third Party Measurement</h5>
                    <p>To better understand our users, Ratemyestate utilises third-party measurement companies to assess
                        Site and mobile Site traffic. A tracking code is used to collect the following information on
                        the
                        usage of the Site:</p>
                    <ul>
                        <li>The number of page views (or page impressions) that occur on the Site;</li>
                        <li>The number of unique visitors to the Site</li>
                        <li>How long these unique visitors (on average) spend on the Site when they do visit; and</li>
                        <li>Common entry and exit points into the Site</li>
                    </ul>
                --}}
@endif
            </div>
        </div>
    </div>

@endsection
