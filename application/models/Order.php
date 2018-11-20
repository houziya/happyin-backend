<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class OrderModel
{
    //免邮临界点
    const SHIPPING = 8;
    const FREEPOST = 20;
    const QUANTITY = 1000000;
    public  static function calculationAmount($loginUid, $couponId, $productJson, $flag = 0, $isRecommend = 0 ,$data = [])
    {
        $productInfos = [];
        $useCoupon = [];
        $productUseCou = [];
        $couponTotal = 0;
        $productTotal = 0;
        $checkCouponInfo = empty($couponId) ? [] : self::checkCouponUsable($loginUid, $couponId);
        //购物车只有一件商品
        if (count($productJson) == 1 && is_array($productJson)) {
            foreach ($productJson as $key => $val) {
                $productInfo = self::getProductInfo($key, $flag);
                $productName = self::getProductName($productInfo["isbn"], $productInfo);
                if (isset($productInfo['status']) && $productInfo['status'] == 0 && $flag == 1) {
                    return ['status' => 0, 'message' => '您选购的商品'. $productName .'已下架'];
                }
                //商品金额
                $productTotal = self::productTotal($key, $val['count'], self::QUANTITY, $productInfo['price'], $flag);
                //商品信息
                $productInfos = self::productInfos($key, $val['count'], $productInfo['price'], self::QUANTITY, $productInfo['state']);
                if (!empty($checkCouponInfo)) {
                     foreach ($checkCouponInfo as $k1 => $v2) {
                         //对于某种商品验证优惠券是否可用
                         if (!CouponModel::queryScopeReturnBool($v2['coupon_id'], $key)) {
                             unset($v2['coupon_id']);
                             //推荐一张最优的优惠券
                             $resultRelData = self::returnRelData($key, $val, $productInfo, $data);
                             $productNumAndPrice = $resultRelData['productNumAndPrice'];
                             $useCoupon = $resultRelData['useCoupon'];
                         } else if(CouponModel::queryScopeReturnBool($v2['coupon_id'], $key) || $isRecommend == 1 && !empty($data)){
                             if ($data->flag == 1) {
                                 $resultData = self::returnCouData($v2, $val, $productInfo, $key, $productTotal);
                                 $productUseCou[] = $resultData['productUseCou'];
                             } else {
                                 $resultData = self::returnRelData($key, $val, $productInfo, $data);
                             }
                             $productNumAndPrice = $resultData['productNumAndPrice'];
                             $useCoupon = $resultData['useCoupon'];
                         } else {
                             //商品价格和数量
                             $productNumAndPrice[$key] = $val['count'] . '_' . $productInfo['price'];
                         }
                     }
                } else if (empty($couponId) && $isRecommend == 1 && !empty($data)) {
                    $resultRelData = self::returnRelData($key, $val, $productInfo, $data);
                    $productNumAndPrice = $resultRelData['productNumAndPrice'];
                    $useCoupon = $resultRelData['useCoupon'];
                } else {
                    //商品价格和数量
                    $productNumAndPrice[$key] = $val['count'] . '_' . $productInfo['price'];
                }
            }
        //购物车中有多件商品
        } else if (is_array($productJson)) {
            $productCoupon = [];
            $products = [];
            $resultProData = self::resultProData($productJson, $flag);
            if (isset($resultProData['status']) && $resultProData['status'] == 0 && $flag == 1) {
                return ['status' => 0, 'message' => $resultProData['message']];
            }
            $productInfo = $resultProData['productInfo'];
            $productNumAndPrice = $resultProData['productNumAndPrice'];
            $productCoupon = $resultProData['productCoupon'];
            $productInfos = $resultProData['productInfos'];
            //商品金额
            $productTotal = array_sum($productCoupon);
            if (!empty($checkCouponInfo)) {
                if ($data->flag == 1) {
                    $resultProDatas = self::resultProDatas($productJson, $checkCouponInfo, $productCoupon, $productInfos, $productTotal);
                    $useCoupon = $resultProDatas['useCoupon'];
                    $productInfos = $resultProDatas['productInfos'];
                    $products = $resultProDatas['products'];
                    $productUseCou[] = $resultProDatas['productUseCou'];
                } else {
                    $resultRelData = self::resultRelData($data['products'], $products, $data);
                    $useCoupon = $resultRelData['useCoupon'];
                }
           }
           if ($isRecommend == 1 && !empty($data) && count($useCoupon) < 2) {
               $resultRelData = self::resultRelData($data['products'], $products, $data, $useCoupon);
               $useCoupon = $resultRelData['useCoupon'];
           }
        }
        return ['productTotal' => $productTotal, 'productNumAndPrice' => $productNumAndPrice, 'useCoupon' => $useCoupon, 'productInfos' => $productInfos, 'productUseCou' => $productUseCou];
    }
    
    public static function getProductName($isbn, $productInfo)
    {
         if ($proDescName = OrderModel::doTableSelect('name', HI\TableName\PRODUCT_DESCRIPTION, ['language_id' => 2, 'product_id' => $isbn])) {
            return $proDescName['name'] . '(' . $productInfo["name"] . ')';
        } else {
            return $productInfo["name"];
        }
    }
    
    public static function getProductInfo($key, $flag)
    {
        $productInfo = self::productInfo($key, 0);
        if (empty($productInfo)) {
            $productInfo = self::productInfo($key, 1);
            switch ($flag){
                //创建订单
                case 1:
                    return ['status' => 0, 'isbn' => $productInfo['isbn'], 'quantity' => $productInfo['quantity'], 'name' => $productInfo['name'], 'state' => $productInfo['state'], 'price' => $productInfo['price']];
                //结算
                case 0:
                default:
                    return ['status' => 1, 'isbn' => $productInfo['isbn'], 'quantity' => $productInfo['quantity'], 'name' => $productInfo['name'], 'state' => $productInfo['state'], 'price' => $productInfo['price']];
            }
        }
        return $productInfo;
    }
    
    public static function productInfo($key, $status)
    {
        switch ($status){
            case 0:
                $where = 'pd.language_id = 2 and p.status = 1 and p.product_id = ' . $key;
                break;
            case 1:
            default:
                $where = 'pd.language_id = 2 and p.product_id = ' . $key;
        }
        $productInfo = (new Query())->select('p.price, p.quantity, pd.name, p.isbn, p.status as state')
        ->from(HI\TableName\PRODUCT ." as p")
        ->leftJoin(HI\TableName\PRODUCT_DESCRIPTION ." as pd", "p.product_id = pd.product_id")
        ->where($where)
        ->one();
        return $productInfo;
    }
    
    public static function resultRelData($dataProducts, $products, $data, $useCoupon = [])
    {
        $newProducts = [];
        $productUseCou = [];
        $newProducts = json_decode($dataProducts , true);
        foreach ($newProducts as $k6 => $v6) {
            if (in_array($k6, $products)) {
                unset($newProducts[$k6]);
            }
        }
        if (!empty($newProducts)) {
            $resultProductsData = self::productCategory($products, $newProducts);
            if (!empty ($resultProductsData['productsData'])) {
                $coupon = Coupon::returnBestCouponList($data, json_encode($resultProductsData['productsData']));
                if ($coupon) {
                    foreach ($coupon as $k5 => $v5) {
                        //使用优惠券
                        $useCoupon[$v5['coupon_id']] = self::useCoupon($v5['coupon_id'], $v5['coupon_id'], $v5['name'], $v5['recommend']);
                    }
                }
            }
        }
        return ['useCoupon' => $useCoupon];
    }
    
    /*
     * 向某表插入数据
     *  */
    public static function doTableInsert($table, $parameter) {
        $connet = Yii::$app->db;
        $res = $connet->createCommand()->insert($table, $parameter)->execute();
        return $connet->getLastInsertID();
    }
    
    /*
     * 查某表数据
     *  */
    public static function doTableSelect($field = '*', $table, $parameter) {
        $query = new Query;
        return $query->select($field)
        ->from($table)
        ->where($parameter)
        ->one();
    }
    
    /*
     * 修改某表数据
     *  */
    public static function doTableUpdate($table, $updateFileds, $where) {
        return Yii::$app->db->createCommand()->update($table, $updateFileds, $where)->execute();
    }
    
    public static function checkCouponUsable($loginUid, $couponId)
    {
        if (!empty($couponId)) {
            $where = "cu.coupon_id in (" . implode(',', $couponId) . ') and cu.type = 0';
        } else {
            $where = "cu.type = 0";
        }
        $query = new Query;
        return $checkCouponId = $query
        ->select("co.name, cu.coupon_id, co.discount, co.reduction, co.property, cop.product_id")
        ->from(HI\TableName\CUSTOMER_COUPON . " as cu")
        ->innerJoin(HI\TableName\COUPON . " as co", "cu.coupon_id = co.coupon_id")
        ->leftJoin(HI\TableName\COUPON_PRODUCT . " as cop", "co.coupon_id = cop.coupon_id")
        ->where("cu.customer_id = $loginUid")
        ->andWhere($where)
        ->groupBy("co.coupon_id")
        ->all();
    }
    
    public static function productTotal($key ,$count, $quantity, $price, $flag) 
    {
        if ($count > $quantity) {
            switch ($flag) {
                case 1:
                    return -1;
                case 0:
                default:
                    $productTotal = $price * $quantity;
                    break;
            }
        } else {
            if (!$product = OrderModel::doTableSelect('price', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $key])) {
                $productTotal = 0;
            } else {
                $productTotal = $price * $count;
            }
        }
        return $productTotal;
    }

    public static function useCoupon($couponId, $key, $couponInfoName, $couponTotal) 
    {
        $useCoupon = [];
        //使用优惠券
        $useCoupon[$couponId]['coupon_id'] = $couponId;
        $useCoupon[$couponId]['name'] = $couponInfoName;
        $useCoupon[$couponId]['recommend'] = $couponTotal;
        return $useCoupon;
    }
    
    public static function productInfos($key, $count, $price, $quantity, $state) 
    {
        //商品信息
        $productInfos[$key]['count'] = $count;
        $productInfos[$key]['price'] = $price;
        $productInfos[$key]['quantity'] = $quantity;
        $productInfos[$key]['state'] = $state;
        return $productInfos;
    }
    
    /**
     * 替换数组中NULL值为空字符串
     */
    public static function arrNullReplaceStr($originalData)
    {
        foreach ($originalData as $k => $v) {
            foreach ($v as $k2 => $v2) {
                if (empty($v2)) {
                    $originalData[$k][$k2] = $v2 = '';
                }
                if ($k2 == 'plapayload') {
                    $originalData[$k][$k2] = json_decode($v);
                }
            }
        }
        return $originalData;
    }
    
    public static function returnRelData($key, $val, $productInfo, $data) 
    {
         $useCoupon = [];
         $productUseCou = [];
         //商品价格和数量
         $productNumAndPrice[$key] = $val['count'] . '_' . $productInfo['price'];
         //获取合适优惠券
         $coupon = Coupon::returnBestCouponList($data, $data['products']);
         if ($coupon) {
             foreach ($coupon as $k6 => $v6) {
                 if ($v6['recommend']) {
                     //优惠价格
                     $couponT = $v6['recommend'];
                 } else {
                     $couponT = 0;
                 }
                 if ($couponT != 0) {
                     $couponTotal = $couponT;
                 }
                 //生成优惠券信息
                 $useCoupon[$v6['coupon_id']] = self::useCoupon($v6['coupon_id'], $v6['coupon_id'], $v6['name'], $couponTotal);
             }
         }
         return ['productNumAndPrice' => $productNumAndPrice, 'useCoupon' => $useCoupon];//, 'productUseCou' => $productUseCou
    }
    
    public static function returnCouData($v2, $val, $productInfo, $key, $productTotal) 
    {
        $productUseCou = [];
        //商品价格和数量
        $productNumAndPrice[$key] = $val['count'] . '_' . $productInfo['price'];
        switch ($v2['property']) {
            case 0:
                $totalCoupon = $v2['discount'] > $productTotal ? $productTotal : $v2['discount'];
                //生成优惠券信息
                $useCoupon[$v2['coupon_id']] = self::useCoupon($v2['coupon_id'], $key, $v2['name'], $totalCoupon);
                $productUseCou[$key] = $v2['coupon_id'];
                break;
            case 1:
                if (in_array($key, [HI\Config\Product\PHOTO_CARDS_PRODUCT_ID, HI\Config\Product\LOMO_CARDS_PRODUCT_ID])) {
                
                    if ($val['count'] >= $v2['reduction']) {
                        //优惠价格
                        $couponTotal = $productInfo['price'] * $v2['reduction'];
                    } else {
                        //优惠价格
                        $couponTotal = $productInfo['price'] * $val['count'];
                    }
                } else {
                    //优惠价格
                    $couponTotal = $val['count'] - $v2['reduction'] <= 0 ? $val['count'] * $productInfo['price'] : $v2['reduction'] * $productInfo['price'];
                }
                //生成优惠券信息
                $useCoupon[$v2['coupon_id']] = self::useCoupon($v2['coupon_id'], $key, $v2['name'], $couponTotal);
                $productUseCou[$key] = $v2['coupon_id'];
                break;
            case 2:
                if (in_array($key, [HI\Config\Product\PHOTO_CARDS_PRODUCT_ID, HI\Config\Product\LOMO_CARDS_PRODUCT_ID])) {
                   $couponTotal = $productInfo['price'];
                } else {
                    //优惠价格
                    $couponTotal = $val['count'] - $v2['reduction'] <= 0 ? $val['count'] * $productInfo['price'] : $v2['reduction'] * $productInfo['price'];
                }
                //生成优惠券信息
                $useCoupon[$v2['coupon_id']] = self::useCoupon($v2['coupon_id'], $key, $v2['name'], $couponTotal);
                $productUseCou[$key] = $v2['coupon_id'];
                break;
        }
        return ['productNumAndPrice' => $productNumAndPrice, 'useCoupon' => $useCoupon , 'productUseCou' => $productUseCou];
    }
    
    public static function resultProData($productJson, $flag) 
    {
        foreach ($productJson as $k => $v) {
            $productInfo = self::getProductInfo($k, $flag);
            $productName = self::getProductName($productInfo["isbn"], $productInfo);
            $productNumAndPrice[$k] = $v['count'] . '_' . $productInfo['price'];
            if (isset($productInfo['status']) && $productInfo['status'] == 0 && $flag == 1) {
                return ['status' => 0, 'message' => '您选购的商品'. $productName .'已下架'];
            }
            //商品总价
            if ($v['count'] > self::QUANTITY) { //$productInfo['quantity']
                switch ($flag) {
                    case 1:
                        return ['status' => -1, 'message' => '您选购的商品'. $productName .'暂时缺货'];
                    case 0:
                    default:
                        $productCoupon[$k] = self::QUANTITY * $productInfo['price']; //$productInfo['quantity']
                        break;
                }
            } else {
                if (!$product = OrderModel::doTableSelect('price', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $k])) {
                    $productCoupon[$k] = 0;
                } else {
                    $productCoupon[$k] = $v['count'] * $productInfo['price'];
                }
            }
            //商品信息
            $productInfos[$k]['count'] = $v['count'];
            $productInfos[$k]['price'] = $productInfo['price'];
            $productInfos[$k]['quantity'] = $productInfo['quantity'];
            $productInfos[$k]['state'] = $productInfo['state'];
        }
        return ['productInfo' => $productInfo, 'productNumAndPrice' => $productNumAndPrice, 'productCoupon' => $productCoupon, 'productInfos' =>$productInfos];
    }
    
    public static function resultProDatas($productJson, $checkCouponInfo, $productCoupon, $productInfos, $productTotal)
    {
        $couponFirst = $couponFirstOne = $newCouponT = $couponT = $couponTwo = $couponTOne = $couponFirstTwo = $newCouponFirst = $flag = 0;
        $useCoupon = [];
        $productUseCou = [];
        $products = [];
        $couPonId = [];
        $couPonIds = [];
        $newProduct = self::orderByProduct($productJson);
        foreach ($newProduct as $k7 => $v7) {
            foreach ($checkCouponInfo as $k4 => $v4) {
                if (!CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7)) {
                    unset($v4['coupon_id']);
                } else {
                    $newProductInfo = self::doTableSelect('price', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $k7]);
                    //购物车有多件商品
                    switch ($v4['property']) {
                        case 0:
                            if (CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7) && $productCoupon[$k7] != 0) {
                                $products[] = $k7;
                                $couponTotalsOne[$k7] = $v7['count'] * $newProductInfo['price'];
                            }
                            foreach ($couponTotalsOne as $k => $v) {
                                $couponTOne = $v;
                            }
                            if ($couponFirstOne == 0) {
                                $newCouponFirstOne = $couponFirstOne = $couponTOne;
                            } else {
                                if ($couponTOne != 0) {
                                    $newCouponFirstOne = $couponFirstOne = $couponTOne + $newCouponFirstOne;
                                } else {
                                    $newCouponFirstOne = $couponFirstOne = $couponTOne;
                                }
                            }
                            $newCouponFirstOne = $v4['discount'] > $newCouponFirstOne ? $newCouponFirstOne : $v4['discount'];
                            //使用优惠券
                            $useCoupon[$v4['coupon_id']] = self::useCoupon($v4['coupon_id'], $v4['coupon_id'], $v4['name'], $newCouponFirstOne);
                            $productUseCou[$k7] = $v4['coupon_id'];
                            break;
                        case 1:
                            $couponTotalsTwo = [];
                            if (CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7) && $productCoupon[$k7] != 0) {
                                $products[] = $k7;
                                //lomo卡和照片卡
                                if (in_array($k7, [HI\Config\Product\PHOTO_CARDS_PRODUCT_ID, HI\Config\Product\LOMO_CARDS_PRODUCT_ID])) {
                                    if (isset($reductionFour)) {
                                        if ($reductionFour == 0) {
                                            $couponTotalsTwo[$k7] = 0;
                                        } else {
                                            if(CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7)){
                                                $productUseCou[$k7] = $v4['coupon_id'];
                                            }
                                            //优惠价格
                                            if ($reductionFour > $v7['count']) {
                                                $couponTotalsTwo[$k7] = $newProductInfo['price'] * $v7['count'];
                                            } else {
                                                $couponTotalsTwo[$k7] = $newProductInfo['price'] * $reductionFour;
                                            }
                                        }
                                    } else {
                                        if(CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7)){
                                            $productUseCou[$k7] = $v4['coupon_id'];
                                        }
                                        //优惠价格
                                        if ($v4['reduction'] > $v7['count']) {
                                            $couponTotalsTwo[$k7] = $newProductInfo['price'] * $v7['count'];
                                        } else {
                                            $couponTotalsTwo[$k7] = $newProductInfo['price'] * $v4['reduction'];
                                        }
                                        $reductionFour = $v4['reduction'] - $v7['count'] > 0 ? $v4['reduction'] - $v7['count'] : 0;
                                    }
                                } else {
                                    if (isset($reductionFour)) {
                                        if ($reductionFour == 0) {
                                            $couponTotals[$k7] = 0;
                                        }
                                    } else if (isset($reductionTwo)) {
                                        if ($reductionTwo != 0) {
                                            if ($reductionTwo > $v7['count']) {
                                                $couponTotalsTwo[$k7] = $v7['count'] * $newProductInfo['price'];
                                            } else {
                                                $couponTotalsTwo[$k7] = $reductionTwo * $newProductInfo['price'];
                                            }
                                            $reductionTwo = $reductionTwo - $v7['count'] <= 0 ? 0 : $reductionTwo - $v7['count'];
                                        }
                                    } else {
                                        //优惠价格
                                        if ($v7['count'] > $v4['reduction']) {
                                            $reductionTwo = isset($reductionTwo) ? $reductionTwo : $v4['reduction'];
                                        } else {
                                            $reductionTwo = isset($reductionTwo) ? $reductionTwo : $v7['count'];
                                        }
                                        if ($reductionTwo > $v7['count']) {
                                            $reductionTwo = $v7['count'];
                                        }
                                        $couponTotalsTwo[$k7] = $reductionTwo * $newProductInfo['price'];
                                        if ($reductionTwo != 0) {
                                            $productUseCou[$k7] = $v4['coupon_id'];
                                        }
                                        $reductionTwo = $v4['reduction'] - $v7['count'] <= 0 ? 0 : $v4['reduction'] - $v7['count'];
                                  }
                               }
                            }
                            if (empty($couponTotalsTwo)) {
                                $couponTwo = 0;
                            } else {
                                foreach ($couponTotalsTwo as $k => $v) {
                                    $couponTwo = $v;
                                }
                            }
                            if ($couponFirstTwo == 0) {
                                $newCouponFirstTwo = $couponFirstTwo = $couponTwo;
                            } else {
                                if ($newCouponFirstTwo != 0 && $couponTwo != 0 && in_array($v4['coupon_id'], $couPonId)) {
                                    $newCouponFirstTwo = $couponFirstTwo = $couponTwo + $newCouponFirstTwo;
                                } else if ($couponTwo == 0 && in_array($v4['coupon_id'], $couPonId)) {
                                    $newCouponFirstTwo = $newCouponFirstTwo;
                                }  else {
                                    $newCouponFirstTwo = $couponFirstTwo = $couponTwo;
                                }
                            }
                            //使用优惠券
                            $useCoupon[$v4['coupon_id']] = self::useCoupon($v4['coupon_id'], $v4['coupon_id'], $v4['name'], $newCouponFirstTwo);
                            $couPonId[] = $v4['coupon_id'];
                            break;      
                        case 2:
                            $couponTotals = [];
                            if (CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7) && $productCoupon[$k7] != 0) {
                                $products[] = $k7;
                                $coupon[$k7][] = $v4['coupon_id'];
                                //lomo卡和照片卡
                                if (in_array($k7, [HI\Config\Product\PHOTO_CARDS_PRODUCT_ID, HI\Config\Product\LOMO_CARDS_PRODUCT_ID])) {
                                    if (isset($reductionThree)) {
                                        if ($reductionThree == 0) {
                                            $couponTotals[$k7] = 0;
                                        }
                                    } else {
                                        if(CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7)){
                                            $productUseCou[$k7] = $v4['coupon_id'];
                                        }
                                        //优惠价格
                                        $couponTotals[$k7] = $newProductInfo['price'];
                                        $reductionThree = 0;
                                    }
                                } else {
                                    if (isset($reductionThree)) {
                                        if ($reductionThree == 0) {
                                            $couponTotals[$k7] = 0;
                                        }
                                    } else if (isset($reduction)) {
                                        if ($reduction != 0) {
                                            if ($reduction > $v7['count']) {
                                                $couponTotals[$k7] = $v7['count'] * $newProductInfo['price'];
                                            } else {
                                                $couponTotals[$k7] = $reduction * $newProductInfo['price'];
                                            }
                                            $reduction = $reduction - $v7['count'] <= 0 ? 0 : $reduction - $v7['count'];
                                        }
                                    } else {
                                        //优惠价格
                                        if ($v7['count'] > $v4['reduction']) {
                                            $reduction = isset($reduction) ? $reduction : $v4['reduction'];
                                        } else {
                                            $reduction = isset($reduction) ? $reduction : $v7['count'];
                                        }
                                        if ($reduction > $v7['count']) {
                                            $reduction = $v7['count'];
                                        }
                                        $couponTotals[$k7] = $reduction * $newProductInfo['price'];
                                        if ($reduction != 0) {
                                            $productUseCou[$k7] = $v4['coupon_id'];
                                        }
                                        $reduction = $v4['reduction'] - $v7['count'] < 0 ? 0 : $v4['reduction'] - $v7['count'];
                                    }
                                }
                                
                            }
                            if (empty($couponTotals)) {
                                $couponT = 0;
                            } else {
                               foreach ($couponTotals as $k => $v) {
                                  $couponT = $v;
                               }
                            }
                            if ($couponFirst == 0) {
                                $newCouponFirst = $couponFirst = $couponT;
                            } else {
                                if ($newCouponFirst != 0 && $couponT != 0 && in_array($v4['coupon_id'], $couPonIds)) {
                                    $newCouponFirst = $couponFirst = $couponT + $newCouponFirst;
                                } else if ($couponT == 0) {
                                    $newCouponFirst = $newCouponFirst;
                                } else {
                                    $newCouponFirst = $couponFirst = $couponT;
                                }
                            }
                            //使用优惠券
                            $useCoupon[$v4['coupon_id']] = self::useCoupon($v4['coupon_id'], $v4['coupon_id'], $v4['name'], $newCouponFirst);
                            $couPonIds[] = $v4['coupon_id'];
                            break;
                    }
                }
            }
        }
        return ['useCoupon' => $useCoupon, 'productInfos' => $productInfos, 'products' => $products , 'productUseCou' => $productUseCou];
    }

    public static function couponTotals($v4, $k7, $v7, $productCoupon, $products, $newProductInfo)
    {
        if (CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7) && $productCoupon[$k7] != 0) {
            $products[] = $k7;
            $coupon[$k7][] = $v4['coupon_id'];
            //优惠价格
            if ($v7['count'] > $v4['reduction']) {
                $reduction = isset($reduction) ? $reduction : $v4['reduction'];
            } else {
                $reduction = isset($reduction) ? $reduction : $v7['count'];
            }
            if ($reduction > $v7['count']) {
                $reduction = $v7['count'];
            }
            $couponTotals[$k7] = $reduction * $newProductInfo['price'];
            if ($reduction != 0) {
                $productUseCou[$k7] = $v4['coupon_id'];
            }
            $reduction = $v4['reduction'] - $v7['count'] < 0 ? 0 : $v4['reduction'] - $v7['count'];
        }
        return ['couponTotals' => $couponTotals, 'productUseCou' => $productUseCou, 'products' => $products, 'reduction' => $reduction];
    }
    
    public static function couponData($k7, $v4, $productCoupon, $v7, $couponFirst, $couponT, $useCoupon, $products, $productUseCou, $newCouponFirst)
    {
        $newProductInfo = self::doTableSelect('price', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $k7]);
        $couponTotals = [];
        if (CouponModel::queryScopeReturnBool($v4['coupon_id'], $k7) && $productCoupon[$k7] != 0) {
            $products[] = $k7;
            $coupon[$k7][] = $v4['coupon_id'];
            //优惠价格
            if ($v7['count'] > $v4['reduction']) {
                $reduction = isset($reduction) ? $reduction : $v4['reduction'];
            } else {
                $reduction = isset($reduction) ? $reduction : $v7['count'];
            }
            if ($reduction > $v7['count']) {
                $reduction = $v7['count'];
            }
            $couponTotals[$k7] = $reduction * $newProductInfo['price'];
            if ($reduction != 0) {
                $productUseCou[$k7] = $v4['coupon_id'];
            }
            $reduction = $v4['reduction'] - $v7['count'] < 0 ? 0 : $v4['reduction'] - $v7['count'];
        }
        foreach ($couponTotals as $k => $v) {
            $couponT = $v;
        }
