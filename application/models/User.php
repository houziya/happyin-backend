<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class UserModel
{
    public static function getAccountUid($token, $type)
    {
        $query = new Query;
        $query->select('customer_id') ->from(HI\TableName\CUSTOMER_CREDENTIAL) ->where(['token' => $token, 'type' => $type, 'enabled' => HI\User\ACCOUNT_NORMAL]);
        $registered = $query->one();
        return $registered['customer_id'];
    }

    public static function addUserData($user)
    {
        $connection = Yii::$app->db;
        $connection->createCommand()->insert(HI\TableName\CUSTOMER, [
            'firstname' => $user->firstname,
            'lastname' => $user->firstname,
            'avatar' => "default",
            'date_added' => date("Y-m-d H:i:s"),
            'status' => 1
        ])->execute();
        return $connection->getLastInsertID();
    }

    public static function addUserAccount($uid, $token, $type)
    {
        $connection = Yii::$app->db;
        $res = $connection->createCommand()->insert(HI\TableName\CUSTOMER_CREDENTIAL, [
            'customer_id' => $uid,
            'type' => $type,
            'token' => $token,
            'enabled' => HI\User\ACCOUNT_NORMAL,
        ])->execute();
        return $res;
    }

    public static function getUserData($uid)
    {
        $query = new Query;
        $query->select('customer_id, firstname, lastname, avatar, status') ->from(HI\TableName\CUSTOMER) ->where(['customer_id' => $uid]);
        $userData = $query->one();
        if (empty($userData)) {
            return false;
        }
        return $userData;
    }

    public static function getAddressData($uid)
    {
        $query = new Query;
        $query->select('address_id, firstname, address_1, city, address_2, custom_field, phone') 
            ->from(HI\TableName\ADDRESS) 
            ->where(['customer_id' => $uid])
            ->orderBy('create_time');
        $address = $query->all();
        $response = [];
        if (!$address) {
            return $response;
        }
        array_walk($address, function($data, $key) use(&$response) {
            $tmp = new stdClass();
            Accessor::wrap($data)->copyRequired(['address_id' => 'aid', 'firstname' => 'username', 'phone'], $tmp);
            $tmp->province['id'] = Yii::$app->redis->hget(HI\User\CITY_CODE, $data['address_1']);
            $tmp->province['name'] = $data['address_1'];
            $tmp->province['city']['id'] =  Yii::$app->redis->hget(HI\User\CITY_CODE, $data['city']);
            $tmp->province['city']['name'] = $data['city'];
            $tmp->province['city']['district']['id'] = Yii::$app->redis->hget(HI\User\CITY_CODE, $data['city'] . $data['address_2']);
            $tmp->province['city']['district']['name'] = $data['address_2'];
            $tmp->province['city']['district']['field']['name'] = $data['custom_field'];
            $response[] = $tmp;
        });
        return $response;
    }

    public static function getUserStatus($uid)
    {
        $query = new Query;
        $query->select('status') ->from(HI\TableName\CUSTOMER) ->where(['customer_id' => $uid]);
        $registered = $query->one();
        if (!$registered) {
            return false;
        }
        return intval($registered['status']);
    }

    public static function addAddress($data)
    {
        $connection = Yii::$app->db;
        $connection->createCommand()->insert(HI\TableName\ADDRESS, [
            'customer_id' => $data->requiredInt('login_uid'),
            'firstname' => urldecode($data->required('username')),
            'address_1' =>  Yii::$app->redis->hget(HI\User\CITY_CODE, $data->requiredInt('province')),    //省
            'city' =>  Yii::$app->redis->hget(HI\User\CITY_CODE, $data->requiredInt('city')),    //市
            'address_2' =>  self::doGetDistrict($data->requiredInt('district')),    //区或者县
            'custom_field' => urldecode($data->required('field')),    //详细信息
            'phone' => $data->required('phone'),
            'create_time' => date("Y-m-d H:i:s"),
        ])->execute();
        return $connection->getLastInsertID();
    }
    
    public static function doGetDistrict($value)
    {
        $district = Yii::$app->redis->hget(HI\User\CITY_CODE, $value);
        $newAddress = mb_substr($district, 0, -1);
        $last = mb_substr($district, -1);
        if (strstr($newAddress, '市')) {
            $tmp = explode('市', $district);
            if (substr_count($district, '市') == 2) {
                $tmp[1] = $tmp[1].$last;
            }
        } elseif (strstr($newAddress, '州')) {
            $tmp = explode('州', $district);
        } elseif (strstr($newAddress, '盟')) {
            $tmp = explode('盟', $district);
        } elseif (strstr($newAddress, '划')) {
            $tmp = explode('划', $district);
        } else {
            $tmp = explode('区', $district);
        }
        return $tmp[1];
    }

    public static function updateUserAddress($uid, $aid, $target, $value)
    {
        switch ($target) {
            case 'username':
                return self::doUpdateAddress($uid, $aid, 'firstname', $value);
                break;
            case 'province':
                return self::doUpdateAddress($uid, $aid, 'address_1', $value);
                break;
            case 'city':
                return self::doUpdateAddress($uid, $aid, $target, $value);
                break;
            case 'district':
                return self::doUpdateAddress($uid, $aid, 'address_2', $value);
                break;
            case 'field':
                return self::doUpdateAddress($uid, $aid, 'custom_field', $value);
                break;
            case 'phone':
                return self::doUpdateAddress($uid, $aid, $target, $value);
                break;
            default:
                throw new InvalidArgumentException('Invalid registration type '. $target);
        }
        return true;
    }

    private static function doUpdateAddress($uid, $aid, $target, $value)
    {
        $connection = Yii::$app->db;
        return $connection->createCommand()->update(HI\TableName\ADDRESS, [$target => $value], ['customer_id' => $uid, 'address_id' => $aid])->execute();
    }

    public static function updateUserSomeAddress($uid, $aid, $data)
    {
        $response = new stdClass();
        array_walk($data, function($value, $target) use (&$response){
            switch ($target) {
                case 'username':
                    $response->firstname = $value;
                    break;
                case 'province':
                    $response->address_1 = Yii::$app->redis->hget(HI\User\CITY_CODE, $value);
                    break;
                case 'city':
                    $response->$target = Yii::$app->redis->hget(HI\User\CITY_CODE, $value);
                    break;
                case 'district':
                    $response->address_2 = self::doGetDistrict($value);
                    break;
                case 'field':
                    $response->custom_field = $value;
                    break;
                case 'phone':
                    $response->$target = $value;
                    break;
                default:
                    throw new InvalidArgumentException('Invalid registration type '. $target);
            }
        });
        if ($response) {
            return self::doUpdateSomeAddress($uid, $aid, $response);
        }
        return true;
    }

    private static function doUpdateSomeAddress($uid, $aid, $data)
    {
        $connection = Yii::$app->db;
        return $connection->createCommand()->update(HI\TableName\ADDRESS, $data, ['customer_id' => $uid, 'address_id' => $aid])->execute();
    }

    public static function deleteAddress($uid, $target)
    {
        $connection = Yii::$app->db;
        return $connection->createCommand()->delete(HI\TableName\ADDRESS, ['customer_id' => $uid, 'address_id' => $target])->execute();
    }

    public static function updateProfile($uid, $target, $value)
    {
        switch ($target) {
            case 'nickname':
                return self::doUpdateUserData($uid, 'lastname', $value);
                break;
            case 'avatar':
                return self::doUpdateUserData($uid, $target, $value);
                break;
            default:
                throw new InvalidArgumentException('Invalid registration type '. $target);
        }
        return true;
    }

    private static function doUpdateUserData($uid, $target, $value)
    {
        $connection = Yii::$app->db;
        return $connection->createCommand()->update(HI\TableName\CUSTOMER, [$target => $value], ['customer_id' => $uid])->execute();
    }
    
    /* 分享用户查询  */
    public static function queryUserInfo($token)
    {
        $query = (new Query())->from(HI\TableName\THIRD_USER)
            ->where(['token' => $token])
            ->one();
        if (!$query) {
            return false;
        }
        return true;
    }
    
    /* 好友领过列表 */
    public static function assocQueryUser($code)
    {
        $select = 'u.token, u.name, u.gender, u.avatar, c.property, c.reduction, c.discount, us.add_time, c.coupon_id, c.name as cn';
        $query = (new Query())->select($select)
            ->from(CouponModel::$thirdUser.' as u ')
            ->innerJoin(CouponModel::$userShare.' as us', ' u.token = us.token')
            ->innerJoin(CouponModel::$shareCoupon.' as sc', ' us.share_id = sc.share_id')
            ->innerJoin(CouponModel::$coupon.' as c', ' c.coupon_id = us.assoc_code')
            ->where(['sc.code' => $code])
            ->orderBy('us.add_time desc')
            ->all();
        if (!$query) {
            return '';
        }
        array_walk($query, function (&$value, $key) {
            $discountData = Coupon::doGetResultByType($value['property'], $value['coupon_id']);
            $value['count'] = $discountData['count'];
            $value['unit'] = $discountData['unit'];
            $value['desc'] = $value['cn'];
            $value['avatar'] = HI\Config\QCloud\IMAGE\DOMAIN.'/profile/avatar/'.$value['avatar'].'.jpg';
            unset($value['property']);
            unset($value['reduction']);
            unset($value['discount']);
        });
        return $query;
    }

    /* 微信环境监测 是否领过 */
    public static function queryUserWhetherOver($ident, $token)
    {
        $select = 'c.coupon_id, c.property, c.reduction, c.discount, c.validity, c.use_type, c.use_start, c.use_end, us.phone, c.name';
        $query = (new Query())->select($select)->from(CouponModel::$userShare.' as us')
            ->leftJoin(CouponModel::$coupon.' as c', 'c.coupon_id = us.assoc_code')
            ->leftJoin(CouponModel::$shareCoupon.' as sc', 'us.share_id = sc.share_id')
            ->where(['us.token' => $token, 'sc.code' => $ident])
            ->one();
        if ($query) {
            return $query;
        }
        return false;
    }

    /* 查询优惠券领取次数  */
    public static function queryUserCouponNums($couponId)
    {
        $query = (new Query())->from(CouponModel::$coupon.' as c')
            ->leftJoin(CouponModel::$customerCoupon.' as cc', 'cc.coupon_id = c.coupon_id')
            ->where(['c.payload' => $couponId, 'c.status' => 1])
            ->count();
        $totalNums = CouponModel::queryOneTableInfo('*', CouponModel::$coupon, ['coupon_id' => $couponId]);
        /* 判断领取 */
        if ($totalNums['nums']) {
            if ($query >= $totalNums['nums']) {
                return false;
            }
        }
        /* 判断劵是否过期 */
        if ($totalNums['use_type'] == 0) {
            if (strtotime($totalNums['use_end']) < strtotime(date('Y-m-d'))) {
                return false;
            }
        }
        return $totalNums;
    }
    
    /* 查询本月单条数据 */
    public static function queryThisMonthShare($uid)
    {
        $query = (new Query())->select('date_added')
            ->from(CouponModel::$shareCoupon)
            ->where(['secret' => $uid, 'share_type' => 3, 'status' => 1])
            ->orderBy('date_added desc')
            ->one();
        if (!$query) {
            return false; //最新分享的链接
        }
        /* 最近一次分享  当前月份*/
        if (strtotime($query['date_added']) >= strtotime(date('Y-m'))) {
            return true; //当前月份
        }
        return NULL; //非当前月份
    }
    
    public static function getRegDeviceId($uid)
    {
        $query = new Query;
        $query->select('reg_device_id') ->from(HI\TableName\CUSTOMER_DEVICE) ->where(['customer_id' => $uid]);
        $regDeviceId = $query->one();
        if ($regDeviceId) {
            return $regDeviceId['reg_device_id'];
        } else {
            return false;
        }
        
    }
}