<?php

class OrderPaymentStatus {

    const UNPAID = 0;
    const PAID = 1;
    const REFUND = - 1;
    const CANCELLED = - 2;
}

class EcpayRtnCode {

    const PAID = '1';
}

/**
 * @param $db
 * @param $orderId
 */
function updatePaymentStatus( $db, $orderId )
{
    $sql = 'UPDATE `order` SET paymentStatus = :paymentStatus WHERE id = :id';
    $statement = $db->prepare( $sql );
    $statement->execute( [
        'paymentStatus' => OrderPaymentStatus::PAID,
        'id'            => $orderId,
    ] );
}

/**
 * @param $data
 * @return string
 */
function getPayload( array $data )
{
    $payload = '';
    foreach ( $data as $key => $value )
    {
        $payload .= $key . '=' . urlencode($value) . '&';
    }
    $payload = rtrim( $payload, "&" );

    return $payload;
}

/**
 * @param $db
 * @param $feedback
 * @return array
 */
function getOrderId( $db, $feedback )
{
    $sql = "SELECT * FROM `orderPayment` WHERE `customId` = :customId";
    $statement = $db->prepare( $sql );
    $statement->execute( [ 'customId' => ( isset( $feedback[ 'MerchantTradeNo' ] ) ? $feedback[ 'MerchantTradeNo' ] : '' ) ] );
    $row = $statement->fetch();

    return ( isset( $row[ 'orderId' ] ) ) ? $row[ 'orderId' ] : null;
}

/**
 * @param $db
 * @param $merchantTradeNo
 * @param $rtnCode
 * @param $payload
 * @return string
 */
function saveEcpayPaymentLog( $db, $merchantTradeNo, $rtnCode, $payload )
{
    $sql = 'INSERT INTO `ecpayPaymentLog` VALUES ( NULL, :merchantTradeNo, :rtnCode, :payload, :createdAt )';
    $statement = $db->prepare( $sql );
    $statement->execute( [
        'merchantTradeNo' => $merchantTradeNo,
        'rtnCode'         => $rtnCode,
        'payload'         => $payload,
        'createdAt'       => time() * 1000,
    ] );
}