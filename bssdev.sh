php /home/v509hanshin/public_html/bin/magento maintenance:enable
php /home/v509hanshin/public_html/bin/magento app:config:import
php /home/v509hanshin/public_html/bin/magento setup:upgrade --keep-generated
php /home/v509hanshin/public_html/bin/magento cache:clean
php /home/v509hanshin/public_html/bin/magento maintenance:disable
echo "AddType application/x-httpd-ea-php71 .php .php7 .phtml" >> /home/v509hanshin/public_html/.htaccess
