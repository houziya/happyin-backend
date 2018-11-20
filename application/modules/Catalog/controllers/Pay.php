<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\web\Request;

class PayController extends Controller_Abstract
{
    /*
     * 获取应用密钥
    */
    public function getWxAction()
    {
        $data = Protocol::arguments();
        //Auth::verifyDeviceStatus($data);
        $secretArr = array(
                "PartnerID"=> HI\Config\Pay\WX_PARTNER_ID,
                "PartnerKey"=> HI\Config\Pay\WX_PARTNER_KEY,
                "AppID"=> HI\Config\Pay\WX_APP_ID,
                //"AppSecret"=> HI\Config\Pay\WX_APP_SECRET,
                //"CallBack" => HI\APP_URL.HI\Config\Pay\WX_APP_CALLBACK,
        );
        Protocol::ok($secretArr);
    }
    
    /**
     * 支付宝
     */
    public function getAliAction()
    {
        $data = Protocol::arguments();
//         Auth::verifyDeviceStatus($data);
        $secretArr = array(
                "AliPay_PARTNER" => HI\Config\Pay\ALI_PARTNER_ID,
                "AliPay_SELLER" => HI\Config\Pay\ALI_PARTNER_SELLER,
                "AliPay_PRIVATE_KEY" => HI\Config\Pay\ALI_PRIVATE_KEY,
                "CallBack" => HI\APP_URL.HI\Config\Pay\ALI_APP_CALLBACK,
        );
        Protocol::ok($secretArr);
    }
    
    public function createWechatPayAction ()
    {
        $data = Protocol::arguments();
        //Auth::verifyDeviceStatus($data);
        $weixinPay = new WechatAppPay();
        $weixinPay->appid = HI\Config\Pay\WX_APP_ID;
        $weixinPay->appSecret = HI\Config\Pay\WX_APP_SECRET;
        $weixinPay->partnerId = HI\Config\Pay\WX_PARTNER_ID;
        $weixinPay->partnerKey = HI\Config\Pay\WX_PARTNER_KEY;
        $weixinPay->body = $data->required('body');
        $weixinPay->out_trade_no = $data->required('order_id');
        $weixinPay->total_fee = $data->required('total') * 100;
        $weixinPay->clent_ip = Protocol::remoteAddress();
        $weixinPay->notify_url = HI\APP_URL.HI\Config\Pay\WX_APP_CALLBACK;
        $result = $weixinPay->createAppPayData();
        $weixinPay->prepayId = $result['prepay_id'];
        $payData = $weixinPay->generatePayConfig();
        $response = [
                        'prepayId' => $result['prepay_id'],
                        'appId' => HI\Config\Pay\WX_APP_ID,
                        'partnerId' => HI\Config\Pay\WX_PARTNER_ID,
                        'noncestr' => $payData['nonce_str'],
                        'appKey' => HI\Config\Pay\WX_APP_SECRET,
                        'timestamp' => time(),
                        'sign' => $payData['sign'],
                    ];
        Protocol::ok($response);
    }
    
