#!/bin/bash
curl -X PURGE -H "X-Magento-Tags-Pattern: .*" http://localhost:8081
/home/nejireo/public_html/bin/magento c:c
/home/nejireo/public_html/bin/magento c:f
