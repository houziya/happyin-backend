<?php
class Session
{
    private static function generateCryptoKey($uid)
    {
        $strong = true;
        return sha1(openssl_random_pseudo_bytes(16, $strong) . $uid);
    }

    private static function sessionKey($uid)
    {
        return HI\User\SESSION . $uid;
    }

    public static function generate($uid)
    {
        return self::generateCryptoKey($uid);
    }

    public static function reset($uid, $expire = 0)
    {
        $key = self::generate($uid);
        $result = false;
        for ($i=0; $i<10; $i++) {
            if ($result) {
            	break;
            }
            $result = Yii::$app->redis->set(self::sessionKey($uid), $key, $expire);
        }
        return $key;
    }

    public static function getSession($uid)
    {
    	return Yii::$app->redis->get(self::sessionKey($uid));
    }

    public static function devidKey($uid)
    {
        return HI\User\US_DEVID . $uid;
    }
    
    public static function verify($uid, $key)
    {
        return Predicates::equals(Yii::$app->redis->get(self::sessionKey($uid)), Preconditions::checkNotNull($key));
    }

    public static function resetDevid($uid, $value)
    {
        return Yii::$app->redis->set(self::devidKey($uid), $value);
    }
    
    public static function checkVerify($uid, $key)
    {
        if (!Predicates::equals(Yii::$app->redis->get(self::devidKey($uid)), Preconditions::checkNotNull($key))) {
            //根据用户id查询devid
            if (!UserModel::getRegDeviceId($uid)) {
                return false;
            } else {
                return self::resetDevid($uid, $key);
            }
        }
        return true;
    }
    
    public static function delete($uid)
    {
        return Yii::$app->redis->del(self::sessionKey($uid));
    }
}

?>
