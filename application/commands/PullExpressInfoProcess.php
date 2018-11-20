<?php
use yii\db\Query;

/**
 * 主动拉去物流状态
 * @author yantao
 */
class PullExpressInfoProcessCommand
{
    public static function main ()
    {
        $connection = Yii::$app->db;
        $sqlFormat = "select order_child_id, shipping_id, splitting_company from ".HI\TableName\ORDER_SPLITTING." where order_child_id > %d and shipping_id != '' and status < 3 limit 200";
        $child_id = 0;
        $num = 0;
        while (true) {
            $sql = sprintf($sqlFormat, $child_id);
            $orderInfos = $connection->createCommand($sql)->queryAll();
            if (!$orderInfos) {
                break;
            }
            foreach ($orderInfos as $order) {
                if ($order['splitting_company'] == 'HTKY') {
                    $postData = ['parternID' => 'TESTJSON', 'serviceType' => 'RequestQuery', 'mailNo'=> $order['shipping_id']];
                    $result = json_decode(self::request_post($postData), true);
                    foreach ($result['traceLogs'] as $traceLog) {
                        $update = [];
                        foreach ($traceLog['traces'] as $trace) {
                            $update[] = [
                                'AcceptTime' => $trace['acceptTime'],
                                'AcceptStation' => $trace['acceptAddress'],
                                'Remark' => $trace['remark'],
                            ];
                            if ($trace['scanType'] == '签收') {
                                $status = 3;
                            } else {
                                $status = 2;
                            }
                            if (strpos(@$trace['acceptAddress'], '派送') !== false) {
                                $redisKey = 'HTKY_'.$traceLog['mailNo'];
                                if (Yii::$app->redis->get($redisKey) === false){
                                    $connection = Yii::$app->db;
                                    $sqlOrder = "select o.order_id, o.customer_id as uid from ".HI\TableName\ORDER_SPLITTING." as os
                                    inner join ".HI\TableName\ORDER." as o on o.order_id = os.order_id
                                    where os.shipping_id = ".$traceLog['mailNo'];
                                    $orderInfo = $connection->createCommand($sqlOrder)->queryOne();
                                    if ($orderInfo) {
                                        $payload = [
                                            'uid' => $orderInfo['uid'],
                                            'message' => '您的订单编号:'.$orderInfo['order_id'].'正在配送中,请注意查收',
                                            'type' => MiPush::TO_EXPRESS,
                                            'payload' => ['type' => 2, 'order_id' => $orderInfo['order_id']],
                                        ];
                                        MiPush::submitWorks($payload);
                                        Yii::$app->redis->set($redisKey, 1);
                                        Yii::$app->redis->expire($redisKey, 3600*12);
                                    }
                                }
                            }
                        }
                        OrderModel::doTableUpdate(HI\TableName\ORDER_SPLITTING, ['payload' => json_encode($update), 'status' => $status, ['shipping_id' => $traceLog['mailNo']]]);
                    }
                } else {
                    $child_id = $order['order_child_id'];
                    $express = new Express();
                    $expressInfo = $express->getInfo($order['splitting_company'], $order['shipping_id']);
                    $expressInfo = json_decode($expressInfo, true);
                    if (!$expressInfo) {
                        continue;
                    }
                    if (!@$expressInfo['LogisticCode'] or !@$expressInfo['State'] or !@$expressInfo['ShipperCode']) {
                        continue;
                    }
                    //更新物流信息
                    OrderModel::doTableUpdate(HI\TableName\ORDER_SPLITTING, ['payload' => json_encode($expressInfo['Traces']), 'status' => $expressInfo['State']], ['shipping_id' => $expressInfo['LogisticCode']]);
                    //调用物流通知队列
                    $redisKey = $expressInfo['ShipperCode'].'_'.$expressInfo['LogisticCode'];
                    if (Yii::$app->redis->get($redisKey) === false){
                        foreach ($expressInfo['Traces'] as $trace) {
                            if (strpos(@$trace['AcceptStation'], '派送') !== false) {
                                $connection = Yii::$app->db;
                                $sqlOrder = "select o.order_id, o.customer_id as uid from ".HI\TableName\ORDER_SPLITTING." as os
                                    inner join ".HI\TableName\ORDER." as o on o.order_id = os.order_id
                                    where os.shipping_id = ".$expressInfo['LogisticCode'];
                                $orderInfo = $connection->createCommand($sqlOrder)->queryOne();
                                if ($orderInfo) {
                                    $payload = [
                                    'uid' => $orderInfo['uid'],
                                    'message' => '您的订单编号:'.$orderInfo['order_id'].'正在配送中,请注意查收',
                                    'type' => MiPush::TO_EXPRESS,
                                    'payload' => ['type' => 2, 'order_id' => $orderInfo['order_id']],
                                    ];
                                    MiPush::submitWorks($payload);
                                    Yii::$app->redis->set($redisKey, 1);
                                    Yii::$app->redis->expire($redisKey, 3600*12);
                                }
                            }
                        }
                    }
                }
            }
        }
        echo "Success \n";
    }
    
    public function request_post($param)
    {
        if (empty($param)) {
            return false;
        }
        $postUrl = 'http://183.129.172.49/ems/api/process';
        $privateKey = '12345';
        $param['digest'] = base64_encode(md5($param['mailNo'].$privateKey, true));
        $o = "";
        foreach ($param as $k => $v ) {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $param = substr($o, 0, -1);
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
}