    /*
     * 微信支付服务端支付
    */
    public function wechatNotifyAction()
    {
        $data = [];
        $postStr = file_get_contents('php://input');
        $data = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        header('Content-Type: text/xml;');
        //签名校验
        if (!self::verifyWeChatNotify($data)) {
            self::displayXml();
        }
        $connection = Yii::$app->db;
        Execution::autoTransaction(Yii::$app->db, function() use($connection, $data) {
            $out_trade_no = $data['out_trade_no'];
            $orderWechatNotify = $connection->createCommand(
                "select count(*) from ".HI\TableName\ORDER_WECHATNOTIFY."
                where out_trade_no = '$out_trade_no' and (result_code = 'SUCCESS') "
            )->queryScalar();
            if ($orderWechatNotify > 0) {
                self::displayXml();
                exit;
            }
            //写入回调数据
            self::insertWechatNotify($connection, $data);
            if ($data['result_code'] == "SUCCESS") {
                //获取订单信息
                $order = self::getOrderInfo($connection, $out_trade_no);
                //校验支付金额,及对应商品
                if (!$order) {
                    //无此订单
                    self::displayXml();
                    exit;
                }
                if ($order['order_status_id'] == 2) {
                    //付过款
                    self::displayXml();
                    exit;
                }
                if (HI\Config\Pay\CHECK) {
                    if (number_format($order['total'], 2)*100 != $data['total_fee']) {
                        //金额有误
                        self::displayXml();
                    }
                }
                //更新订单状态
                self::updateOrderStatus($order['order_id']);
                Order::insertOrderLog($order['order_id'], ['status' => 2, 'status_desc' => "已付款"], $order['customer_id']);
                //减商品库存
                self::updateProductStock($connection, $order['order_id']);
                //更新优惠劵
                $coupons = self::getCoupon($connection, $order['order_id']);
                if ($coupons) {
                    foreach ($coupons as $coupon) {
                        self::updateConpon($order['customer_id'], $order['customer_id'], $coupon['coupon_id'], $order['total']);
                    }
                }
                self::displayXml();
            } else {
                self::displayXml('FAIL');
                exit;
            }
        });
        exit;
    }
    
    /*
     * ali支付服务端回调方法
    */
    public function aliNotifyAction()
    {
        //签名校验
        $aliPay = new AliPay();
        $aliPay->setPrivateKeyPath(__DIR__.'/../../../../conf/rsa_private_key.pem');
        $aliPay->setPublicKeyPath(__DIR__.'/../../../../conf/rsa_public_key.pem');
        $aliPay->setPartner(HI\Config\Pay\ALI_PARTNER_ID);
        if ($aliPay->verify()) {
            echo "fail";
            exit;
        }
        $data = Protocol::arguments();
        $connection = Yii::$app->db;
        Execution::autoTransaction(Yii::$app->db, function() use($connection, $data) {
            $out_trade_no = $data->required('out_trade_no'); 
            $orderAlinotify = $connection->createCommand(
                    "select count(*) from ".HI\TableName\ORDER_ALINOTIFY."
                     where out_trade_no = '$out_trade_no' and (trade_status = 'TRADE_FINISHED' or trade_status = 'TRADE_SUCCESS') "
            )->queryScalar();
            if ($orderAlinotify > 0) {
                echo "success";
                exit;
            }
            if ($data->required('trade_status') == "TRADE_SUCCESS" || $data->required('trade_status')=="TRADE_FINISHED") {
                //写入回调数据
                self::insertAliNotify($connection, $data);
                //获取订单信息
                $order = self::getOrderInfo($connection, $data->required('out_trade_no'));
                //校验支付金额,及对应商品
                if (!$order) {
                    //无此订单
                    echo "success";
                    exit;
                }
                if ($order['order_status_id'] == 2) {
                    //付过款
                    echo "success";
                    exit;
                }
                if (HI\Config\Pay\CHECK) {
                    if (number_format($order['total'], 2) != $data->required('total_fee')) {
                        //金额有误
                        echo "success";
                        exit;
                    }
                }
                //更新订单状态
                self::updateOrderStatus($order['order_id']);
                Order::insertOrderLog($order['order_id'], ['status' => 2, 'status_desc' => "已付款"], $order['customer_id']);
                //减商品库存
                self::updateProductStock($connection, $order['order_id']);
                //更新优惠劵
                $coupons = self::getCoupon($connection, $order['order_id']);
                if ($coupons) {
                    foreach ($coupons as $coupon) {
                        self::updateConpon($order['customer_id'], $order['customer_id'], $coupon['coupon_id'], $order['total']);
                    }
                }
                echo "success";
            } else {
                echo "fail";
            }
        });
       exit;
    }
    
