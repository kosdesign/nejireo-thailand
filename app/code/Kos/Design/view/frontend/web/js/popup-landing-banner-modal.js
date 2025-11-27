define([
    "jquery", 
    "Magento_Ui/js/modal/modal",
    'jquery/jquery.cookie'
], function($){
    var ContactFormModal = {
        initModal: function(config, element) {
            var cookie_timeout = config.cookie_timeout;
            var date = new Date();
            var now = new Date();
            $target = $(config.target);
            $target.modal();
            var check_cookie = new Date($.cookie('landingBanner'));
            if(now >= check_cookie){
                var minutes = cookie_timeout;
                date.setTime(date.getTime() + (minutes * 60 * 1000));
                $.cookie('landingBanner', date);
                $("#modal-popup-landing-banner").modal("openModal");
                $("#modal-popup-landing-banner").show();
                var modal_inner_wrap = $("#modal-popup-landing-banner").parent().parent().attr('class');
                $('.' + modal_inner_wrap + ' footer.modal-footer button').hide();
                //$target.modal('openModal');
            }
            //console.log('date=' + date);
            //console.log('check_cookie=' + check_cookie);
            //console.log('minutes=' + cookie_timeout);
            //console.log('cookie=' + $.cookie('landingBanner'));
            $("#modal-popup-landing-banner-button").on('click',function(){ 
                $("#modal-popup-landing-banner").modal("openModal");
                var modal_inner_wrap = $("#modal-popup-landing-banner").parent().parent().attr('class');
                $('.' + modal_inner_wrap + ' footer.modal-footer button').hide();
            });
        }
    };
    return {
        'popup-landing-banner-modal': ContactFormModal.initModal
    };
}
);