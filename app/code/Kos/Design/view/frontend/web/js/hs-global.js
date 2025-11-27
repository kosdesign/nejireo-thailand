define([
    "jquery",
    "js/jquery.nicescroll",
    "js/bxslider/jquery.bxslider", 
], function($){
    ({
        init: function () {
            this.popup_toggle();
            this.top_cat_hover();
            this.cat_banner_slider();

            $(document).click(function (e) {
                if (e.target.class != 'card-item' && !$('.card-item').find(e.target).length) {
                    $(".sub-cat .subcat-list").hide();
                }
            });
        },
        popup_toggle: function () {
            $(".toggle-pop").click(function () {
                var t = $(this).attr("data-pop"),
                    i = $("#" + t);
                $(this).toggleClass("active"), i.each(function () {
                    "fade" == $(this).attr("data-action") ? $(this).fadeToggle() : "slide" == $(this).attr("data-action") && $(this).stop(!0, !0).slideToggle(), $(this).toggleClass("active")
                })
                return false;
            })
        },
        top_cat_hover: function () {
            $(".top-cat .card-item a").hover(
                function() {
                    $(".sub-cat .subcat-list").hide();
                    var str = $(this).attr('id');
                    /*console.log('hover = ' + str);*/
                    if (str) {
                        var res = str.split("_");
                        var id = res[2];
                        var data_right = 0;
                        var data_left = $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).parent().attr('data-left');
                        if (data_left == 0) {
                            var p = $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).parent().position();
                            $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).parent().attr('data-left', p.left);
                        }
                        var data_left = $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).parent().attr('data-left');
                        var position = $(this).parent().position();
                        data_right = parseFloat(data_left) - parseFloat(position.left);
                        $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).parent().css({'right': data_right + 'px','top': '-10px'});
                        $(".sub-cat .card-item .subcat-list#sub_top_cat_" + id).show();
                    }
                }
            );
            $(".sub-cat").hover(
                function() {
                    
                }, function() {
                    $(".sub-cat .subcat-list").hide();
                }
            );
        },
        cat_scroll: function () {
            if($(window).width() > 1024){
                $(".cat-scroll").niceScroll({
                    cursorwidth: "7px",
                    cursorcolor: "#E6552F",
                    background: "#F0F0F3" // change css for rail background
                });
            }

        },
        cat_banner_slider: function () {
            var self = this;
            var slider = $('.card-banner-slider>div');
            var item = slider.find('.card-item');
            if (item.length) {
                slider.bxSlider({
                    speed: 700,
                    pause: 9000,
                    mode: 'fade',
                    auto: true,
                    controls: false,
                    onSliderLoad: function (currentIndex) {
                        var h = $('.card-category-nav-list').height();
                        //$('.cat-scroll').css('max-height', h - 100);
                        self.cat_scroll();
                    }
                });
            }

        }
    }).init()
});