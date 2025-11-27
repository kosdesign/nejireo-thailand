#composer update
#composer install
chmod +x bin/magento
bin/magento setup:di:compile
bin/magento setup:static-content:deploy en_US th_TH -f
rm -f app/etc/env.php
rm -f index.php
echo "Move sites updated code to staging site"
rsync -e "ssh -o StrictHostKeyChecking=no -p 22" -avz ./ neji@139.162.35.48:/srv/users/neji/apps/neji/public/
ssh -o StrictHostKeyChecking=no -p 22 neji@139.162.35.48 'bash /srv/users/neji/apps/neji/public/staging.sh'