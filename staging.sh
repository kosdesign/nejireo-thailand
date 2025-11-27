php7.3-sp /srv/users/neji/apps/neji/public/bin/magento maintenance:enable
php7.3-sp /srv/users/neji/apps/neji/public/bin/magento app:config:import
php7.3-sp /srv/users/neji/apps/neji/public/bin/magento setup:upgrade --keep-generated
php7.3-sp /srv/users/neji/apps/neji/public/bin/magento setup:di:compile
php7.3-sp /srv/users/neji/apps/neji/public/bin/magento cache:flush
php7.3-sp /srv/users/neji/apps/neji/public/bin/magento maintenance:disable
echo "AddType application/x-httpd-ea-php71 .php .php7 .phtml" >> /srv/users/neji/apps/neji/public/.htaccess
