<?php
use yii\db\Query;

class PushMessageProcessCommand
{
    public static function main ()
    {
        AsyncTask::consume(HI\Config\MIPUSH_NODE, function($task){
            try {
                (new Query())->select('version()')->all();
            } catch (Exception $e) {
                Yii::$app->db->close();
                Yii::$app->db->open();
            }
            $subTask = $task->payload;
            if($subTask->type == MiPush::TO_EXPRESS) {
                //物流派送推送
                MiPush::sendMessage([$subTask->uid], $subTask->payload, $subTask->message, '快乐印');
            }elseif ($subTask->type == MiPush::TO_COUPON) {
                //领取优惠劵推送
                MiPush::sendMessage([$subTask->uid], $subTask->payload, $subTask->message, '快乐印');
            }
            return true;
        }, 2, 1024);
    }
}