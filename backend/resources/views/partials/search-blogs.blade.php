<div class="blog-content-wrap">
    @foreach($blogs as $blog)
        <div class="single-blog-col">
            <div class="single-blog-col-in">
                <div class="img-wrap">
                    <a href="{{url('/blog-detail/'.$blog['slug'])}}">
                        <img src="{{$blog['full_feature_image']}}" alt="blog">
                    </a>
                </div>
                <div class="content-wrap">
                    <h4>
                        <a href="{{url('/blog-detail/'.$blog['slug'])}}">{{trimTitle($blog['title'])}}</a>
                    </h4>
                    <div class="meta-wrap">
                        <ul>
                            <li class="calender">
                                <img src="{{url('images/calender-img.png')}}" alt="blog-image">
                                {{\Carbon\Carbon::parse($blog['created_at'])->format('d M, Y')}}
                            </li>
                        </ul>
                    </div>
                    <p>{!! trimDescription($blog['description']) !!}</p>
                    <a class="learn-more" href="{{url('/blog-detail/'.$blog['slug'])}}">Learn More</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@if(count($blogs)===0)
    <div>No Blogs found</div>
@endif
@if(count($blogs) < $totalBlogs)
    <button class="btn m-t-15" id="blog-show-more">
        Show More
    </button>
@endif
