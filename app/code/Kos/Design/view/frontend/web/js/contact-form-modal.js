define([
    "jquery",
    "Magento_Ui/js/modal/modal"
], function($){
    var ContactFormModal = {
        initModal: function(config, element) {
            $target = $(config.target);
            $target.modal();
            $element = $(element);
            $element.click(function() {
                $target.modal('openModal');
                $('.modal-footer').hide();
            });
            $target = $(config.target);
            $target.modal();
        }
    };
    return {
        'contact-form-modal': ContactFormModal.initModal
    };
}
);
