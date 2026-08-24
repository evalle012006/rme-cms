// JavaScript Document

equalheight = function (container) {

    var currentTallest = 0, currentRowStart = 0, rowDivs = new Array(), $el, topPosition = 0;
    $(container).each(function () {
        $el = $(this);
        $($el).height('auto')
        topPostion = $el.position().top;
        if (currentRowStart != topPostion) {
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].outerHeight(currentTallest);
            }
            rowDivs.length = 0; // empty the array
            currentRowStart = topPostion;
            currentTallest = $el.height();
            rowDivs.push($el);
        } else {
            rowDivs.push($el);
            currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
        }
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].height(currentTallest);
        }
    });
}


jQuery(document).ready(function ($) {

    $('.mainmenu li:has(ul)').addClass('parent');
    $('.review-dropdwn:has(ul)').addClass('parent');

    $('a.menulinks').click(function () {
        $(this).next('ul').slideToggle(250);
        $('body').toggleClass('mobile-open');
        $('.mainmenu li.parent ul').slideUp(250);
        $('.review-dropdwn.parent ul').slideUp(250);
        $('a.child-triggerm').removeClass('child-open');
        return false;
    });

     $('.mainmenu li.parent > a, .review-dropdwn.parent > a').after('<a class="child-triggerm"><span></span></a>');
    // $('.review-dropdwn.parent > ul').before('<a class="child-triggerm"><span><i class="fa fa-angle-down" aria-hidden="true"></i>\n</span></a>');

    $('.mainmenu a.child-triggerm').click(function () {
        $(this).parent().siblings('li').find('a.child-triggerm').removeClass('child-open');
        $(this).parent().siblings('li').find('ul').slideUp(250);
        $(this).next('ul').slideToggle(250);
        $(this).toggleClass('child-open');
        return false;
    });


    wrapsset();

    if (window.matchMedia('(max-width: 767px)').matches) {
        $('.header-main .header-btn').appendTo('.header-main .nav-menu ul.mainmenu');
        $('.header-top .contact-info-header > a').appendTo('.header-main .nav-menu ul.mainmenu');
        $('.mainmenu > a').appendTo('.header-main .header-btn');
        $('.header-main .header-btn > a').wrapAll('<div class="btn-wrap"></div>')
    }

    $('.header-btn .review-dropdwn a.child-triggerm').click(function () {
        $(this).next().slideToggle();
        $(this).toggleClass('child-open');
    });


    $('.blog-post-slider').slick({
        arrows: false,
        dots: false,
        slidesToShow: 3,
        slidesToScroll: 1,
        // centerMode:true,
        // centerPadding:'150px',
        autoplay: true,
        autospeed: 3000,
        responsive: [
            {
                breakpoint: 1460,
                settings: {
                    slidesToShow: 3,
                    arrows: false,
                    dots: false,
                    centerMode: false,
                }
            },
            {
                breakpoint: 991,
                settings: {
                    slidesToShow: 2,
                    arrows: false,
                    dots: false,
                    centerMode: false,
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1
                }
            }

            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });

    $('.popular-blogs-wrap .posts-wrap').slick({
        arrows: true,
        dots: true,
        slidesToShow: 2,
        slidesToScroll: 2,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false
                }
            }
        ]
    });

    $('.popular-blogs-wrap .posts-wrap .slick-prev').insertBefore('.popular-blogs-wrap .posts-wrap .slick-dots li:first-child');
    $('.popular-blogs-wrap .posts-wrap .slick-next').appendTo('.popular-blogs-wrap .posts-wrap .slick-dots');


    jQuery(document).ready(function ($) {
        equalheight('.recent-reviews-section .section-content .review-box-col');
    });

    $('.content-panel .show-less').click(function () {
        $(this).parent('.content-panel').slideUp(50);
        $(this).parent().parent().children(':first-child').show();
        setTimeout(() => {
            equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
            equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
        }, 50);
    })

    $('.more-details-section .show-more').click(function () {
        $(this).parent().closest('.content-panel').slideUp(50);
        console.log($(this).next());
        if ($(this).next().is(':hidden') == true) {
            $(this).hide();
            $(this).next().slideDown(50);
            setTimeout(() => {
                equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
                equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
                //equalheight('.content-panel .rating-wrapper-in .rating-wrap > p:first-child');
            }, 50);
        }
    });

    $(document).on('click', '.property-wise-reviews-single .show-more', function () {
        var contentPanel = $(this).parents('.property-wise-reviews-single').next().find('.content-panel');
        if (contentPanel.is(':hidden') == true) {
            contentPanel.slideDown(50);
        } else {
            contentPanel.slideUp(50);
        }
         setTimeout(() => {
            //equalheight('.content-panel .rating-wrapper-in .rating-wrap > p:first-child');
                equalheight('.property-wise-reviews-wrap-custom .rating-wrapper-in .rating-wrap p:first-child');
        }, 50);
    });

    $(document).on('click', '.review-detail-watch .show-btn', function () {

        $('.review-detail-watch .show-btn').removeClass('on');
        $('.content-panel').slideUp('normal');

        if ($(this).next().is(':hidden') == true) {
            $(this).addClass('on');
            $(this).next().slideDown('normal');
        }

    });

    $(document).on('mouseover', '.review-detail-watch .show-btn', function () {
        $(this).addClass('over');
    }).on('mouseout', '.review-detail-watch .show-btn', function () {
        $(this).removeClass('over');
    });
    $('.content-panel').hide();


    //property review slider
    $('.featured-review-wrap .img-slider-wrap').slick({
        arrows: true,
        dots: true,
        slidesToShow: 1,
        slidesToScroll: 1
    });

    // if (window.matchMedia('(max-width: 991px)').matches) {
    //     $('.contact-info-header ul').insertBefore('.header-top .logo-wrap');
    //     $('.header-top .container > ul').wrap('<div class="header-info"></div>');
    //     $('.header-main .header-btn').appendTo('.mainmenu');
    //     $('.header-main .nav-menu').appendTo('.contact-info-header');
    // }

    equalheight('.section-content .single-box .cat-content-wrap .content-wrap');
    equalheight('.cat-review-section .single-box .content-wrap');
    equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
    equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
    equalheight('.latest-property-post-section .latest-property-post-wrap .latest-property-single .property-detail');
    equalheight('.recent-reviews-section .section-content .review-box-col');
    equalheight('.recent-reviews-section .section-content-slider  .review-box-col .review-box-col-in');

    if (jQuery(window).width() < 767) {
        jQuery('.section-content-slider').slick({
            arrows: true,
            dots: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            responsive: [
            {
              breakpoint: 768,
              settings: {
                arrows: false,
                dots: true,
              }
            }
          ]
        });
    }

    /* 29-07-2020 */
    jQuery(document).on('click', '.view-rating', function () {
        const display = jQuery(this).parents('.single-box-height').find('.properties-rating-list').css('display');
        if (display === 'none') {
            jQuery(this).parents('.single-box-height').find('.properties-rating-list').slideDown(50);
        } else {
            jQuery(this).parents('.single-box-height').find('.properties-rating-list').slideUp(50);
        }
        setTimeout(() => {
            equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
            equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
        }, 50);
    });
    /*07/08/2020*/
    if ($(window).width() < 1201) {

        $('.cat-review-section .section-content').slick({
            arrows: false,
            dots: true,
            infinite: false,
            slidesToShow: 3,
            slidesToScroll: 1,
            // autoplay: true,
            // autospeed: 3000,
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 2,
                        arrows: false,
                        centerMode: false,
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });

        $('.review-step-wrap .review-step-scroll').slick({
            arrows: false,
            dots: true,
            infinite: false,
            slidesToShow: 3,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        arrows: false,
                        centerMode: false,
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                  breakpoint: 480,
                  settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                  }
                }
            ]
        });
    }
    //register sing-in-box wrap
    jQuery( ".sign-in-wrap .sing-in-box .join-comm-wrap" ).wrapInner( "<div class='login-wrap'></div>");

   if (jQuery(window).width() < 767 ) {
        jQuery('.advance-srch-drpdwn h6').on("click", function(){
            jQuery(this).next('.filter-dropdown').toggle();
        });
   }

});//document.ready end here

