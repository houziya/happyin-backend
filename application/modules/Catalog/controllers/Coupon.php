<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class CouponController extends Controller_Abstract
{
    /* 接口参数类型 */
    const ACTIVITY_ENDS = '活动结束';
    const ALREADY_OVER = '你已经领过了';
    const ALREADY_AFTER = '优惠劵已经被领完了';
    const WRONG_PASSWORD = '口令错误，你再想想';
    const WRONG_NUM_TEN = '输入错误超过10次，请稍后再试';
    const OTHER_GET_COUPON = '该优惠券已被领取';
    const COUNT_HOUR = 5;
    const NUM_LIMIT = 10;
    const LIMIT_IP = HI\Config\Coupon\LIMIT_IP;

    public static $redisTiming = HI\Coupon\COUPON_TIMING;
    public static $redisCount = HI\Coupon\COUPON_COUNT;

    /* 我的优惠劵 */
    public function myCouponAction()
    {
        $data = Protocol::arguments();
        $tmp['list'] = [];
        $flag = '';
        $message = '';
        $count = 0;
        Execution::autoTransaction(Yii::$app->db, function() use($data, $tmp, $flag, $count, $message) {
            /* 输入 code 领取优惠劵 */
            if ($data->optional('code')) {
                $limitNums = CouponModel::queryCouponInfoByCode($data->optional('code'));
                $getCoupons = CouponModel::queryUserAlreadyExistCoupon($data->requiredInt('login_uid'), $data->optional('code'));
                if (!$limitNums) {
                    if (Yii::$app->redis->get(self::$redisCount . $data->requiredInt('login_uid')) === false) {
                        Yii::$app->redis->set(self::$redisCount . $data->requiredInt('login_uid'), 0);
                    }
                    /* 输入错误计数  */
                    $num = Yii::$app->redis->incr(self::$redisCount . $data->requiredInt('login_uid'));
                    /* 连续输入错误十次  */
                    if (Predicates::equals($num, self::NUM_LIMIT)) {
                        Yii::$app->redis->set(self::$redisTiming . $data->requiredInt('login_uid'), (self::COUNT_HOUR * 60 * 60 + time()));
                        Yii::$app->redis->expire(self::$redisCount . $data->requiredInt('login_uid'), (self::COUNT_HOUR * 60 * 60 + time()));
                    } elseif ($num > self::NUM_LIMIT) {
                        /* 错误口令 >10 5小时内  */
                        if (Yii::$app->redis->get(self::$redisTiming . $data->requiredInt('login_uid')) > time()) {
                            Protocol::ok('', '', '', self::WRONG_NUM_TEN);
                            return;
                        }
                    }
                    /* 错误口令 <10 5小时内  */
                    Protocol::ok('', '', '', self::WRONG_PASSWORD);
                    return;
                } elseif (Yii::$app->redis->get(self::$redisTiming . $data->requiredInt('login_uid')) > time()) {
                    /* 连续输入错误口令 >10 5小时内  */
                    Protocol::ok('', '', '', self::WRONG_NUM_TEN);
                    return;
                } elseif (Predicates::equals($getCoupons, false)) {
                    /* 已经领过了 */
                    Protocol::ok('', '', '', self::ALREADY_OVER);
                    return;
                } elseif (Predicates::equals($limitNums, true) && Predicates::equals($getCoupons, true)) {
                    /* 已被领取 */
                    Protocol::ok('', '', '', self::OTHER_GET_COUPON);
                    return;
                }
                if (is_array($getCoupons)) {
                    $limitNums = $getCoupons;
                }

                /* 同IP限制 */
                $limitIp = Yii::$app->redis->get(date('Y-m-d') . '_' . Protocol::remoteAddress());
                if ($limitIp && $limitIp > self::LIMIT_IP) {
                    Protocol::ok('', '', '', self::WRONG_PASSWORD);
                    return;
                } else {
                    Yii::$app->redis->incr(date('Y-m-d') . '_' . Protocol::remoteAddress());
                    $ttl = Yii::$app->redis->ttl(date('Y-m-d') . '_' . Protocol::remoteAddress());
                    if ($ttl < 0) {
                        Yii::$app->redis->expire(date('Y-m-d') . '_' . Protocol::remoteAddress(), 60 * 60 * 24);
                    }
                }

                /* 10次内间隔输对一次合法code 计数结束  */
                if (Yii::$app->redis->get(self::$redisCount . $data->requiredInt('login_uid'))) {
                    Yii::$app->redis->del(self::$redisCount . $data->requiredInt('login_uid'));
                }

                /* code对应多张优惠劵  */
                foreach ($limitNums as $val) {
                    switch ($val['logged']) {
                        case 0: //领取渠道为普通
                        case 3: //领取渠道为分享
                            if (Yii::$app->redis->get(HI\Coupon\CHECK_NUMS) === false) {
                                Yii::$app->redis->set(HI\Coupon\CHECK_NUMS, 1);
                                $currentNums = CouponModel::queryCouponCollectTimes($val['coupon_id']);
                                if (strtotime($val['date_end']) < strtotime(date('Y-m-d'))) {
                                    /* 对应code的优惠劵 ：仅1张  */
                                    $message = self::ACTIVITY_ENDS;
                                    if (Predicates::equals(count($limitNums), 1)) {
                                        /* 领取超过活动期限  */
                                        Protocol::ok('', '', '', $message);
                                        return;
                                    }
                                } elseif ($val['nums'] <= $currentNums) {
                                    /* 未设置领取次数 直接可领取 */
                                    if($val['nums']) {
                                        $message = self::ALREADY_AFTER;
                                        if (Predicates::equals(count($limitNums), 1)) {
                                            /* 已经领完了 */
                                            Protocol::ok('', '', '', $message);
                                            return;
                                        }
                                    } else {
                                        $flag = true;
                                    }
                                } else {
                                    $flag = true;
                                }
                                /* 对应code的优惠劵：多张 且有无效优惠劵  */
                                if ($flag) {
                                    /* 优惠劵信息绑定用户入库  */
                                    CouponModel::couponAssocCustomer($val['coupon_id'],$data->requiredInt('login_uid'));
                                    if (CouponModel::queryOneTableInfo('assoc_code', CouponModel::$userShare, ['assoc_code' => $val['coupon_id'], 'status' => 0])) {
                                        Preconditions::checkArgument(DataBase::doTableUpdate(CouponModel::$userShare, ['status' => 1], ['assoc_code' => $val['coupon_id']]));
                                    }
                                    $count++;
                                }
                            } else {
                                $message = self::ALREADY_AFTER;
                            }
                            Yii::$app->redis->del(HI\Coupon\CHECK_NUMS);
                            break;
                        case 4: //领取渠道为打包优惠劵
                            if (Yii::$app->redis->LRANGE(HI\Coupon\AMOUNT . $val['coupon_id'], 0, -1)) {
                                    if (strtotime($val['date_end']) < strtotime(date('Y-m-d'))) {
                                        /* 领取超过活动期限  */
                                        $message = self::ACTIVITY_ENDS;
                                    } else {
                                        $flag = true;
                                        if ($val['property'] == 0) {
                                            $val['discount'] = Yii::$app->redis->LPOP(HI\Coupon\AMOUNT . $val['coupon_id']);
                                        } else {
                                            $val['reduction'] = Yii::$app->redis->LPOP(HI\Coupon\AMOUNT . $val['coupon_id']);
                                        }
                                    }
                            } else {
                                $message = self::ALREADY_AFTER;
                            }
                            if ($flag) {
                                $newCoupon = CouponModel::doGetNewCoupon($val, 1); //1.code 跟随
                                CouponModel::couponAssocCustomer($newCoupon['coupon_id'], $data->requiredInt('login_uid'));
                                $count++;
                            }
                        break;
                        default :
                            $message = self::ALREADY_AFTER;
                    }
                }
                /* 当前领取的优惠劵都有异常 */
                if (!$count) {
                    Protocol::ok('', '', '', $message);
                    return;
                }
            }
            /* 拉取最新的优惠劵列表 */
            $couponList = CouponModel::queryCouponList($data->requiredInt('login_uid'));
            if ($couponList) {
                $tmp = $this->doReturnCouponList($data, $couponList);
            }
            /* 返回数据 */
            $copyWriter = empty($count) ? '' : "恭喜你获得".$count.'张优惠劵';
            /* 获取用户最新的订单ID (戳红包用) */
            $tmp['newest_order'] = CouponModel::queryNewestOrderId($data->requiredInt('login_uid'));
            $tmp['share'] = Order::orderShareInfo(Order::ORDER_SHARE, $tmp['newest_order']);
            $tmp['shareApp_limit'] = Order::$appShareChannelLimit;
            $tmp['shareFree_limit'] = Order::$shareFreeChannelLimit;
            /* 用户vip状态 */
            $vipStatus = CouponModel::queryOneTableInfo('approved', HI\TableName\CUSTOMER, ['customer_id' => $data->requiredInt('login_uid')]);
            $tmp['vip_status'] = empty($vipStatus['approved']) ? false : true;
            $tmp['receive_state'] = Predicates::isNull(UserModel::queryThisMonthShare($data->requiredInt('login_uid'))) ? true : false;
            Protocol::ok($tmp, '', '', $copyWriter);
        });
    }

    /* 根据类型 返回优惠劵 */
    private function doReturnCouponList($data, $couponList)
    {
        /* 过滤总金额 （暂时后台未设置） */
        $filterCoupon = Coupon::filterCoupon($data->optional('products'), $data->requiredInt('login_uid'), $couponList);
        /* 初始化优惠劵列表  */
        $tmp = Coupon::initCouponList($couponList, $filterCoupon, $data); 
        switch ($data->requiredInt('type')) {
            case CouponModel::COUPON_LIST :
                /* 排序  */
                return Coupon::doSortCouponList(Coupon::doGetSubCouponList($tmp));
                break;
            case CouponModel::COUPON_AVAIALABLE :
                /* 商品为空时 返回不可用列表  */
                if (!$data->optional('products')) {
                    return Coupon::doSortCouponList(Coupon::doGetSubCouponList($tmp));
                }
                /* 过滤不适用的商品  */
                $list = Coupon::doGetSubCouponList(Coupon::doFilterFailedState($data->optional('products'), $tmp, 1));
                /* 排序  */
                return Coupon::doSortCouponList($list);
                break;
            default :
                Protocol::badRequest(NULL, NULL, $type.' does not exist '.$type);
        }
    }

    public function demoAction()
    {
        echo '<pre>';
        $data = Protocol::arguments();
        var_dump(Coupon::returnBestCouponList($data, $data->optional('products')));
    }
}
