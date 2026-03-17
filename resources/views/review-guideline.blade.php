@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{$sectionData['banner']['image'] ? checkUrl($sectionData['banner']['image']) : 'images/homepage/HomepageHero.jpg'}});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h2>{{$sectionData['banner']['title']}}</h2>
            @else
                <h2>Review Guidelines</h2>
            @endif
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Review Guidelines</span>
            </div>
        </div>
    </div>
    <div class="general-content-section custom-content-section">
        <div class="container">
            <div class="content-wrap">
                @if($sectionData['reviews_on_rme']['title'])
                    <h4>{{$sectionData['reviews_on_rme']['title']}}</h4>
                @else
                    <h4>Property, Project & Builders reviews on ratemyestate</h4>
                @endif

                @if($sectionData['reviews_on_rme']['body'])
                    {!! $sectionData['reviews_on_rme']['body'] !!}
                @else
                    <p>Finding the right home is tedious, time-consuming and one of the biggest purchases of our lives.
                        So
                        why do it blindly?</p>
                    <p>That’s why we’ve created a community where you can find and share information about properties,
                        projects/ estates and builders across Australia written by past buyers.</p>
                    <p>This community allows you to purchase with confidence.</p>
                @endif

                @if($sectionData['guidelines']['title'])
                    <h4>{{$sectionData['guidelines']['title']}}</h4>
                @else
                    <h4>Our community guidelines</h4>
                @endif

                @if($sectionData['guidelines']['body'])
                    {!! $sectionData['guidelines']['body'] !!}
                @else
                    <p>To build a community of reviews in the spirit of helping others, all reviews and subsequent
                        comments
                        from the company must follow our guidelines.</p>
                    <ul>
                        <li>
                            <strong>Respectful</strong> - reviews should not contain profanity, threats, violent
                            references,
                            sexual or prejudiced or aggressive or hateful sentiments and any other comment or language
                            deemed inappropriate for our community. Similarly, any allegation of, or reference to,
                            racism or
                            illegal activity should be raised with the appropriate authorities and will not be
                            published.
                        </li>
                        <li>
                            <strong>Relevant</strong> - The purpose of your review is to help others guide the journey
                            of
                            building/ buying in Australia, so all content should be about the experience of building/
                            buying
                            at the specific company mentioned. Any content deemed to be off topic, such as generalised
                            discussion or relating to named customer service experiences of interactions with a company
                            will
                            not be published.
                        </li>
                        <li>
                            <strong>Balanced</strong> - We believe every experience will have both good points and areas
                            to
                            improve upon, therefore we encourage you to present a balanced view of your experience. We
                            want
                            to help those looking to build/ buy/discover what the journey is really like with a
                            particular
                            company by avoiding reviews which are artificially positive or deliberately damaging. We may
                            not
                            publish your review if it is deemed to be malicious, biased or suspicious.
                        </li>
                        <li>
                            <strong>Authentic</strong> - Each review has to be related to first-hand experiences for a
                            specific developer, project and/ or builder. Any review found to be fraudulent or duplicated
                            will not be published. All content should be original and not quoted from other sources such
                            as
                            (but not limited to) other websites, social media, email, other reviews etc.
                        </li>
                        <li>
                            <strong>Non-commercial</strong> - Any reviews promoting products and services of a company,
                            containing URLs, email links or telephone numbers, or deemed to have been incentivised to be
                            provided will not be published.
                        </li>
                    </ul>
                @endif

                @if($sectionData['reviews_dos']['title'])
                    <h4>{{$sectionData['reviews_dos']['title']}}</h4>
                @else
                    <h4>Review dos and don’ts</h4>
                @endif

                @if($sectionData['reviews_dos']['body'])
                    {!! $sectionData['reviews_dos']['body'] !!}
                @else
                    <ol>
                        <li>
                            Do not highlight or call out individuals for either praise or criticism It is acceptable to
                            reference your experience though generalised, in a respectful and factual manner.
                            <p>OK</p>
                            <p>"The customer service of the sales professional was really exceptional and took the time
                                to
                                answer all of my questions to put my concerns at ease.”</p>
                            <ul>
                                <li>“I felt that the sales professional wasn’t great at feeding all of the relevant
                                    information to me and just wanted me to sign the contract.”
                                </li>
                                <li>“The sales professional never had the time to answer any of my questions following
                                    signing the contract.”
                                </li>

                            </ul>
                            <p>Not OK</p>
                            <ul>
                                <li>“My sales professional John was really great to deal with, I would encourage to work
                                    with him if you could.”
                                </li>
                                <li>“Stefani was always difficult, wouldn’t take the time to solve my problems.”</li>
                            </ul>
                        </li>
                        <li>
                            Always be respectful
                            <p>Our community strives to be a safe and inclusive place to share honest opinions. We will
                                not
                                allow hateful, aggressive or racist sentiments or language to be published. Any
                                allegations
                                of discrimination or harassment should be raised with the appropriate authority.</p>
                            <p>OK</p>
                            <ul>
                                <li>“I really hated dealing with this business, I couldn’t wait to be in the property to
                                    not
                                    have to deal with them again”
                                </li>
                                <li>“This builder is terrible, they would not resolve my defects within sufficient time
                                    and
                                    would constantly delay fixing them.”
                                </li>
                            </ul>
                            <p>Not OK</p>
                            <ul>
                                <li>“If you’re a female and ask for a discount or free extras you’ll generally get
                                    them.”
                                </li>
                                <li>“If you’re a first home buyer and under 30 they will do anything to take advantage
                                    of
                                    you”
                                </li>
                            </ul>
                        </li>
                        <li>
                            Do not make allegations of illegal activity
                            <p>We make every attempt to validate the purchase of propertied though we can not validate
                                reviews for the accuracy or verify facts or the extent of any allegation. Any
                                allegations of
                                illegal activity should be raised with the appropriate authority.</p>
                            <p>OK</p>
                            <ul>
                                <li>“I think the values and culture in this company are questionable, there was a lot of
                                    politics and I felt people were being dishonest.”
                                </li>
                                <li>“We had challenges dealing with difficult staff, the number of complaints was very
                                    big.”
                                </li>
                            </ul>
                            <p>Not OK</p>
                            <ul>
                                <li>“Corruption by management is massive - they are all guilty of fraud.”</li>
                                <li>“Everyone is always stealing from the company, money goes missing most weeks.”</li>
                            </ul>
                        </li>
                    </ol>
                @endif

                @if($sectionData['moderation_works']['title'])
                    <h4>{{$sectionData['moderation_works']['title']}}</h4>
                @else
                    <h4>How does moderation work?</h4>
                @endif

                @if($sectionData['moderation_works']['body'])
                    {!! $sectionData['moderation_works']['body'] !!}
                @else
                    <p>We monitor all reviews to ensure that they meet our community guidelines and are helpful to
                        others.
                        We have a four-stage moderation process:</p>
                    <ol>
                        <li>Past or current buyer – people share their experience with companies they interact with. A
                            user
                            is required to upload a “proof of purchase” which validates their purchase.
                        </li>
                        <li>Human moderation – reviews are read by our moderation team and evaluated based on our
                            community
                            guidelines.
                        </li>
                        <li>Reporting a review – any user of our site can report a review as inappropriate, triggering a
                            re-evaluation.
                        </li>
                        <li>Verification- from time to time we may contact the relevant builder or developer to help
                            confirm
                            and validate your purchase.
                        </li>
                    </ol>
                    <p>
                        Should your review need moderation we will write to you asking for a review of our guidelines,
                        highlighting the inappropriateness and ask for to amend. Once resubmitted, this will trigger
                        re-evaluation.
                    </p>
                @endif

                @if($sectionData['wrote_a_review']['title'])
                    <h4>{{$sectionData['wrote_a_review']['title']}}</h4>
                @else
                    <h4>I wrote a review but I can't see it on the website - why not?</h4>
                @endif

                @if($sectionData['wrote_a_review']['body'])
                    {!! $sectionData['wrote_a_review']['body'] !!}
                @else
                    <p>If you wrote a review but can’t see it on the website, one of two things might have happened.</p>
                    <ul>
                        <li>We don’t yet have a page for the company you reviewed – it can take a little while to get
                            all
                            the information we need for each company’s page.
                        </li>
                        <li>There was an issue with your review – if your review did not follow our community guidelines
                            we
                            will not have been able to publish it.
                        </li>
                    </ul>
                    <p>ENDS</p>
                    <p>Version 1 . 25 February 2020.</p>
                @endif

            </div>
        </div>
    </div>
@endsection
