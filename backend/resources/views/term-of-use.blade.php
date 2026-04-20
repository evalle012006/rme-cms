@extends('layouts.app-layout')

@section('content')
    <div class="bg-main inner-banner"
         style="background-image: url({{$sectionData['banner']['image'] ? checkUrl($sectionData['banner']['image']) : 'images/homepage/HomepageHero.jpg'}});">
        <div class="banner-caption">
            @if($sectionData['banner']['title'])
                <h2>{{$sectionData['banner']['title']}}</h2>
            @else
                <h2>Terms Of Use</h2>
            @endif
            <div class="breadcrumb-wrap">
                <a href="{{route('root')}}">Home</a> / <span>Terms Of Use</span>
            </div>
        </div>
    </div>
    <div class="general-content-section custom-content-section">
        <div class="container">
            <div class="content-wrap">

                @if($sectionData['general']['title'])
                    <h4>{{$sectionData['general']['title']}}</h4>
                @else
                    <h4>General</h4>
                @endif

                @if($sectionData['general']['body'])
                    {!! $sectionData['general']['body'] !!}
                @else
                    <p>Users of ratemyestate.com.au (the Site), agree to be bound by these Terms and Conditions and any
                        other notices and guidelines which appear on the Site, which are subject to change without
                        notice,
                        from time to time in our sole discretion. Your use of and access to the Site indicates your
                        acceptance of these Terms and Conditions, as they exist at that time.</p>
                    <p>By using ratemyestate.com.au you agree that you will not take any of the following actions:</p>
                    <ul>
                        <li>Break our Community Guidelines when posting reviews and comments</li>
                        <li>Create more than one account on ratemyestate.com.au</li>
                        <li>Re-establish an account on ratemyestate.com.au after your account has been terminated.</li>
                        <li>Violate the intellectual property rights of a third party.</li>
                        <li>Affect the way ratemyestate.com.au displays its pages, such as by framing
                            ratemyestate.com.au or
                            placing pop-up windows over its pages.
                        </li>
                        <li>Use any automated means to access the Site or collect any information from the Site.</li>
                        <li>Modify or create variant versions of ratemyestate’s logos or other trademarks.</li>
                        <li>Illegal activity under the laws of Queensland and Australia.</li>
                    </ul>
                    <p>We have the right to take any of the following actions in our sole discretion at any time and for
                        any
                        reason without giving you any prior notice:</p>
                    <ul>
                        <li>Restrict, suspend, or terminate your access to all or any part of ratemyestate.com.au.</li>
                        <li>Change, suspend, or discontinue all or any part of the Site.</li>
                        <li>Reject, move, or remove any material that you submit to the Site for any reason.</li>
                        <li>Reject, move, or remove any content that is available on the Site.</li>
                        <li>Deactivate or delete your accounts and all related information and files in your account.
                        </li>
                    </ul>
                    <p>By joining ratemyestate.com.au you agree to the following:</p>
                    <ul>
                        <li>Comply with ratemyestate’s <strong>Terms of Use</strong> outlined above.</li>
                        <li>You agree not to disclose your password and your authentication token to any third party and
                            to
                            take sole responsibility for any activities or actions under your password and
                            authentication
                            token.
                        </li>
                        <li>Receive member email updates and newsletters, which can be cancelled at any time by updating
                            your member profile.
                        </li>
                    </ul>
                @endif


                @if($sectionData['copyright']['title'])
                    <h4>{{$sectionData['copyright']['title']}}</h4>
                @else
                    <h4>Copyright</h4>
                @endif

                @if($sectionData['copyright']['body'])
                    {!! $sectionData['copyright']['body'] !!}
                @else
                    <ul>
                        <li>Except for company trademarks, all material contained on ratemyestate.com.au, including,
                            text,
                            design, graphics, applications, software, underlying source code and the selection of
                            arrangements is the copyright of ratemyestate.com.au.
                        </li>
                        <li>In accessing the web pages on the Site, you agree that any downloading of material is for
                            personal, non-commercial use only. No part of the Site may be reproduced or transmitted in
                            any
                            form or by any means, electronic, mechanical, photocopying, recording or otherwise, without
                            prior permission of RateMyEstate Pty Ltd.
                        </li>
                        <li>It is permissible to send the URL of the Site to a third party, so that they can view the
                            material in its context.
                        </li>
                        <li>Upon submission of your review or any other content (“Content”) to ratemyestate.com.au, you
                            warrant that you are the author and owner of the Content and you grant to RateMyEstate Pty
                            Ltd,
                            a non-exclusive, perpetual, irrevocable, transferable, royalty-free license (including
                            rights to
                            sub-license) to use the Content (including, the right to publish, modify, delete in its
                            entirety, adapt, edit or sell the Content) in any media without restriction.
                        </li>
                        <li>You hereby warrant that all intellectual property rights that you may have in the Content,
                            have
                            been voluntarily waived by you and you agree not to assert any intellectual property rights
                            in
                            the Content.
                        </li>
                        <li>Breach of these terms may be contrary to the Copyright Act 1968 (Cth) and sections 52 and 53
                            of
                            the Trade Practices Act 1974 (Cth).
                        </li>
                    </ul>
                @endif

                @if($sectionData['disclaimer']['title'])
                    <h2>{{$sectionData['disclaimer']['title']}}</h2>
                @else
                    <h2>Disclaimer</h2>
                @endif

                @if($sectionData['disclaimer']['body'])
                    {!! $sectionData['disclaimer']['body'] !!}
                @else
                    <ol>
                        <li>The ratemyestate.com.au website is a public forum presenting user opinions on selected
                            products,
                            and as such the views expressed do not necessarily reflect the opinion of ratemyestate.
                        </li>
                        <li>By visiting and using the site, you agree to be bound to and abide by the following terms
                            and
                            conditions.
                        </li>
                        <li>Any disputes arising from, about or in connection with, the site are to be governed by the
                            laws
                            of the State of Queensland. By visiting and using the site you unconditionally submit to the
                            jurisdiction of the Courts of that State.
                        </li>
                        <li>The materials on the site have been prepared for general information purposes only. The
                            information on the site, may not necessarily be accurate, complete or current. No person
                            should
                            act or fail to act based on these materials.
                        </li>
                        <li>To the maximum extent permitted by law, RateMyEstate (including on behalf of its employees
                            and
                            agents) disclaims any and all liability (including liability for negligence) to any person
                            arising out of or in relation to the use of, or access to, the site, including:
                            <ul>
                                <li>any action or failure to act by any person in accessing, downloading, uploading,
                                    using,
                                    relying on or dealing in any way with, any materials from the site or from any other
                                    website on the Internet;
                                </li>
                                <li>any errors or omissions on the site or on any other website on the Internet
                                    (including
                                    but not limited to errors or omissions arising as a result of the negligence of
                                    RateMyEstate or its employees or agents);
                                </li>
                                <li>or any delay or interruption in access to or use of the site or any other website on
                                    the
                                    Internet (including but not limited to delay or interruption arising as a result of
                                    the
                                    negligence of RateMyEstate or its employees or agents).
                                </li>
                            </ul>
                        </li>
                        <li>
                            You accept all risk and responsibility for losses, damages, costs and other consequences
                            resulting directly or indirectly from using the site and any information or material
                            available
                            from the site.
                        </li>
                        <li>Whilst RateMyEstate reserves the right, in its absolute discretion, to delete, alter or move
                            any
                            message or other posted material on the site, it is under no obligation to do so, nor is it
                            under an obligation to update any information on the site, or to correct any inaccuracy on
                            the
                            site that may become apparent at a later time.
                        </li>
                        <li>Where RateMyEstate advertises on behalf of a person whose goods or services appear on the
                            site
                            (a "<strong>provider</strong>") or appears by reference of logo or otherwise in an
                            advertisement
                            of any provider, to the fullest extent permitted by law, such advertising or reference is
                            not to
                            be construed as:
                            <ul>
                                <li>an endorsement by RateMyEstate;</li>
                                <li>an acknowledgement or representation by RateMyEstate as to fitness for purpose;</li>
                                <li>or a recommendation or warranty by RateMyEstate of, for, or in relation to the
                                    product
                                    or service of the provider.
                                </li>
                            </ul>
                        </li>
                        <li>
                            Accordingly RateMyEstate, to the fullest extent allowed by law, neither takes nor assumes
                            any
                            responsibility for the product or service provided. Visitors to the site should rely on
                            their
                            own inquiries and seek any assurance or warranties directly from the provider of the service
                            or
                            product.
                        </li>
                        <li>RateMyEstate does not warrant the accuracy of any hypertext links provided on the site, nor
                            the
                            suitability or accuracy of any content located at those links. Links and frames connecting
                            the
                            site with other websites are for convenience only and do not mean that RateMyEstate is
                            associated with, endorses or approves those other websites, their content, or the people who
                            run, or contribute to, them. Use or reliance on the content of those websites is at the
                            user's
                            own risk.
                        </li>
                        <li>RateMyEstate does not represent or warrant that any files displayed or obtained from or
                            through
                            the site, or any other website linked to them, are free from computer viruses or other
                            defects.
                            Any such files are provided, and may only be accessed or used, on the basis that the user
                            assumes all responsibility for any loss, damage or consequence resulting directly or
                            indirectly
                            from the use of those files.
                        </li>
                        <li>If you feel that any information on the site has infringed your rights, please contact us.
                        </li>
                    </ol>
                @endif
            </div>
        </div>
    </div>
@endsection