    /**
     * 更新订单状态
     * @param unknown $order_id
     */
    private function updateOrderStatus ($order_id)
    {
         return OrderModel::doTableUpdate(HI\TableName\ORDER, ['order_status_id' => 2], ['order_id' => $order_id]);
    }
    
    /**
     * 获取优惠劵
     */
    private function getCoupon ($connection, $order_id)
    {
        $sqlOrderCoupon = "select * from ".HI\TableName\ORDER_COUPON." where order_id = '$order_id'";
        $OrderCoupon = $connection->createCommand($sqlOrderCoupon)->queryAll();
        return $OrderCoupon;
    }
    /**
     * 使用优惠劵
     */
    private function updateConpon ($orderId, $customerId, $couponId, $amount)
    {
        return CouponModel::afterCompleteChangeStatus($orderId, $customerId, $couponId, $amount);
    }
    
    /**
     * 写入ali回调表
     */
    private function insertAliNotify ($connection, $data)
    {
        return $connection->createCommand()->insert(HI\TableName\ORDER_ALINOTIFY, [
                'discount' => $data->optional('discount', ''),
                'payment_type' => $data->optional('payment_type', ''),
                'subject' => $data->optional('subject', ''),
                'trade_no' => $data->required('trade_no'),
                'buyer_email' => $data->optional('buyer_email', ''),
                'gmt_create' => $data->optional('gmt_create', ''),
                'notify_type' => $data->required('notify_type'),
                'quantity' => $data->required('quantity'),
                'out_trade_no' => $data->required('out_trade_no'),
                'seller_id' => $data->optional('seller_id', ''),
                'notify_time' => $data->required('notify_time'),
                'notify_id' => $data->required('notify_id'),
                'body' => $data->required('body'),
                'trade_status' =>$data->required('trade_status'),
                'is_total_fee_adjust' =>$data->optional('is_total_fee_adjust', ''),
                'total_fee' =>$data->required('total_fee'),
                'gmt_payment' => $data->optional('gmt_payment', ''),
                'seller_email' =>$data->required('seller_email'),
                'buyer_id' =>$data->required('buyer_id'),
                'price' => $data->required('price'),
                'use_coupon' =>$data->optional('use_coupon', ''),
                'sign' =>$data->required('sign'),
                'sign_type' =>$data->required('sign_type'),
                ])->execute();
    }
    
    /**
     * 写入wechat回调表
     */
    private function insertWechatNotify ($connection, $data)
    {
        return $connection->createCommand()->insert(HI\TableName\ORDER_WECHATNOTIFY, [
            'appid' => $data['appid'],
            'bank_type' => $data['bank_type'],
            'cash_fee' => $data['cash_fee'],
            'device_info' => @$data['device_info'] ? $data['device_info']:'',
            'fee_type' => @$data['fee_type'] ? $data['fee_type'] : '',
            'is_subscribe' => @$data['is_subscribe'] ? $data['is_subscribe'] : '',
            'mch_id' => $data['mch_id'],
            'nonce_str' => $data['nonce_str'],
            'openid' => $data['openid'],
            'out_trade_no' => $data['out_trade_no'],
            'result_code' => $data['result_code'],
            'return_code' => $data['return_code'],
            'sign' => $data['sign'],
            'time_end' => $data['time_end'],
            'total_fee' => $data['total_fee'],
            'trade_type' => $data['trade_type'],
            'transaction_id' => $data['transaction_id'],
        ])->execute();
    }
    /**
     * 获取订单信息
     */
    private function getOrderInfo ($connection, $orderId)
    {
        $sqlProduct = "select order_id, customer_id, total, order_status_id from ".HI\TableName\ORDER." where order_number = '$orderId'";
        $product_info = $connection->createCommand($sqlProduct)->queryOne();
        return $product_info;
    }
    
