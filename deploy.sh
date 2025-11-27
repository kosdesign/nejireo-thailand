#composer update
#composer install
#chmod +x bin/magento

php bin/magento maintenance:enable 
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy en_US th_TH -f
bin/magento c:c
bin/magento c:f
php bin/magento maintenance:disable

#rm -f app/etc/env.php
#rm -f index.php
#rm -f .htaccess
#echo "Move sites updated code to production site"
#rsync -e "ssh -o StrictHostKeyChecking=no -p 22" -avz ./ nejireo@188.166.178.104:/home/master/applications/xsvpsyrkjq/public_html/
#ssh -o StrictHostKeyChecking=no -p 22 nejireo@188.166.178.104 'bash /home/master/applications/xsvpsyrkjq/public_html/production.sh'
