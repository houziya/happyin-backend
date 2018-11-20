<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\db\Exception;

class ExpressController extends Controller_Abstract
{
    /**
     * 物流信息回调
     */
    public function receiveAction ()
    {
        $RequestData = json_decode($_REQUEST['RequestData'], true);
        if ($RequestData['EBusinessID'] != 1256461) {
            echo json_encode(['EBusinessID' => 1256461, 'UpdateTime' => date("Y/m/d H:i:s"), 'Success' => true, 'Reason' => '']);
            exit;
        }
        if ($RequestData['Count'] == 0) {
            echo json_encode(['EBusinessID' => 1256461, 'UpdateTime' => date("Y/m/d H:i:s"), 'Success' => true, 'Reason' => '']);
            exit;
        }
        foreach ($RequestData['Data'] as $data) {
            //更新物流信息
            OrderModel::doTableUpdate(HI\TableName\ORDER_SPLITTING, ['payload' => json_encode($data['Traces']), 'splitting_company' => $data['ShipperCode'], 'status' => $data['State']], ['shipping_id' => $data['LogisticCode']]);
            //调用物流通知队列
            $redisKey = $data['ShipperCode'].'_'.$data['LogisticCode'];
            if (Yii::$app->redis->get($redisKey) === false){
                if (strpos(@$data['AcceptStation'], '派件') !== false) {
                    $connection = Yii::$app->db;
                    $sql = "select o.order_id, o.customer_id as uid from ".HI\TableName\ORDER_SPLITTING." as os 
                            inner join ".HI\TableName\ORDER." as o on o.order_id = os.order_id 
                            where os.shipping_id = ".$data['LogisticCode'];
                    $orderInfo = $connection->createCommand($sql)->queryOne();
                    if ($orderInfo) {
                        $payload = [
                            'uid' => $orderInfo['uid'],
                            'message' => '您的订单编号:'.$orderInfo['order_id'].'正在配送中,请注意查收',
                            'type' => MiPush::TO_EXPRESS,
                        ];
                        MiPush::submitWorks($payload);
                        Yii::$app->redis->set($redisKey, 1);
                        Yii::$app->redis->expire($redisKey, 3600*12);
                    }
                }
            }
        }
        echo json_encode(['EBusinessID' => 1256461, 'UpdateTime' => date("Y/m/d H:i:s"), 'Success' => true, 'Reason' => '']);
        exit;
    }

    public function tracesAction ()
    {
        $data = Protocol::arguments();
        $connection = Yii::$app->db;
        $trace = [];
        $sql = "select os.order_child_id, os.shipping_id, os.payload, os.status, o.telephone, ec.company,
                o.shipping_firstname, o.shipping_address_1, o.shipping_country, o.shipping_city, o.date_added
                from ".HI\TableName\ORDER_SPLITTING." as os
                inner join ".HI\TableName\ORDER." as o on os.order_id = o.order_id
                inner join ".HI\TableName\EXPRESS_CODE." as ec on os.splitting_company = ec.code
                where os.order_child_id = ".$data->requiredInt('order_id');
        $trace = $connection->createCommand($sql)->queryOne();
        if (!$trace) {
            Protocol::ok();
            exit;
        }
        if ($trace['status'] == 3) {
            $trace['status'] = '已签收';
        } else {
            $trace['status'] = '在途中';
        }
        $payload = json_decode($trace['payload'], true);
        if (count($payload) > 0) {
            rsort($payload);
        }
        $trace['payload'] = $payload;
        Protocol::ok($trace);
    }
    
    public function subscribeAction ()
    {
        $express = new Express();
        $channel = 'SF';
        $orders = ['605558126332'];
        $a = $express->subscribe($channel, $orders);
        var_dump($a);
    }
    
    public function getOrderInfoAction ()
    {
        $express = new Express();
        $a = $express->getInfo('SF', '605558126332');
        var_dump($a);
    }
    
    public function pushAction ()
    {
        $orderInfo = ['uid'=>77, 'order_id' => 100];
        $payload = [
            'uid' => $orderInfo['uid'],
            'message' => '您的订单编号:'.$orderInfo['order_id'].'正在配送中,请注意查收',
            'type' => MiPush::TO_EXPRESS,
        ];
        var_dump($payload);
        MiPush::submitWorks($payload);
        echo 'aa';
    }
}
