<?php
    // Execute the shell script to purge Varnish cache
    exec('/home/nejireo/public_html/pub/purge_cache.sh', $output, $return_var);

    if ($return_var === 0) {
        echo "Cache clearing triggered";
    } else {
        echo "Failed to trigger cache clearing";
    }
?>
