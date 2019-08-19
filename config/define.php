<?php
$env = getenv( 'ENV' );
try
{
    if ( isset( $env ) && $env === 'testing' )
    {
        define( "ServiceURL", 'https://payment-stage.ecpay.com.tw/SP/CreateTrade' );
        define( "sSPCheckOut_Url", 'https://payment-stage.ecpay.com.tw/SP/SPCheckOut' );
        define( "HashKey", '5294y06JbISpM5x9' );
        define( "HashIV", 'v77hoKGq4kWxNNIS' );
        define( "MerchantID", '2000132' );
    } else
    {
        $merchantId = getenv( 'MERCHANT_ID' );
        $hashKey = getenv( 'HASH_KEY' );
        $hashIv = getenv( 'HASH_IV' );

        if ( ! isset( $merchantId ) || empty( $merchantId ) ) throw new Exception( 'No MerchantId' );
        if ( ! isset( $hashKey ) || empty( $hashKey ) ) throw new Exception( 'No HashKey' );
        if ( ! isset( $hashIv ) || empty( $hashIv ) ) throw new Exception( 'No HashIv' );

        define( "ServiceURL", 'https://payment.ecpay.com.tw/SP/CreateTrade' );
        define( "sSPCheckOut_Url", 'https://payment.ecpay.com.tw/SP/SPCheckOut' );
        define( "MerchantID", $merchantId );
        define( "HashKey", $hashKey );
        define( "HashIV", $hashIv );
    }
} catch ( Exception $exception )
{
    echo '0|' . $exception;
}


define( "ReturnURL", 'https://quickpi-240207.appspot.com/ecpay_payment_receive' );