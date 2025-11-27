php /home/master/applications/xsvpsyrkjq/public_html/bin/magento maintenance:enable
php /home/master/applications/xsvpsyrkjq/public_html/bin/magento app:config:import
php /home/master/applications/xsvpsyrkjq/public_html/bin/magento setup:upgrade --keep-generated
php /home/master/applications/xsvpsyrkjq/public_html/bin/magento setup:di:compile
php /home/master/applications/xsvpsyrkjq/public_html/bin/magento cache:flush
php /home/master/applications/xsvpsyrkjq/public_html/bin/magento maintenance:disable
echo "AddType application/x-httpd-ea-php71 .php .php7 .phtml" >> /home/master/applications/xsvpsyrkjq/public_html/.htaccess
