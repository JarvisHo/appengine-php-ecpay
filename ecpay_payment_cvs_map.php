<?php
require __DIR__ . "/config/cors.php";
require __DIR__ . "/config/define.php";
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/helper.php";

if ( isset( $_POST[ 'MerchantID' ] ) && $_POST[ 'MerchantID' ] !== '' )
{
    $host = $_POST[ 'ExtraData' ];

    unset( $_POST[ 'ExtraData' ] );

    if(empty($host)) $host = 'https://shop.quick-pi.com';

    echo "<script>console.log('" . $host . "/map?" . getPayload( $_POST ) . "');setTimeout(function() { window.location='" . $host . "/map?" . getPayload( $_POST ) . "' },100) </script>";
}
