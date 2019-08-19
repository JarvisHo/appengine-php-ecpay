<?php
require __DIR__ . "/config/define.php";
require __DIR__ . "/config/db.php";
require __DIR__ . "/config/helper.php";
require __DIR__ . '/ECPay.Payment.Integration.php';

try
{
    // 收到綠界科技的付款結果訊息，並判斷檢查碼是否相符
    $AL = new ECPay_AllInOne();
    $AL->MerchantID = MerchantID;
    $AL->HashKey = HashKey;
    $AL->HashIV = HashIV;
    $AL->EncryptType = ECPay_EncryptType::ENC_SHA256; // SHA256
    $feedback = $AL->CheckOutFeedback();

    if ( ! isset( $feedback[ 'MerchantTradeNo' ] ) || empty( $feedback[ 'MerchantTradeNo' ] ) ) throw new Exception( 'No MerchantTradeNo' );
    if ( ! isset( $feedback[ 'RtnCode' ] ) || empty( $feedback[ 'RtnCode' ] ) ) throw new Exception( 'No RtnCode' );

    saveEcpayPaymentLog( $db, $feedback[ 'MerchantTradeNo' ], $feedback[ 'RtnCode' ], getPayload( $feedback ) );

    if ( (string) $feedback[ 'RtnCode' ] === EcpayRtnCode::PAID )
    {
        $orderId = getOrderId( $db, $feedback );
        if ( $orderId === null || ! is_numeric( $orderId ) ) throw new Exception( 'No order' );
        updatePaymentStatus( $db, $orderId );
    }

    // 以付款結果訊息進行相對應的處理
    /**
     * 回傳的綠界科技的付款結果訊息如下:
     * Array
     * (
     * [MerchantID] =>
     * [MerchantTradeNo] =>
     * [StoreID] =>
     * [RtnCode] =>
     * [RtnMsg] =>
     * [TradeNo] =>
     * [TradeAmt] =>
     * [PaymentDate] =>
     * [PaymentType] =>
     * [PaymentTypeChargeFee] =>
     * [TradeDate] =>
     * [SimulatePaid] =>
     * [CustomField1] =>
     * [CustomField2] =>
     * [CustomField3] =>
     * [CustomField4] =>
     * [CheckMacValue] =>
     * )
     */
    // 在網頁端回應 1|OK
    echo '1|OK';
} catch ( Exception $e )
{
    echo '0|' . $e->getMessage();
}