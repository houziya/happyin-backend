<?php
use yii\db\Query;

class PackagePostProcessCommand
{
    public static function main ($argv)
    {
        $case = @$argv[1] ? $argv[1] : 1; 
        $origin = [
            1 => ['queue' => HI\Config\PackagePostSd, 'url' => HI\Config\PackagePostSdUrl],
            2 => ['queue' => HI\Config\PackagePostZj, 'url' => HI\Config\PackagePostZjUrl],
        ];
        AsyncTask::consume($origin[$case]['queue'], function($task) use($case, $origin){
            try {
                (new Query())->select('version()')->all();
            } catch (Exception $e) {
                Yii::$app->db->close();
                Yii::$app->db->open();
            }
            $orderId = $task->payload;
            $sql = "select o.order_id, o.order_number, o.telephone, o.shipping_firstname, o.shipping_country, o.shipping_city, o.shipping_zone, o.shipping_address_1, o.total, o.shipping_country_id, op.name, op.price, op.total, op.quantity, o.date_added, os.code, os.shipping_id, os.parcle, os.splitting_company, os.order_child_id from ".HI\TableName\ORDER." as o left join ".HI\TableName\ORDER_PRODUCT." as op on op.order_id = o.order_id left join ".HI\TableName\ORDER_SPLITTING." as os on os.order_id = o.order_id where o.order_id = ".$orderId;
            $orderInfo = Yii::$app->db->createCommand($sql)->queryAll();
            $sdProduct = $hzProduct = [];
            foreach ($orderInfo as $order) {
                $info = [
                    'order_number' => $order['order_number'],
                    'shipping_firstname' => $order['shipping_firstname'],
                    'shipping_country' => $order['shipping_country'],
                    'shipping_city' => $order['shipping_city'],
                    'shipping_zone' => $order['shipping_zone'],
                    'total' => $order['total'],
                    'date_added' => $order['date_added'],
                    'shipping_id' => $order['shipping_id'],
                    'splitting_company' => $order['splitting_company'],
                    'parcle' => "http://".HI\Config\DOWNLOAD_DOMAIN."/order/parcel/".$order['parcle'].".zip",
                ];
                if ($order['code'] == 0) {
                    $sdProduct[] = ['name' => $order['name'], 'price' => $order['price'], 'quantity' => $order['quantity'], 'total' => $order['price'] * $order['quantity']];
                    $sqlNumber = "select order_numbering_id from ".HI\TableName\ORDER_NUMBERING_SD." where order_child_id = ".$order['order_child_id'];
                    $info['order_numbering'] = Yii::$app->db->createCommand($sqlNumber)->queryOne()['order_numbering_id'];
                }
                if ($order['code'] == 1) {
                    $hzProduct[] = ['name' => $order['name'], 'price' => $order['price'], 'quantity' => $order['quantity'], 'total' => $order['price'] * $order['quantity']];
                    $sqlNumber = "select order_numbering_id from ".HI\TableName\ORDER_NUMBERING_ZJ." where order_child_id = ".$order['order_child_id'];
                    $info['order_numbering'] = Yii::$app->db->createCommand($sqlNumber)->queryOne()['order_numbering_id'];
                }
          }
            switch ($case) {
                case 1:
                    if ($sdProduct) {
                        $info['products'] = $sdProduct;
                        $resultSd = false;
                        while (!json_decode($resultSd)) {
                            $resultSd = self::post($origin[$case]['url'], json_encode($info), $timeout = 300);
                            sleep(3);
                        }
                    }
                    break;
                case 2:
                    if ($hzProduct) {
                        $info['products'] = $hzProduct;
                        $resultHz = false;
                        while (!json_decode($resultHz)) {
                            $resultHz = self::post($origin[$case]['url'], json_encode($info), $timeout = 300);
                            sleep(3);
                        }
                    }
                    break;
            }
            echo 'ok';
            return true;
        }, 2, 1024);
    }
    
    public static function post($url, $post_data = '', $timeout = 5){
    
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POST, 1);
        if($post_data != ''){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //严格认证
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    
    }
}
