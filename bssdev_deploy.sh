#composer update
#composer install
chmod +x bin/magento
bin/magento setup:di:compile
bin/magento setup:static-content:deploy en_US -f
rm -f app/etc/env.php
rm -f index.php
echo "Move sites updated code to staging site"
rsync -e "ssh -o StrictHostKeyChecking=no -p 2222" -avz ./ v509hanshin@139.162.31.74:/home/v509hanshin/public_html/
ssh -o StrictHostKeyChecking=no -p 2222 v509hanshin@139.162.31.74 'bash /home/v509hanshin/public_html/bssdev.sh'