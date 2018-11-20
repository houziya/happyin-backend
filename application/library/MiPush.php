<?php
spl_autoload_register(function($class){
    $dir = dirname(__FILE__);
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    include($dir.DIRECTORY_SEPARATOR.$class);
});

use MiPush\IOSBuilder;
use MiPush\Sender;
use MiPush\Constants;
use MiPush\Stats;
use MiPush\Tracer;
use MiPush\Builder;
use MiPush\TargetedMessage;
class MiPush 
{
    const TO_EXPRESS = 0;  //物流
    const TO_COUPON = 1;  //优惠
    
    public static function sendMessage ($users, $payload, $desc, $title = '')
    {
        if (!is_array($users)) {
            return false;
        }
        foreach ($users as $uid) {
            $num = Yii::$app->redis->get(HI\MI_PUSH\COUNT.$uid);
            $num = $num ? $num++ : 1;
            //mipush计数
            Yii::$app->redis->set(HI\MI_PUSH\COUNT.$uid, $num, HI\User\MONTH);
            self::_ios([$uid], $payload, $desc, $num);
            self::_android([$uid], $payload, $desc, $title, $num);
        }
        return true;
    }
    
    private static function _ios ($aliasList, $payload, $desc, $num, $sound = 'default')
    {
        Constants::setBundleId(HI\MI_PUSH\IOS_BUNDLE_ID);
        Constants::setSecret(HI\MI_PUSH\IOS_SECRET);
        Constants::useOfficial();
        $message = new IOSBuilder();
        $message->description(self::emojiToUnicode($desc));
        $message->soundUrl('sms_circles.caf');
        //$message->badge('1');
        $message->badge($num);
        $message->extra('payload', json_encode($payload));
        $message->build();
        $sender = new Sender();
        $sender->sendToAliases($message,$aliasList)->getRaw();
    }
    
    private static function _android ($aliasList, $payload, $desc, $title = '', $num, $sound = 'default')
    {
        Constants::setPackage(HI\MI_PUSH\ANDROID_BUNDLE_ID);
        Constants::setSecret(HI\MI_PUSH\ANDROID_SECRET);
        Constants::useOfficial();
        $sender = new Sender();
        // message1 演示自定义的点击行为
        $message1 = new Builder();
        $message1->title($title);  // 通知栏的title
        $message1->description(self::emojiToUnicode($desc)); // 通知栏的descption
        $message1->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        $message1->payload(json_encode($payload)); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $message1->extra(Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
        $message1->notifyId(2); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message1->build();
        $targetMessage = new TargetedMessage();
        $targetMessage->setTarget('alias1', TargetedMessage::TARGET_TYPE_ALIAS); // 设置发送目标。可通过regID,alias和topic三种方式发送
        $targetMessage->setMessage($message1);
        $sender->sendToAliases($message1,$aliasList)->getRaw();
    }
 
    /* 提交rabbitMq Work */
    public static function submitWorks($payload) {
        AsyncTask::submit(HI\Config\MIPUSH_NODE, $payload);
    }
    
    /* 替换表情为Unicode */
    public static function emojiToUnicode($str) {
        return preg_replace_callback("/(:\\w+:)/", function ($matches) {
            if(!yii::$app->redis->exists(Us\Push\PUSH_EMOJI_JSON)){
                $jsonFile = APP_PATH . "/conf/document.json";
                $template = file_get_contents($jsonFile);
                yii::$app->redis->set(HI\Push\PUSH_EMOJI_JSON, $template);
                yii::$app->redis->expire(HI\Push\PUSH_EMOJI_JSON, 60*24);
            }else {
                $template = yii::$app->redis->get(HI\Push\PUSH_EMOJI_JSON);
            }
            $template = json_decode($template, true);
            if(isset($template[$matches[0]])) {
                return ($template[$matches[0]]);
            }else {
                return ($matches[0]);
            }
        }, $str);
    }
}
