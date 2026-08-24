            <div class="blogs-wrap">
                @foreach($blog_posts as $blogPost)
                    <div class="single-post-wrap">
                        <div class="img-wrap">
                            <img src="{{$blogPost->full_feature_image}}">
                        </div>
                        <div class="blog-detail">
                            <h4><a href="{{url('/blog-detail/'.$blogPost->slug)}}" tabindex="0">{{$blogPost->title}}</a>
                            </h4>
                            <p>{!! trimDescription($blogPost->description) !!}</p>
                            <a class="learn-more" href="{{url('/blog-detail/'.$blogPost->slug)}}" tabindex="0">Learn
                                More</a>
                        </div>
                    </div>
                @endforeach
            </div>
            {{$blog_posts->links()}}