//         if ($couponFirst == 0) {
//             $newCouponFirst = $couponFirst = $couponT;
//         } else {
//             if ($newCouponFirst != 0) {
//                 $newCouponFirst = $couponFirst = $couponT + $newCouponFirst;
//             } else if ($couponT == 0) {
//                 $newCouponFirst = $newCouponFirst;
//             } else {
//                 $newCouponFirst = $couponFirst = $couponT;
//             }
//         }
        //使用优惠券
//         $useCoupon[$v4['coupon_id']] = self::useCoupon($v4['coupon_id'], $v4['coupon_id'], $v4['name'], $newCouponFirst);
        return ['couponFirst' => $couponFirst, 'couponT' => $couponT, 'products' => $products, 'productUseCou' => $productUseCou];
    }
    
    public static function totalCoupon($couponTotals, $couponFirst)
    {
        foreach ($couponTotals as $k => $v) {
            $couponT = $v;
        }
        if ($couponFirst == 0) {
            $newCouponFirst = $couponFirst = $couponT;
        } else {
            if ($newCouponFirst != 0) {
                $newCouponFirst = $couponFirst = $couponT + $newCouponFirst;
            } else {
                $newCouponFirst = $couponFirst = $couponT;
            }
        }
        return $newCouponFirst;
    }
    
    public static function orderByProduct($productJson)
    {
        $product = [];
        $newProduct = [];
        foreach ($productJson as $k => $v) {
            $productInfo = self::doTableSelect('price', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $k]);
            $product[$k] = $productInfo['price'];
            $productCount[$k] = $v;
            arsort($product);
        }
        foreach ($product as $k2 => $k3) {
            foreach ($productCount as $k4 => $v4) {
                if ($k2 == $k4) {
                    $newProduct[$k2] = $v4;
                }
            }
        }
        return $newProduct;
    }
    
    public static function productCategory($products, $newProducts)
    {
        $uncategory = [];
        $category = [];
        if (!empty($products) && !empty($newProducts)) {
            //查询商品是否属于一个品类
            foreach ($products as $k7 => $v7) {
                $query = new Query;
                $uncategory[] = $query
                ->select("pc.product_id, c.column")
                ->from(HI\TableName\PRODUCT_TO_CATEGORY . " as pc")
                ->innerJoin(HI\TableName\CATEGORY . " as c", "pc.category_id = c.category_id")
                ->where(['pc.product_id' => $v7])
                ->andWhere("c.column != 1")
                ->one();
            }
            foreach ($newProducts as $k8 => $v8) {
                $query = new Query;
                $category[] = $query
                ->select("pc.product_id, c.column")
                ->from(HI\TableName\PRODUCT_TO_CATEGORY . " as pc")
                ->innerJoin(HI\TableName\CATEGORY . " as c", "pc.category_id = c.category_id")
                ->where(['pc.product_id' => $k8])
                ->andWhere("c.column != 1")
                ->one();
            }
            $newCategory = [];
            foreach ($uncategory as $k9 => $v9) {
                foreach ($category as $k2 => $v2) {
                    if ($v9['column'] == $v2['column']) {
                        unset($category[$k2]);
                    } else {
                        $newCategory[$v2['product_id']] = $v2['product_id'];
                    }
                }
            }
            $productsData = [];
            foreach ($newCategory as $k3 => $v3) {
                foreach ($newProducts as $k4 => $v4) {
                    if ($k3 == $k4) {
                        $productsData[$k4]['count'] = $v4['count'];
                    }
                }
            }
        } else {
            $productsData = $newProducts;
        }
        return ['productsData' => $productsData];
    }
    
    public function branchOrderIncrement($order_id)
    {
        $query = new Query;
        $orderSplitting = $query->select('s.order_child_id, s.code')
                   ->from(HI\TableName\ORDER ." as o")
                   ->leftJoin(HI\TableName\ORDER_SPLITTING ." as s", "o.order_id = s.order_id")
                   ->where(['o.order_id' => $order_id])
                   ->all();
        array_walk($orderSplitting, function (&$value, $key) {
            if ($value['code'] == 0) {
                //订单编号表
                OrderModel::doTableInsert(HI\TableName\ORDER_NUMBERING_SD, ['order_child_id' => $value['order_child_id']]);
            } else {
                //订单编号表
                OrderModel::doTableInsert(HI\TableName\ORDER_NUMBERING_ZJ, ['order_child_id' => $value['order_child_id']]);
            }
        });
        return true;
    }

}
