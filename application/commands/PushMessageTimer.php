<?php
use yii\db\Query;

class PushMessageTimerCommand
{
    public static function main ()
    {
        //$orderPayed = DataBase::getTableDataRows('order', "order_status_id = :o_s_id", [':o_s_id' => 2], 'order_id');
        $orderPayed = (new Query())
            ->select("o.order_number, o.customer_id, l.*")
            ->from('order as o')
            ->rightJoin('order_log as l', "o.order_id = l.order_id")
            ->where(['o.order_status_id' => 2])
            ->all();
        if(Predicates::isNotEmpty($orderPayed)) {
            $orderPush = [];
            foreach ($orderPayed as $orderLog) {
                if(!in_array($orderLog['order_id'], $orderPush)) {
                    $payload = json_decode($orderLog['payload'], true);
                    if(($payload['status'] == 2) && (!isset($payload['push_count'])) && ((time() - strtotime($orderLog['date_added'])) >= 3*60*60)) {
                    //if($payload['status'] == 2) {
                        /* 付款超过三个小时,没上传照片,进行小米推送 */
                        $payloadPush = [
                            'uid' => $orderLog['customer_id'],
                            'message' => '您的订单照片上传未完成，请点击继续上传',
                            'type' => MiPush::TO_EXPRESS,
                            'payload' => new StdClass()
                        ];
                        MiPush::submitWorks($payloadPush);
                        $orderPush[] = $orderLog['order_id'];
                        $payload['push_count'] = 1;
                        DataBase::doTableUpdate('order_log', ['payload' => json_encode($payload)], ['id' => $orderLog['id']]);
                    }
                }
            }
        }
    }
}
?>