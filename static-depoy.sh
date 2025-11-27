#!/bin/sh

set -e
rm -rf pub/static/frontend/Hanshin
rm -rf var/view_preprocessed/pub/static/frontend/*
# # php -d set_time_limit=3600 -d memory_limit=-1 bin/magento setup:static-content:deploy --theme Aware/siamtv th_TH -f

# # php -d set_time_limit=3600 -d memory_limit=1024M bin/magento setup:static-content:deploy -f
php -d set_time_limit=3600 -d memory_limit=1024M bin/magento setup:static-content:deploy en_US th_TH -f
php bin/magento cache:clean
php bin/magento cache:flush
# bash ./permission.sh

DONE="Success static content deploy."
echo $DONE
