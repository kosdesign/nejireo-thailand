<?php
namespace Eghl\PaymentMethod\Controller\Index;

if (interface_exists("Magento\Framework\App\CsrfAwareActionInterface"))
    include __DIR__ . "/ResponseHandler.gte_m230.php";
else
    include __DIR__ . "/ResponseHandler.lt_m230.php";
?>