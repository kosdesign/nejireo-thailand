var config = {
    map: {
        '*': {
            sliders: 'Magento_Theme/js/component/sliders',
            global: 'Magento_Theme/js/component/global'
        }
    },
    deps: [
        'Magento_Theme/js/component/hs-responsive'
    ],
    config: {
        mixins: {
            'Magento_Theme/js/view/messages': {
                'Magento_Theme/js/messages-mixin': true
            }
        }
    }
};