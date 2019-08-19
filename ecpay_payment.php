<?php
require __DIR__ . "/config/cors.php";
require __DIR__ . "/config/define.php";
require __DIR__ . '/ECPay.Payment.Integration.php';

/*
    程式撰寫流程(以信用卡為範例)
    0.參數定義
    1.讀取購物車商品
    2.寫入訂單，取得訂單編後
    3.透過站內付SDK 送出請求，並取得API回傳參數
    4.將API回傳參數往前端送
*/

try
{
    // 0.參數定義
    $aShopping_Cart = [];  // 購物車內資訊
    $aOrder_Info = [];  // 訂單資訊
    $aAjax_Return = [];  // 回傳給前端頁面資訊

    if ( ! isset( $_POST[ 'payment_type' ] ) || empty($_POST[ 'payment_type' ]) ) throw new Exception( 'No payment_type' );
    if ( ! isset( $_POST[ 'order_id' ] ) || empty($_POST[ 'order_id' ]) ) throw new Exception( 'No order_id' );
    if ( ! isset( $_POST[ 'order_amount' ] ) || empty($_POST[ 'order_amount' ]) ) throw new Exception( 'No order_amount' );

    $sPayment_Type = htmlspecialchars( trim( $_POST[ 'payment_type' ] ) );   // 付款方式: ATM
    $order_id = (string) $_POST[ 'order_id' ];
    $order_amount = (int) $_POST[ 'order_amount' ];

// 1. 讀取購物車商品(廠商自行撰寫)
    $aShopping_Cart = [];

// 2.寫入訂單，取得訂單編後(廠商自行撰寫)
    if ( true )
    {
        $aOrder_Info = [];
        $aOrder_Info = $aShopping_Cart;

        $aOrder_Info[ 'order_id' ] = $order_id;
        $aOrder_Info[ 'order_amount' ] = $order_amount;
        $aOrder_Info[ 'Items' ] = [ 'Name' => "商品", 'Price' => (int) $order_amount, 'Currency' => "元", 'Quantity' => (int) "1" ];
    }

    // 3.透過站內付SDK 送出請求，並取得API回傳參數
    if ( true )
    {
        //載入SDK(路徑可依系統規劃自行調整)
        try
        {
            $obj = new ECPay_AllInOne();

            //服務參數
            $obj->ServiceURL = ServiceURL;    //服務位置
            $obj->HashKey = HashKey;          //測試用Hashkey，請自行帶入ECPay提供的HashKey
            $obj->HashIV = HashIV;            //測試用HashIV，請自行帶入ECPay提供的HashIV
            $obj->MerchantID = MerchantID;    //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $obj->EncryptType = '1';          //CheckMacValue加密類型，請固定填入1，使用SHA256加密

            //基本參數(請依系統規劃自行調整)
            $obj->Send[ 'ReturnURL' ] = ReturnURL;  //付款完成通知回傳的網址
            $obj->Send[ 'MerchantTradeNo' ] = $aOrder_Info[ 'order_id' ];  //訂單編號
            $obj->Send[ 'MerchantTradeDate' ] = date( 'Y/m/d H:i:s' );                                      //交易時間
            $obj->Send[ 'TotalAmount' ] = $aOrder_Info[ 'order_amount' ];  //交易金額
            $obj->Send[ 'TradeDesc' ] = "無";   //交易描述
            $obj->Send[ 'ChoosePayment' ] = ECPay_PaymentMethod::ALL;  //付款方式:全功能
            $obj->Send[ 'NeedExtraPaidInfo' ] = 'Y';

            // //訂單的商品資料
            array_push( $obj->Send[ 'Items' ], $aOrder_Info[ 'Items' ] );

            if ( $sPayment_Type == 'ATM' )
            {
                // ATM 延伸參數
                $obj->SendExtend[ 'ExpireDate' ] = 10;                    //繳費期限 (預設3天，最長60天，最短1天)
                $obj->SendExtend[ 'PaymentInfoURL' ] = ReturnURL;                //伺服器端回傳付款相關資訊。
            }

            if ( $sPayment_Type == 'CVS' )
            {
                // CVS超商代碼延伸參數(可依系統需求選擇是否代入)
                $obj->SendExtend[ 'Desc_1' ] = '快拍直播購物電商'; //交易描述1 會顯示在超商繳費平台的螢幕上。預設空值
                $obj->SendExtend[ 'Desc_2' ] = '';               //交易描述2 會顯示在超商繳費平台的螢幕上。預設空值
                $obj->SendExtend[ 'Desc_3' ] = '';               //交易描述3 會顯示在超商繳費平台的螢幕上。預設空值
                $obj->SendExtend[ 'Desc_4' ] = '';               //交易描述4 會顯示在超商繳費平台的螢幕上。預設空值
                $obj->SendExtend[ 'PaymentInfoURL' ] = '';       //預設空值
                $obj->SendExtend[ 'ClientRedirectURL' ] = '';    //預設空值
                $obj->SendExtend[ 'StoreExpireDate' ] = '86400';     //預設空值 (以分鐘為單位)
            }

            //產生訂單(auto submit至ECPay)
            $aSdk_Return = $obj->CreateTrade();

            // 接回來的參數
            //var_dump($aSdk_Return);
            // exit;

            $aSdk_Return[ 'SPCheckOut' ] = sSPCheckOut_Url;

            if ( $sPayment_Type == 'CREDIT' )
            {
                $aSdk_Return[ 'PaymentType' ] = 'CREDIT';
            } else if ( $sPayment_Type == 'ATM' )
            {
                $aSdk_Return[ 'PaymentType' ] = 'ATM';
            } else if ( $sPayment_Type == 'CVS' )
            {
                $aSdk_Return[ 'PaymentType' ] = 'CVS';
            } else
            {
                $aSdk_Return[ 'PaymentType' ] = 'CREDIT';
            }


            $sAjax_Return = json_encode( $aSdk_Return );


        } catch ( Exception $e )
        {
            $aAjax_Return[ 'msg' ] = $e->getMessage();
            $sAjax_Return = json_encode( $aAjax_Return );
        }
    }

    // 4.將API回傳參數往前端送
    if ( ! empty( $sAjax_Return ) )
    {
        echo $sAjax_Return;
    }

} catch ( Exception $e )
{
    echo '0|' . $e->getMessage();
}
