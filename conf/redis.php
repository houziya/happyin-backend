<?php
return function ()
{
    $redis = new Redis();
    $redis->connect(HI\Config\Redis\HOSTNAME, HI\Config\Redis\PORT, HI\Config\Redis\TIMEOUT, NUll, HI\Config\Redis\RETRY_INTERVAL);
    $redis->auth(HI\Config\Redis\AUTH);
    return $redis;
}
?>
