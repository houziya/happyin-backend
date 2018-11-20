<?php

class RateLimit
{
    public static function limit($key, $operation, $limit, $expire = 60)
    {
        $realKey = 'ratelimit$$' . $key . '$$' . $operation;
        $current = Yii::$app->redis->incr($realKey);
        Yii::$app->redis->expire($realKey, $expire);
        if ($current > $limit) {
            Protocol::tooManyRequest();
        }
    }
};

?>
