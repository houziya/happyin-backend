<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class Coupon
{
    /* 优惠劵状态  */
    const NOT_SUIT = 6; //不适用
    const NEW_COUPON = 5; //新到
    const TO_EXPIRE_COUPON = 4; //快过期
    const NORMAL_COUPON = 3; //正常
    const NOT_STARTED = 2; //未开始
    const EXPIRED_COUPON = 1; //已过期
    const USED_COUPON = 0; //已使用

    /* 优惠券单位 */
    const UNIT_MOMENY = '元';
    const UNIT_NUMBER = '件';
    const UNIT_SHEETS = '张';
    
    /*优惠劵可用状态  */
    public static $usableCoupon = [self::NEW_COUPON, self::TO_EXPIRE_COUPON, self::NORMAL_COUPON];

    public static $coupon = HI\TableName\COUPON;
    public static $couponProduct = HI\TableName\COUPON_PRODUCT;
    public static $couponHistory = HI\TableName\COUPON_HISTORY;
    public static $customerCoupon = HI\TableName\CUSTOMER_COUPON;
    public static $order = HI\TableName\ORDER;
    public static $orderProduct = HI\TableName\ORDER_PRODUCT;
    public static $productDesc = Hi\TableName\PRODUCT_DESCRIPTION;

    /* 返回最佳优惠劵 */
    public static function returnBestCouponList($data, $newProducts)
    {
        $couponList = CouponModel::queryCouponList($data->requiredInt('login_uid'));
        if ($newProducts) {
            if ($couponList) {
                /* 过滤金额  */
                $filterCoupon = self::filterCoupon($newProducts, $data->requiredInt('login_uid'), $couponList);
                /* 初始化  */
                $info = self::initCouponList($couponList, $filterCoupon, $data, $newProducts);
                if ($info) {
                    /* 过滤商品  */
                    $tmp = self::doFilterFailedState($newProducts, $info);
                    if ($tmp) {
                        /* 排序  */
                        $list = self::doSortCouponList(self::doGetSubCouponList($tmp), 1);
                        $newList = [];
                        /* 获取排序后每个分类的头数据  */
                        array_walk($list['list'], function ($value) use (&$newList) {
                           $newList[] = self::dealNotWasteCoupon($value['sub_list']);
                        });
                        return $newList;
                    }
                }
            }
        }
        return false;
    }
    /* 优惠劵不浪费原则  */
    public static function dealNotWasteCoupon($subList)
    {
        /* 同优惠价格合并  */
        foreach($subList as $value) {
            if(is_float($value['recommend'])) {
                $flag = true;
                $value['recommend'] =  $value['recommend'] * 100;
            }
            $sameDiscount[$value['recommend']][] = $value;
        }
        foreach($sameDiscount as $v1) {
            /* 大于 1 处理同优惠价格的优惠劵列表*/
            if(count($v1) > 1) {
                $newList = array_shift($sameDiscount);
                foreach($newList as &$v) {
                    $type =  CouponModel::queryOneTableInfo('property', CouponModel::$coupon, ['coupon_id' => $v['coupon_id'], 'status' => 1]);
                    $price = CouponModel::queryOneTableInfo('price', CouponModel::$product, ['product_id' => $v['product_id'][0], 'status' => 1]);
                    switch ($type['property']) {
                        case 0 :
                            $discount = $v['count'];
                            break;
                        case 1 :
                            $discount = $v['count'] * $price['price'];
                            break;
                        case 2 :
                            if (in_array($v['product_id'][0], [CouponModel::$lomoCards, CouponModel::$photoCards])) {
                               $discount = $price['price'];
                            } else {
                               $discount = $v['count'] * $price['price'];
                            }
                            break;
                        default :
                    }
                    $v['discount'] = $discount + count(CouponModel::queryAbleProduct($v['coupon_id'], 1));
                }
                usort($newList, function($a, $b) {
                    /* 不浪费排序 */
                    if (intval($a['discount']) == intval($b['discount'])) {
                        if(strtotime($a['date_end']) != strtotime($b['date_end'])) {
                            return (strtotime($a['date_end']) < strtotime($b['date_end'])) ? -1 : 1;
                        }
                        return 0;
                    }
                    return intval($a['discount']) < intval($b['discount']) ? -1 : 1;
                });
                $currList = array_shift($newList);
                if (isset($flag)) {
                    $currList['recommend'] =  $currList['recommend'] / 100;
                }
                unset($currList['discount']);
                return $currList;
            } else {
                return array_shift($subList);
            }
        }
    }

    /* 过滤能用的商品ID 和有效优惠劵 */
    public static function doFilterFailedState($products, $tmp, $tagert = NULL)
    {
        $totalList['list']= [];
        $filterList['list'] = [];
        $productList = array_keys(Preconditions::checkArgument(json_decode($products, true)));
        array_filter($tmp['list'], function ($v) use(&$filterList, &$productList, &$totalList, &$target) {
            $canUseProduct = CouponModel::queryAbleProduct($v['sub_list']['coupon_id'], 1);
            /* 过滤不适用的商品  状态改为不可用 */
            if (array_intersect($canUseProduct, $productList)) {
                /* 过滤不可用的状态  */
                if (in_array($v['sub_list']['status'], self::$usableCoupon)) {
                    $totalList['list'][] = $v;
                } else {
                    $filterList['list'][] = $v;
                }
            } else {
                $v['sub_name'] = CouponModel::NOT_ABLE_NAME;
                $v['sub_type'] = CouponModel::NOT_ABLE;
                if (in_array($v['sub_list']['status'], self::$usableCoupon)) {
                    $v['sub_list']['status'] = self::NOT_SUIT;
                }
                $filterList['list'][] = $v;
            }
        });
        /* 购物车进入优惠劵列表 ：真区间  否则推荐优惠劵使用 */
        if ($tagert) {
            return ['list' => array_merge($totalList['list'], $filterList['list'])];
        } else {
            return $totalList;
        }
    }

    /* 排序  */
    public static function doSortCouponList($tmp, $target = NULL)
    {
        /* 排序大类型 照片类 相框类 以及不可用类 */
        usort ($tmp['list'], function($a, $b) {
            if ($a['sub_type'] == $b['sub_type']) {
                return 0;
            }
            return ($a['sub_type'] < $b['sub_type']) ? 1 : -1;
        });
        /* 遍历排序子类型列表 */
        array_walk($tmp['list'], function (&$temp, $key) {
            if (count($temp['sub_list']) > 1) {
                usort($temp['sub_list'], function($a, $b) {
                    if ($a['status'] == $b['status']) {
                        if (strtotime($a['date_start']) == strtotime($b['date_start'])) {
                            if (strtotime($a['date_end']) != strtotime($b['date_end'])) {
                                return (strtotime($a['date_end']) < strtotime($b['date_end'])) ? -1 : 1;
                            }
                            return 0;
                        }
                       return (strtotime($a['date_start']) < strtotime($b['date_start'])) ? 1 : -1;
                    }
                    return ($a['status'] < $b['status']) ? 1 : -1;
                });
            }
        });
        if ($target) {
            array_walk($tmp['list'], function (&$temp, $key) {
                if (count($temp['sub_list']) > 1) {
                    usort ($temp['sub_list'], function($a, $b) {
                        /* 最佳优惠劵优惠金额 外层排序  */
                        if ($a['recommend'] == $b['recommend']) {
                            return 0;
                        }
                        return $a['recommend'] < $b['recommend'] ? 1 : -1;
                    });
                }
            });
        }
        return $tmp;
    }
    
    /* 初始化优惠劵列表  */
    public static function initCouponList($couponList, $filterCoupon, $data, $newProducts = null)
    {
        $list = [];
        $recommend = [];
        array_walk ($couponList, function($value, $key) use(&$list, &$filterCoupon, &$recommend, &$data, &$newProducts) {
            if($filterCoupon) {
                if(in_array($value['coupon_id'], $filterCoupon)) {
                    $products = empty($newProducts) ? $data->optional('products') : $newProducts;
                    /* 去得到能用的商品分类数据  */
                    $productScope = CouponModel::queryAbleProduct($value['coupon_id']);
                    //$endDate = date('Y-m-d', (86400 * $value['v'] + strtotime($value['da'])));
                    /* 去准备优惠券开始时间/结束时间  */
                    $date = self::classificationUsedType($value['use_type'], $value['v'], $value['use_start'], $value['use_end'], $value['da']);
                    /* 去得到优惠券状态 */
                    $status = self::doGetCouponStatus($date['end_date'], $date['start_date'], $value['type'], $value['da']);
                    /* 去设置不可用优惠券分区 */
                    if (!in_array($status, self::$usableCoupon) || ($data->optional('type') == CouponModel::COUPON_AVAIALABLE && !$products)){
                        $productScope['c_name'] = CouponModel::NOT_ABLE_NAME;
                        $productScope['c_type'] = CouponModel::NOT_ABLE;
                    }
                    /* 去得到优惠券的类别数据 */
                    $count = self::doGetResultByType($value['property'], $value['coupon_id']);
                    /* 去计算优惠价格 */
                    if ($products) {
                        $recommend = self::doGetBestCoupon($status, $value['property'], $count, $products, $value['coupon_id']);
                    }
                    /* 初始化组装数据 */
                    $list['list'][$key] = [
                        'sub_name' => $productScope['c_name'],    //分类名
                        'sub_type' => $productScope['c_type'],    //分类型
                        'sub_list' =>                                                   //分类列表
                        [
                            'coupon_id' => intval($value['coupon_id']),
                            'name' => $value['name'],
                            'date_start' => $date['start_date'],
                            'date_end' => $date['end_date'],
                            'products' => !$productScope ? '' : $productScope['name'],
                            'product_to_category' => empty($productScope) ? new StdClass() : $productScope['ids'],
                            'status' => $status,
                            'unit' => $count['unit'],
                            'count' => intval($count['count']),
                            'recommend' => !$recommend ? 0 : floatval($recommend[key($recommend)]),
                            'product_id' => !$recommend ? [] : array_keys($recommend)
                        ]
                    ];
                }
            }
        });
        return $list;
    }

    /* 根据后台设置的优惠劵使用类型 获取有效期起始值 */
    public static function classificationUsedType($useType, $validity, $useStart, $useEnd, $added)
    {
        switch ($useType) {
            case 0 :
                return ['start_date' => date('Y-m-d H:i:s', strtotime($useStart)), 'end_date' => date('Y-m-d H:i:s', strtotime($useEnd))];
                break;
            case 1 :
                return ['start_date' => date('Y-m-d H:i:s', strtotime($added)), 'end_date' => date('Y-m-d H:i:s', (86400 * $validity + strtotime($added)))];
                break;
            default :
                Procotol::badRequest(NULL, NULL, $useType.' Undefined');
        }
    }
    
    /* 子类型合并  */
    public static function doGetSubCouponList($list)
    {
        $count = [];
        array_walk($list['list'], function($val, $key) use(&$count) {
            $count[$val['sub_type']][] = $key;
        });
        $newList = [];
        $tmp = [];
        $n = 0;
        array_walk($count, function($val, $key) use($list, &$newList, &$n, &$tmp) {
            foreach ($val as $k => $v) {
                if ($k == 0) {
                    $newList[$n] = $list['list'][$v];
                    $tmp = $newList[$n]['sub_list'];
                    unset($newList[$n]['sub_list']);
                    $newList[$n]['sub_list'][] = $tmp;
                } else {
                    $newList[$n]['sub_list'][] = $list['list'][$v]['sub_list'];
                }
            }
            $n++;
        });
        return ['list' => $newList];
    }
    
    /* 根据总金额配置 过滤优惠劵  */
    public static function filterCoupon($productInfo, $customerId , $couponList)
    {
        $sum = 0;
        $ids = [];
        if($productInfo) {
            Preconditions::checkArgument($list = json_decode($productInfo, true));
            array_walk($list, function ($temp, $key) use (&$sum) {
                $price = CouponModel::queryOneTableInfo('price', CouponModel::$product, ['product_id' => $key]);
                $sum += $price['price'] * $temp['count'];
            });
        }
        array_walk($couponList, function ($v, $k) use (&$sum, &$ids) {
            if($v['total'] > $sum) {
                unset($v['coupon_id']);
            } else {
                $ids[] = $v['coupon_id'];
            }
        });
        return $ids;
    }

    /* 优惠劵状态  */
    public static function doGetCouponStatus($endDate, $startDate, $type, $added)
    {
        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));
        $add = date('Y-m-d', strtotime($added));
        if ($type == 1) {
            $status = self::USED_COUPON; //已使用
        } elseif ($end == date("Y-m-d", strtotime("+1 day")) || $end == date("Y-m-d")) {
            $status = self::TO_EXPIRE_COUPON; //快过期
        } elseif (strtotime($end) < strtotime(date('Y-m-d'))) {
            $status = self::EXPIRED_COUPON; //已过期
        } elseif ((strtotime($start) > strtotime(date('Y-m-d')))) {
            $status = self::NOT_STARTED; //未开始
        } else {
            if (date("Y-m-d", strtotime($start)) == date("Y-m-d") || $add == date('Y-m-d')) {
                $status = self::NEW_COUPON; //新到
            } else {
                $status = self::NORMAL_COUPON; //正常
            }
        }
        return $status;
    }

    /* 最佳优惠劵  */
    public static function doGetBestCoupon($status, $type, $count, $productInfo, $couponId)
    {
        $discount = [];
        $curr = [];
        $usedCoupons = '';
        $usedAmountCoupons = '';
        if (in_array($status, self::$usableCoupon)) {
            $list = CouponModel::querySameCategory(Preconditions::checkArgument(json_decode($productInfo, true)));
            foreach ($list as $key=>$temp) {
                $price = CouponModel::queryOneTableInfo('price', CouponModel::$product, ['product_id' => $key]);
                /* type==0 减免金额 type ==2 ==3 减免 件数 张数  */
                if ($type == 0 ) {
                    $total = $temp['count'] * $price['price'];
                    $offset  = !isset($tNums) ? $count['count'] : $tNums;
                    /* 可适用的商品 计算最优 */
                    if (CouponModel::queryScopeReturnBool($couponId, $key)) {
                        /* 总金额  > 减免金额  计算最优值*/
                        if (!$usedAmountCoupons) {
                            $tNums = $offset - $total;
                            if ($tNums < 0) {
                                $discount[$couponId][$type][$key] = $offset;
                                $usedAmountCoupons = true;
                            } else {
                                $discount[$couponId][$type][$key] = $total;
                            }
                        }
                    }
                } else {
                    $current  = !isset($nums) ? $count['count'] : $nums;
                    /* 可适用的商品 计算最优 */
                    if (CouponModel::queryScopeReturnBool($couponId, $key)) {
                        if ($type == 2) {
                            if (CouponModel::$lomoCards == $key) {
                                $used[CouponModel::$lomoCards] = $price['price'];
                                return $used;
                            }
                            if (CouponModel::$photoCards == $key) {
                                $used[CouponModel::$photoCards] = $price['price'];
                                return $used;
                            }
                        }
                        /* 购买件数  > 减免件数*/
                        if (!$usedCoupons) {
                            $nums = $current - $temp['count'];
                            if ($nums < 0) {
                                $discount[$couponId][$type][$key] = $price['price'] * $current;
                                $usedCoupons = true;
                            } else {
                                $discount[$couponId][$type][$key] = $price['price'] * $temp['count'];
                            }
                        }
                    }
                }
            };
            /* 第一层 排序  */
            if ($discount) {
                uasort ($discount[$couponId][$type], function($a, $b) {
                    if ($a == $b) {
                        return 0;
                    }
                    return $a < $b ? 1 : -1;
                });
                $curr = array_shift($discount[$couponId]);
            }
        }
        /* count > 1 代表 同类别的商品*/
        if (count($curr) > 1) {
            $curr[key($curr)] = array_sum($curr);
        }
        return $curr;
    }

    /*
     *  0 折扣  1 件数  2 张数
     */
    public static function doGetResultByType($type, $couponId)
    {
        switch ($type) {
            case 0 :
                $count = CouponModel::queryOneTableInfo('discount', CouponModel::$coupon, ['coupon_id' => $couponId, 'property' => 0, 'status' => 1]);
                return ['count' => intval($count['discount']), 'unit' => self::UNIT_MOMENY];
                break;
            case 1 :
                $count =  CouponModel::queryOneTableInfo('reduction', CouponModel::$coupon, ['coupon_id' => $couponId, 'property' => 1, 'status' => 1]);
                return ['count' => $count['reduction'], 'unit' => CouponModel::queryProductUnit($couponId)];
                break;
            case 2 :
                $count = CouponModel::queryOneTableInfo('reduction', CouponModel::$coupon, ['coupon_id' => $couponId, 'property' => 2, 'status' => 1]);
                return ['count' => $count['reduction'], 'unit' => self::UNIT_SHEETS];
                break;
            default :
                Procotol::badRequest(NULL, NULL, $type.' Undefined');
        }
    }
    
    /* 通过order_id返回此订单的使用优惠券 */
    public static function getOrderCoupon($orderId) {
        return DataBase::getTableDataRows('order_coupon', "order_id = :o_id", [":o_id" => $orderId], "*");
    }
}