    /**
     * 微信签名校验
     */
    private function verifyWeChatNotify($data)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        ksort($data);
        $str = self::arrayToString($data);
        $str .= "&key=".HI\Config\Pay\WX_PARTNER_KEY;
        return strtoupper(md5($str)) == $sign;
    }
    
    /**
     * 数组转成字符串
     *
     * @param array $arr
     * @return string
     */
    private function arrayToString($arr)
    {
        $str = '';
        foreach($arr as $key => $value) {
            $str .= "{$key}={$value}&";
        }
        return substr($str, 0, strlen($str)-1);
    }
    
    private function displayXml ($status = 'SUCCESS')
    {
        echo "<xml>
               <return_code><![CDATA[$status]]></return_code>
             </xml>";
    }
    
    private function updateProductStock ($connection, $oder_id)
    {
        $sqlOrderProduct = "select op.product_id, op.quantity, p.isbn, os.code, os.order_child_id 
                from ".HI\TableName\ORDER_PRODUCT." as op 
                left join ".HI\TableName\PRODUCT." as p on p.product_id = op.product_id 
                left join ".HI\TableName\ORDER_SPLITTING." as os on os.order_child_id = op.order_child_id 
                where op.order_id = $oder_id";
        $orderProducts = $connection->createCommand($sqlOrderProduct)->queryAll();
        if ($orderProducts) {
            foreach ($orderProducts as $orderProduct) {
                if ($orderProduct['code'] == 0) {
                    if ($orderProduct['isbn']) {
                        $connection->createCommand(
                                "update ".HI\TableName\PRODUCT." set quantity = quantity - ".$orderProduct['quantity'].", quantity_sd = quantity_sd - ".$orderProduct['quantity']." where quantity >= ".$orderProduct['quantity']." and product_id = ".$orderProduct['isbn']
                        )->execute();
                    }
                    $connection->createCommand(
                            "update ".HI\TableName\PRODUCT." set quantity = quantity - ".$orderProduct['quantity'].", quantity_sd = quantity_sd - ".$orderProduct['quantity']." where quantity >= ".$orderProduct['quantity']." and product_id = ".$orderProduct['product_id']
                        )->execute();
                }
                if ($orderProduct['code'] == 1) {
                    if ($orderProduct['isbn']) {
                        $connection->createCommand(
                                "update ".HI\TableName\PRODUCT." set quantity = quantity - ".$orderProduct['quantity'].", quantity_zj = quantity_zj - ".$orderProduct['quantity']. " where quantity >= ".$orderProduct['quantity']." and product_id = ".$orderProduct['isbn']
                        )->execute();
                    }
                    $connection->createCommand(
                            "update ".HI\TableName\PRODUCT." set quantity = quantity - ".$orderProduct['quantity'].", quantity_zj = quantity_zj - ".$orderProduct['quantity']." where quantity >= ".$orderProduct['quantity']." and product_id = ".$orderProduct['product_id']
                    )->execute();
                }
                
                $sqlparts = "select pp.parts_id, pp.deduction_number from ".HI\TableName\PARTS_PRODUCT." as pp left join ".HI\TableName\PARTS." as p on p.parts_id = pp.parts_id where pp.product_id = ".$orderProduct['product_id'];
                $partsProducts = $connection->createCommand($sqlparts)->queryAll();
                if ($partsProducts) {
                    foreach ($partsProducts as $partsProduct) {
                        if (HI\Config\Product\FIVE_INCH == $orderProduct['product_id'] || HI\Config\Product\SIX_INCH == $orderProduct['product_id']) {
                            $quantity = $partsProduct['deduction_number'] * ceil(($orderProduct['quantity'] + 1)/HI\Config\Product\SET_UNIT);
                        } else {
                            $quantity = $partsProduct['deduction_number'] * $orderProduct['quantity'];
                        }
                        $connection->createCommand(
                                "update ".HI\TableName\PARTS." set quantity = quantity - ".$quantity." where quantity >= ".$quantity." and parts_id = ".$partsProduct['parts_id']
                        )->execute();
                    }
                }
            }
        }
        return true;
    }
}
