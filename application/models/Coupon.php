<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class CouponModel
{
    const PRODUCT_PREFIX = '适用商品: ';
    const PRODUCT_SHELVES_DESC = '商品已下架';

    /* 优惠劵分类  */
    const FRAMES = 1;
    const PHOTOS = 2;
    const NOT_ABLE = 0;
    const NOT_ABLE_NAME = '不可用';
    /* lomo卡 照片卡单位 （特殊处理的优惠券）*/
    const LOMO_PHOTOS_UNIT = '一';

    const COUPON_LIST = 0;  // 0 我的所有优惠劵
    const COUPON_AVAIALABLE = 2;  //2 我的当前可用优惠劵
    
    /* 分享优惠劵 常量 */
    const ADMIN_SHARE_COUPON = 2;

    public static $coupon = HI\TableName\COUPON; //优惠劵表
    public static $couponProduct = HI\TableName\COUPON_PRODUCT; //优惠劵商品关联表
    public static $couponCategory = HI\TableName\COUPON_CATEGORY; //优惠劵分类关联表
    public static $couponHistory = HI\TableName\COUPON_HISTORY; //优惠劵历史表
    public static $customerCoupon = HI\TableName\CUSTOMER_COUPON; //优惠劵用户关联表
    public static $order = HI\TableName\ORDER; //订单表
    public static $orderProduct = HI\TableName\ORDER_PRODUCT; //订单商品关联表
    public static $productDesc = Hi\TableName\PRODUCT_DESCRIPTION; //商品描述表
    public static $product = Hi\TableName\PRODUCT; //商品表
    public static $productCatagory = HI\TableName\PRODUCT_TO_CATEGORY; //商品关联类别表
    public static $categoryDesc = HI\TableName\CATEGORY_DESCRIPTION; //类别描述表
    public static $category = HI\TableName\CATEGORY; //商品分类表
    public static $shareCoupon = HI\TableName\SHARE_COUPON; //分享优惠券表
    public static $userShare = HI\TableName\USER_TO_SHARE; //用户关联分享表
    public static $thirdUser = HI\TableName\THIRD_USER; //第三方用户表

    /* 特殊商品ID */
    public static $photoCards = HI\Config\Product\PHOTO_CARDS_PRODUCT_ID;
    public static $lomoCards = HI\Config\Product\LOMO_CARDS_PRODUCT_ID;

    /* 查询验证商品抵扣范围
     * productId : is_array return array
     * or return bool
     */
    public static function queryScopeReturnBool($couponId ,$productId)
    {
        $able = [];
        $query = (new Query())->select('product_id')
            ->from(self::$couponProduct)
            ->where(['coupon_id' => $couponId])
            ->all();
        if ($query) {
            /* IF-array */
            if (is_array($productId)) {
                array_walk($query, function($value, $key) use(&$productId, &$able){
                    if (in_array($value['product_id'], $productId)) {
                        $able[$key] = $value['product_id'];
                    }
                });
                return $able;
            }
            /* IF-string */
            foreach ($query as $val) {
                if (Predicates::equals(intval($val['product_id']), $productId)) {
                    return true;
                }
            }
        }
        return false;
    }

    /* 查询用户优惠券列表  */
    public static function queryCouponList($customerId)
    {
        $params = "cc.coupon_id, cc.type, c.payload, c.property, c.total, c.name, c.date_start as s, c.date_end as d, 
            c.date_added as a, cc.date_added as da, c.validity as v, c.use_type, c.use_start, c.use_end";
        $condition['cc.customer_id'] = $customerId;
        $condition['c.status'] = 1; //优惠劵可用状态
        $query = (new Query())->select($params)
            ->from(self::$customerCoupon.' as cc')
            ->innerJoin(self::$coupon.' as c', 'c.coupon_id = cc.coupon_id')
            ->where($condition)
            ->orderBy('d desc')
            ->all();
        if (!$query) {
            return false;
        }
        return $query;
    }

    /* 通过用户ID查询优惠券ID */
    public static function queryCouponByCustomerId($customerId)
    {
        $query = (new Query())->select('coupon_id')
            ->from(self::$customerCoupon)
            ->where(['customer_id' => $customerId])
            ->all();
        if (!$query) {
            return false;
        }
        return $query;
    }

    /* 查询能用的商品 */
    public static function queryAbleProduct($couponId, $type = NULL)
    {
        $selectParams = 'c.property, pd.name, pd.product_id, pc.category_id, cd.meta_keyword, cd.name as c_name, cg.column, p.isbn, p.sku';
        $query = (new Query())->select($selectParams)
            ->from(self::$coupon.' as c')
            ->leftJoin(self::$couponProduct.' as cp', 'c.coupon_id = cp.coupon_id')
            ->innerJoin(self::$product.' as p', 'p.product_id = cp.product_id')
            ->innerJoin(self::$productDesc.' as pd', 'cp.product_id = pd.product_id ')
            ->innerJoin(self::$productCatagory.' as pc', 'pd.product_id = pc.product_id ')
            ->innerJoin(self::$category.' as cg', 'cg.category_id = pc.category_id')
            ->innerJoin(self::$categoryDesc.' as cd', 'cd.category_id = pc.category_id')
            ->where(['cp.coupon_id' => $couponId, 'pd.language_id' => 2, 'cd.language_id' => 2, 'p.status' => 1, 'c.status' => 1])
            ->andWhere(' cd.meta_keyword != 8 and cg.column != 1')
            ->all();
        $data = '';
        $cType = '';
        $ids = [];
        if ($type) {
            if (!$query) {
                return [];
            }
            array_walk ($query, function($tmp, $k) use (&$data) {
                $data[]= $tmp['product_id'];
            });
            return $data;
        }
        if (!$query) {
            return ['ids' => new StdClass(), 'name' => self::PRODUCT_PREFIX . self::PRODUCT_SHELVES_DESC, 'c_type' => self::NOT_ABLE, 'c_name' => self::NOT_ABLE_NAME];
        }
        /* 子商品过滤  */
        foreach($query as $v) {
            if (empty($v['isbn'])) {
                $newData[] = $v;
            }
        }
        array_walk($newData, function($tmp, $k) use (&$data, &$ids, &$newData, &$cType) {
            /* sub_name */
            if (!Predicates::equals(intval($tmp['meta_keyword']), 1)) {
                $cType = ['type' => self::FRAMES, 'c_name' =>  $tmp['column']]; //相框类
            } else {
                $cType = ['type' => self::PHOTOS, 'c_name' =>  $tmp['column']]; //照片类
            }
            /* 同种类商品 直接返回分类ID （前端点击去使用跳转参数）*/
            if (count($newData) > 1) {
                $ids[] = ['category_id' => $tmp['category_id'], 'type' => $tmp['meta_keyword']];
            } else {
                $ids[] = ['category_id' => $tmp['category_id'], 'product_id' => $tmp['product_id'], 'type' => $tmp['meta_keyword']];
            }
            if (($tmp['product_id'] == self::$photoCards || $tmp['product_id'] == self::$lomoCards) && $tmp['property'] == 2) {
                $tmp['name'] = $tmp['name'] . self::LOMO_PHOTOS_UNIT . $tmp['sku'];
            }
            $data .= $tmp['name'] . '、';
        });
        return ['ids' => $ids[0], 'name' => self::PRODUCT_PREFIX.rtrim($data, "、"), 'c_type' => $cType['type'], 'c_name' => $cType['c_name']];
    }

    /* 查询对应表数据 返回 唯一条数据 */
    public static function queryOneTableInfo($params, $tableName, $condition)
    {
        $query  = (new Query())->select($params)
            ->from($tableName)
            ->where($condition)
            ->one();
        if (!$query) {
            return false;
        }
        return $query;
    }

    /* 通过code 查询优惠劵信息  */
    public static function queryCouponInfoByCode($code)
    {
        if (preg_match("/1[345678]{1}\d{9}$/", $code)) {
            if (self::queryExistCouponStatus($code, 1)) {
                return true;
            }
            $query = self::queryExistCouponStatus($code, 0);
        } else {
        /* 不是通过分享链接输入手机号 则 再确定为分享code */
            $query = (new Query())->from(self::$coupon)
                ->where(['code' => $code, 'status' => 1])
                ->andWhere('logged !=3')
                ->all();
        }
        $newResult = [];
        if ($query) {
            foreach ($query as $value) {
                /* 有效领取时间 */
                if ((strtotime($value['date_start']) <= strtotime(date('Y-m-d'))) && (strtotime(date('Y-m-d')) <= strtotime($value['date_end']))) {
                    $flag = true;
                } elseif (strtotime($value['date_start']) == strtotime(date('Y-m-d'))) { //边界条件
                    $flag = true;
                } else {
                    $flag = false; //无效领取时间或未开始领取时间
                }
                if ($flag) {
                    /* 自定义日期 判断是否过期 */
                    if ($value['use_type'] == 0) {
                        if (strtotime($value['use_end']) >= strtotime(date('Y-m-d'))) {
                            $newResult[] = $value;
                        }
                    } else {
                        $newResult[] = $value;
                    }
                }
            }
        }
        if (!$newResult) {
            return false;
        }
        return $newResult;
    }

    
    private static function queryExistCouponStatus($code, $status)
    {
        return (new Query())->from(self::$userShare.' as us')
            ->leftJoin(self::$coupon.' as c', 'us.assoc_code = c.coupon_id')
            ->where(['us.phone' => $code, 'c.status' => 1, 'us.status' => $status])
            ->all();
    }
    
    private static function queryShareLinkCoupon($customerId, $code, $status)
    {
        if (preg_match("/1[345678]{1}\d{9}$/", $code)) {
           return (new Query())->select('c.coupon_id')
                ->from(self::$customerCoupon. ' as cc')
                ->innerJoin(self::$coupon.' as c', 'cc.coupon_id=c.coupon_id')
                ->innerJoin(self::$userShare.' as us', 'us.assoc_code=c.coupon_id')
                ->where(['cc.customer_id' => $customerId, 'us.phone' => $code, 'c.status' => 1, 'us.status' => $status])
                ->all();
        }
        return (new Query())->from(self::$customerCoupon. ' as cc')
            ->leftJoin(self::$coupon.' as c', 'cc.coupon_id=c.coupon_id')
            ->where(['cc.customer_id' => $customerId, 'c.code' => $code, 'c.status' => 1])
            ->all();
    }

    /* 查询用户是否领过本口令的优惠券  */
    public static function queryUserAlreadyExistCoupon($customerId, $code)
    {
        if ($query = self::queryExistCouponStatus($code, 0)) {
            return $query;
        }
        if (self::queryShareLinkCoupon($customerId, $code, 1)) {
            return false;
        }
        return true;
    }

    /* 获取领取次数 */
    public static function queryCouponCollectTimes($couponId)
    {
        return (new Query())->from(self::$customerCoupon. ' as cc')
            ->leftjoin(self::$coupon.' as c', 'cc.coupon_id = c.coupon_id')
            ->where(['c.coupon_id' => $couponId, 'c.status' => 1])
            ->count();
    }

    /* 支付完成后修改状态  */
    public static function afterCompleteChangeStatus($orderId, $customerId, $couponId, $amount)
    {
        /* 优惠劵用户表修改优惠劵状态*/
        $updateStatus = DataBase::doTableUpdate(self::$customerCoupon, ['type' => 1], ['customer_id' => $customerId, 'coupon_id' => $couponId]);
        $insertData = [
            'coupon_id' => $couponId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'amount' => $amount,
            'date_added' => date('Y-m-d H:i:s')
        ];
        /* 订单使用优惠劵历史表信息入库 */
        $insertHistory = DataBase::doTableInsert(self::$couponHistory, $insertData, 1);
        if ($updateStatus && $insertHistory) {
            return true;
        }
        return false;
    }
    
    /* 同类商品按高价格排序  */
    public static function querySameCategory($products)
    {
        array_walk($products, function (&$value, $key) {
            $query = (new Query())->select('p.price p, pc.category_id ci')
                ->from(self::$product.' as p')
                ->leftJoin(self::$productCatagory.' as pc', 'p.product_id = pc.product_id')
                ->where('p.product_id ='.$key)
                ->one();
            $value['category_id'] = $query['ci'];
            $value['price'] = $query['p'];
        });
       /* 排序  */
        uasort($products, function ($a, $b) {
            if ($a['price'] == $b['price']) {
                return 0;
            }
            return $a['price'] > $b['price'] ? -1 : 1;
        });
        $temp = [];
        array_walk($products, function ($v, $k) use (&$temp) {
            $temp[$v['category_id']][$k] = [$v['count'] => $v['price']];
        });
        $curr = [];
        foreach ($temp as $k=>$val) {
            foreach ($val as $k1=>$flag) {
                $curr[$k1] = ['count' => key($flag)];
            }
        }
        return $curr;
    }
    
    /* 随机code （订单完成后分享订单ID为sercet）入库  */
    public static function doRandomlyCoupon($randCode, $type, $secret)
    {
        $insertData = [
            'secret' => $secret,
            'code' => $randCode,
            'share_type' => $type
        ];
        $insert = DataBase::doTableInsert(self::$shareCoupon, $insertData, 1);
        if(!$insert) {
            return false;
        }
        return $insert;
    }

    /* 分享优惠券库数据(可选查询条件) */
    public static function queryShareCouponList($shareId, $num, $condition = NULL)
    {
        if ($condition) {
            $where = array_merge(['logged' => self::ADMIN_SHARE_COUPON, 'status' => 1], $condition);
        } else {
            $where = ['logged' => self::ADMIN_SHARE_COUPON, 'status' => 1];
        }
        $data = (new Query())->select('coupon_id, uses_total, discount')
            ->from(self::$coupon)
            ->where($where)
            ->limit($num)
            ->orderBy('coupon_id')
            ->all();
        shuffle($data);
        if (!$data) {
            return false;
        }
        /* 组合概率数组  */
        $temp = [];
        array_walk($data, function ($value, $key) use (&$temp, &$shareId) {
            $temp[$value['coupon_id']] = $value['uses_total']; //概率需求
            Yii::$app->redis->RPUSH(HI\Coupon\RAND_RED_PACKET . $shareId, $value['coupon_id']);
        });
        Yii::$app->redis->expire(HI\Coupon\RAND_RED_PACKET . $shareId, 86400 * 30 * 2);
        return $temp;
    }
    
    /* 查询用户最新的订单 */
    public static function queryNewestOrderId($customerId)
    {
        $query = (new Query())->select('order_id')
            ->from(self::$order)
            ->where(['customer_id' => $customerId])
            ->andWhere('order_status_id not in (1,14)')
            ->orderBy('order_id desc')
            ->one();
        if (!$query) {
            return '';
        }
        return $query['order_id'];
    }
    
    /* 查询是否超过领取次数 */
    public static function queryWhetherOverCount($code, $shareType = NULL)
    {
        $condition['sc.code'] = $code;
//         $condition['sc.status'] = 1;
        if ($shareType) {
            $condition['sc.share_type'] = $shareType;
        }
        return (new Query())->from(self::$shareCoupon.' as sc')
            ->innerJoin(self::$userShare.' as us', 'sc.share_id = us.share_id')
            ->where($condition)
            ->count();
    }
    
    /* 查询单位 */
    public static function queryProductUnit($couponId)
    {
        $query = (new Query())->select('p.sku')
            ->from(self::$coupon.' as c')
            ->leftJoin(self::$couponProduct.' as cp', 'c.coupon_id = cp.coupon_id')
            ->leftJoin(self::$product.' as p', 'p.product_id = cp.product_id')
            ->where(['c.coupon_id' => $couponId])
            ->one();
        return Preconditions::checkArgument($query['sku']);
    }
    
    /* 随机一条数据 */
    public static function doGetNewCoupon($couponData, $type = NULL)
    {
        if (empty($type)) {
            $couponData['code'] = substr(uuid_create(), -8); //随机code
        }
        $couponData['payload'] = $couponData['coupon_id']; //父优惠劵
        $couponData['logged'] = 3; //后台不显示
        $couponData['date_added'] = date('Y-m-d H:i:s'); //添加时间
        $couponData['nums'] = 1; //只能领取一次
        $couponProduct = CouponModel::queryAbleProduct($couponData['coupon_id'], 1); //适用商品
        unset($couponData['coupon_id']);
        $getNewCouponId = DataBase::doTableInsert(CouponModel::$coupon, $couponData, 1);//新数据
        if ($getNewCouponId) {
            foreach ($couponProduct as $ids) {
                $insertData = ['coupon_id' => $getNewCouponId, 'product_id' => $ids];
                Preconditions::checkArgument(DataBase::doTableInsert(self::$couponProduct, $insertData, 1));
            }
            $couponData['coupon_id'] = $getNewCouponId;
            return $couponData;
        }
        return false;
    }

    /* 优惠劵关联用户 */
    public static function couponAssocCustomer($couponId, $customerId)
    {
        $coupon = [
            'coupon_id' => $couponId,
            'customer_id' => $customerId,
            'date_added' => date('Y-m-d H:i:s')
        ];
        return DataBase::doTableInsert(CouponModel::$customerCoupon, $coupon, 1);
    }
}
