<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class Thirdparty
{
    private $impl;
    private $class;

    public function __construct($spec = "Taobao") {
        $this->class = $spec . "Api";
        $this->impl = new $this->class();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->impl, $this->class .  "::" . $name], $arguments);
    }
    
    public static function getTradeInfo($orderId)
    {
        $query = new Query;
        $order = $query->select(' o.order_number, o.shipping_country, o.shipping_firstname, o.shipping_city, o.date_added, o.date_modified, o.shipping_address_1, s.parcle')
            ->from(HI\TableName\ORDER ." as o")
            ->leftJoin(HI\TableName\ORDER_SPLITING ." as s", "o.order_id = s.order_id")
            ->where(['o.order_id' => $orderId, 's.code' => 0])->one(); //'o.order_status_id' => 15,
        $order['parcle'] = 'Desktop';
        //         $order = Accessor::wrap(OrderModel::doTableSelect("*", HI\TableName\ORDER, ["order_id" => $orderId, 'order_status_id' => 15]));
        $address = $order['shipping_country'] . $order['shipping_city'] . $order['shipping_address_1'];
        $trade = [
            "seller_nick" => "HappyIn",
            "pic_path" => "http://hipubdev-10006628.file.myqcloud.com/order/parcel/" . $order['parcle'] . '.zip',   //订单打包图片
            "payment" => 0,
            "seller_rate" => true,
            "post_fee" => 0,
            "consign_time" => "2000-01-01 00:00:00",
            "order_tax_fee" => 0,
            "tid" => $order['order_number'],
            "num" => 1,
            "num_iid" => 3424234,
            "status" => "WAIT_SELLER_SEND_GOODS",
            "title" => "HappyIn",
            "type" => "fixed(一口价)",
            "price" => 0,
            "discount_fee" => 0,
            "total_fee" => 0,
            "buyer_message" => "",
            "buyer_memo" => "",
            "seller_memo" => "",
            "buyer_nick" => $order['shipping_firstname'],
            "shipping_type" => "express",
            "service_tags" => ["logistics_tag" => ""],
            "adjust_fee" => 0,
            "buyer_rate" => true,
            "eticket_service_addr" => $address,
            "pay_time" => $order['date_added'],
            "created" => $order['date_added'],
            "modified" => $order['date_modified'],
            "end_time" => $order['date_modified']
        ];
//         [$order->date_added => "created", "date_added" => "pay_time", "date_modified" => "modified", "date_modified" => "end_time"
//         Accessor::copyOptional([ "created" => $order['date_added']], $trade);
        return $trade;
    }
}

?>
