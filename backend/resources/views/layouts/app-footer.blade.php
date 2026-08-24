<footer class="footer-section">
    <div class="top-footer">
        <div class="container">
            <div class="footer-left-part">
                <div class="about-col footer-col">
                    <h6>About us</h6>
                    <p>Ratemyestate is a review platform developed to create a community that allows buyers to tell their story and have it heard by other people and businesses being reviewed and help others make better decisions.</p>
                    <a class="btn btn-bordered white" href="{{route('about-page')}}">Read More</a>
                </div>
                <div class="category-col footer-col">
                    <h6>Categories</h6>
                    <ul>
                        @foreach(getPropertyCategory() as $cat)
                            <li><a href="{{route('property-listing-with-slug',['slug'=>$cat['slug']])}}">{{$cat['name']}}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="quick-links-col footer-col">
                    <h6>Quick Links</h6>
                    <ul>
                        <li><a href="{{route('property-guide')}}">Property Guide</a></li>
                        <li><a href="{{route('property-blog')}}">Property Blog</a></li>
                        <li><a href="{{route('review-guideline')}}">Review Guidelines</a></li>
                        <li><a href="{{route('terms-of-use')}}">Terms Of Use</a></li>
                        <li><a href="{{route('privacy-policy')}}">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-right-part">
                <div class="contact-col footer-col">
                    <h6>Contact Us</h6>
                    <ul>
                        <li class="mail-wrap"><a href="mailto:admin@ratemyestate.com.au">admin@ratemyestate.com.au</a></li>
                    </ul>
                </div>
                <div class="follow-col footer-col">
                    <h6>Follow Us</h6>
                    <ul>
                        <li><a href="https://www.facebook.com/share/1Un93B9z23/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" aria-label="Rate My Estate on Facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                        <li><a href="https://www.instagram.com/ratemyestate.com.au/" target="_blank" rel="noopener noreferrer" aria-label="Rate My Estate on Instagram"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="btm-footer">
        <div class="container">
            <p>Copyright © 2020 All Rights Reserved By <a href="{{route('root')}}">ratemyestate</a></p>
        </div>
    </div>
</footer>
