php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy en_US th_TH -f
php bin/magento c:c
php bin/magento c:f
php bin/magento maintenance:disable