jQuery(window).load(function () {
    equalheight('.section-content .single-box .cat-content-wrap .content-wrap');
    equalheight('.cat-review-section .single-box .content-wrap');
    equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
    equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
    equalheight('.latest-property-post-section .latest-property-post-wrap .latest-property-single .property-detail');
    equalheight('.recent-reviews-section .section-content .review-box-col');
    equalheight('.recent-reviews-section .section-content-slider  .review-box-col .review-box-col-in');
});

jQuery(window).resize(function () {
    equalheight('.section-content .single-box .cat-content-wrap .content-wrap');
    equalheight('.cat-review-section .single-box .content-wrap');
    equalheight('.property-review-listing .review-box-wrap .box-listing .single-box .single-box-height');
    equalheight('.profile-right-part .my-properties .my-properties-list .single-box .single-box-height');
    equalheight('.latest-property-post-section .latest-property-post-wrap .latest-property-single .property-detail');
    equalheight('.recent-reviews-section .section-content .review-box-col');
    equalheight('.recent-reviews-section .section-content-slider  .review-box-col .review-box-col-in');
});


function wrapsset() {
    var windwidth = $(window).width();
    var containerwidth = $('.container').width();
    var setleft = (windwidth - containerwidth) / 2;
    $('.left-space').css('padding-left', setleft);
    $('.right-space').css('padding-right', setleft);
}

function readURL(input) {
    if (input.files && input.files[0]) {
        const fileType = input.files[0].type;
        const imageTypes = ['image/jpeg', 'image/png'];
        if (imageTypes.includes(fileType)) {
            var reader = new FileReader();
            reader.onload = function (e) {
                console.log(jQuery('#user-profile-image'));
                jQuery('#user-profile-image').attr({'src': e.target.result,});
                // jQuery('#user-profile-image').attr({'src': e.target.result, 'height': '150px', 'width': '200px'});
                jQuery('#session-image').hide();
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            input.value = '';
        }
    }
}

function readImageUrl(input,id) {
    if (input.files && input.files[0]) {
        const fileType = input.files[0].type;
        const imageTypes = ['image/jpeg', 'image/png'];
        if (imageTypes.includes(fileType)) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // jQuery('#advertise-image').attr({'src': e.target.result, 'height': '150px', 'width': '200px'});
                jQuery('#'+id).attr({'src': e.target.result, 'height': '150px', 'width': '200px'});
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            input.value = '';
        }
    }
}


function ratingClass(rate) {
    var rating = rate.toString();
    if (rating.includes('.0')) {
        return rating.replace('.0', '');
    } else {
        return rating.replace('.5', '-5');
    }
}

function roundOffRating(rating) {
    return Math.round((rating / 0.5) * 0.5).toFixed(1);
}
