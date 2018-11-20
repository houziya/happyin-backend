<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\db\Exception;

class OrderController extends Controller_Abstract
{
    public static $quantityLimit = 1000000;
    public static $shippingLatest = "暂无物流信息";
    public static $orderStatusDesc = "（等待发货,预计2-3天发货）";
    public static $ratingsContent = "快去帮快乐印评一个五星好评吧，好评越多，我们将发放更多的福利";
    public static $quantity = "0";
    public static $orderStatusShipped = "7";
    public static $orderStatusDel = "14";
    /**
     * 订单列表
     */
    public function listAction()
    {
        $data = Protocol::arguments();
        /* 接收参数 */
        $loginUid = $data->requiredInt('login_uid');//登录uid
        $cursor = Protocol::optional('cursor');//游标(分页参数)
        $limit = Protocol::optionalInt('limit', 20);
        if ($limit > 100) {
            $limit = 100;
        }
        if (Predicates::isNotNull($cursor)) {
            $cursor = json_decode(base64_decode($cursor), true);
            $orderId  = $cursor['order_id'];
        } else {
            $orderId = PHP_INT_MAX;
        }
        $orderList = [];
        $orderList = self::myOrderList($orderList, $orderId, $loginUid, $limit);
        $list = [];
        $orderData = [];
        $orderData = self::orderData($orderList, $list);
        $newList['list'] = [];
        if (!empty($orderData['list'])) {
            foreach ($orderData['list'] as $order) {
                $newList['list'][] = $order;
            }
        }
        $newList['cursor'] = base64_encode(json_encode(['order_id' => $orderData['lastorderId']]));
        Protocol::ok($newList);
    }
    
    private function orderData($orderList)
    {
        $list['list'] = [];
        $lastorderId = 0;
        array_walk ($orderList, function($value, $key) use(&$list, &$lastorderId, &$productImage) {
            if ($productImage = OrderModel::doTableSelect('image', HI\TableName\PRODUCT, ['product_id' => $value["isbn"]])) {
                $product['image'] = 'admin/images/' . $productImage['image'] . '.jpg';
                $proDescName = OrderModel::doTableSelect('name', HI\TableName\PRODUCT_DESCRIPTION, ['language_id' => 2, 'product_id' => $value["isbn"]]);
                $product['name'] = $proDescName['name'] . '(' . $value["name"] . ')';
            } else {
                $product['image'] = 'admin/images/' . $value['image'] . '.jpg';
                $product['name'] = $value["name"];
            }
            $product['price'] = round($value["price"], 2);
            $product['quantity'] = $value["quantity"];
            $product['unit'] = '/' . $value["sku"];
            $list['list'][$value['order_id']]['product'][] = $product;
            $list['list'][$value['order_id']]['total'] = round($value['total'], 2);
            $list['list'][$value['order_id']]['order_id'] = $value['order_id'];
            $list['list'][$value['order_id']]['order_number'] = $value['order_number'];
            $status = self::orderStatus($value["order_status_id"]);
            $list['list'][$value['order_id']]['order_status_id'] = $status;
            if ($value['order_status_id'] != self::$orderStatusShipped) {
                $list['list'][$value['order_id']]['order_status_desc'] = self::$orderStatusDesc;
            }
            $lastorderId = $value['order_id'];
        });
        return ['lastorderId' => $lastorderId, 'list' => $list['list']];
    }
    
    private function myOrderList($orderList, $orderId, $loginUid, $limit)
    {
        $orderList = (new Query())->select('o.order_id, o.order_number, o.date_modified as date_modified, o.total,
            o.order_status_id, p.product_id, pr.sku, pr.isbn, pr.image, d.name, p.price, p.quantity')
                    ->from(HI\TableName\ORDER ." as o")
                    ->leftJoin(HI\TableName\ORDER_PRODUCT ." as p", "o.order_id = p.order_id")
                    ->leftJoin(HI\TableName\PRODUCT_DESCRIPTION ." as d", "p.product_id = d.product_id")
                    ->leftJoin(HI\TableName\PRODUCT ." as pr", "d.product_id = pr.product_id")
                    ->where(['o.customer_id' => $loginUid, 'd.language_id' => 2])
                    ->andWhere('o.order_status_id not in (1, 14) and o.order_id < ' . intval($orderId))
                    ->orderBy(["o.date_modified" => SORT_DESC, "p.order_product_id" => SORT_DESC])
                    ->limit($limit)
                    ->all();
        return $orderList;
    }
    
    /**
     * 价格计算
     */
    public function totalAmountAction()
    {
        $data = Protocol::arguments();
        /* 接收参数 */ 
        $couponId = json_decode($data->required('coupon'), true);//优惠券id
        $loginUid = $data->requiredInt('login_uid');//用户id
        $productJson = json_decode($data->required('products'), true);//json格式要结算的商品
        $unselectedProductJson = json_decode($data->optional('unselected_products', null), true);//json格式要结算的商品
        $productInfos = [];
        if (!empty($unselectedProductJson)) {
            array_walk($unselectedProductJson, function($v3, $k3) use(&$productInfo, &$productInfos){
                $productInfo = OrderModel::doTableSelect('price, quantity, status as state', HI\TableName\PRODUCT, ['status' => 1, 'product_id' => $k3]);
                $productInfos[$k3]['quantity'] = $productInfo['quantity'];
                $productInfos[$k3]['price'] = $productInfo['price'];  
                $productInfos[$k3]['state'] = $productInfo['state'];
            });
        }
        $resultData = [];
        $resultData['useCoupon'] = [];
        $resultData['productInfos'] = [];
        $billing['billingTotal'] = 0;
        $address = json_decode($data->required('address'), true);
        if (!empty($productJson)) {
            //计算总金额，优惠金额
            $resultData = OrderModel::calculationAmount($loginUid, $couponId, $productJson, 0, 1, $data);
            if ($resultData == 0) {
                Protocol::ok('', null, null, '商品不存在');
                return;
            }
            $result['coupon'] = [];
            $result['recommendCount'] = 0;
            $billing['shipping'] = 0;
            $billingTotal = 0;
            array_walk($resultData['useCoupon'], function($v, $k) use(&$coupon, &$result){
                array_walk($v, function($v4, $k4) use(&$coupon, &$result){
                    $coupon['coupon_id'] = isset($v4['coupon_id']) ? $v4['coupon_id'] : '';
                    $coupon['name'] = isset($v4['name']) ? $v4['name'] : '';
                    $coupon['recommend'] = (string)$v4['recommend']; //优惠价格
                    $result['recommendCount'] = (string)$v4['recommend'] + $result['recommendCount']; //优惠价格
                    $result['coupon'][] = $coupon;
                });
            });
            $billing = self::getBilling($resultData['productTotal'], $result['recommendCount'], $billingTotal);
        }
        $result['billingTotal'] = round($billing['billingTotal'], 2);//最终结算价格，如果包邮则不加邮费，反之加邮费 
        $result['productTotal'] = isset($resultData['productTotal']) ? $resultData['productTotal'] : 0; //商品总计价格
        $result['productInfos'] = [];
        foreach ($productInfos + $resultData['productInfos'] as $k2 => $v2) {
            $field['product_id'] = (string)($k2);
            $field['quantity'] = self::$quantityLimit;//isset($v2['quantity']) ? $v2['quantity'] : '';
            $field['price'] = isset($v2['price']) ? round($v2['price'], 2) : '';
            $field['shelves'] = $v2['state'] == 1 ? 0 : 1;
            $result['productInfos'][] = $field;
        }
        if ((isset($resultData['productTotal']) ? $resultData['productTotal'] : 0) == 0) {
            $result['shipping'] = 0;
        } else {
            $result['shipping'] = $billing['shipping'];
        }
        if ($result['shipping'] == 0) {
            //不需要凑单
            $result['category_id'] = 0;
        } else {
            $categoryInfo = OrderModel::doTableSelect('category_id', HI\TableName\CATEGORY_DESCRIPTION, ['meta_keyword' => 8]);
            $result['category_id'] = $categoryInfo['category_id'];
        }
        $result['shippingDesc'] = "（实付满20元包邮）";
        Protocol::ok($result, "", "success");
    }
    
    public function createOrderAction() {
        $data = Protocol::arguments();
        /* 接收参数 */
        $loginUid = $data->requiredInt('login_uid');//用户id
        $address = json_decode($data->required('address'), true);//物流地址
        $payMethod = $data->requiredInt('pay_method');//支付方式
        //查看优惠券是否可用
        $checkCouponId = OrderModel::checkCouponUsable($loginUid, json_decode($data->required('coupon'), true));
        //计算总金额，优惠金额
        $orderData = OrderModel::calculationAmount($loginUid, json_decode($data->required('coupon'), true), json_decode($data->required('products'), true), 1, 0, $data);
        if (isset($orderData['status']) && $orderData['status'] == 0) {
            Protocol::notFound(new StdClass, $orderData['message']);
            return;
        }
        $billingTotal = 0;
        if ($orderData == 0) {
            Protocol::notFound(new StdClass, '商品不存在');
            return;
        }
        array_walk($orderData['useCoupon'], function($v, $k) use(&$couponTotal){
            array_walk($v, function($v4, $k4) use(&$couponTotal){
                $couponTotal = (string)$v4['recommend'] + $couponTotal; //优惠价格
            });
        });
        $billing = self::getBilling($orderData['productTotal'], $couponTotal, $billingTotal);
        if ($orderInfo = Yii::$app->redis->get($loginUid.sha1($data->required('products')))) {
            Protocol::ok(json_decode($orderInfo, true), '', 'success');
            return;
        }
        //入库
        Execution::autoTransaction(Yii::$app->db, function() use($loginUid, $address, $orderData, $payMethod, $billing, $couponTotal, $data) {
            $connection = Yii::$app->db;
            $orderInfoId = self::orderInsert($loginUid, $orderData, $payMethod, $address, $billing['billingTotal'], $billing['shipping'], $couponTotal);
            Order::insertOrderLog($orderInfoId, ['status' => 1, 'status_desc' => "未付款"], $loginUid);
            $results = self::orderSplittingAndProductInsert($orderInfoId, $orderData);
            self::updateProducts($results['location1'], $orderInfoId, 0);
            self::updateProducts($results['location2'], $orderInfoId, 1);
            if (!empty($orderData['useCoupon'])) {
                self::orderCoupon($orderData, $orderInfoId);
            }
            $orderInfo = OrderModel::doTableSelect('order_number, total, order_status_id', HI\TableName\ORDER, ['order_id' => $orderInfoId]);
            $result = [];
            $result['order_id'] = $orderInfoId;//订单id
            $result['order_number'] = $orderInfo['order_number'];//订单号
            $result['total'] = $orderInfo['total'];//结算金额
            $result['order_status'] = $orderInfo['order_status_id'];//订单状态
            Protocol::ok($result, '', 'success');
            self::setOrderProducts($loginUid.sha1($data->required('products')), json_encode($result));
        });
    }
    
    private function setOrderProducts($key, $val)
    {
        //将订单信息写入redis
        Yii::$app->redis->set($key, $val);
        Yii::$app->redis->expire($key, HI\Config\Redis\ORDER_PRODUCTS);
        return true;
    }
    
   //订单详情 
   public function orderDetailAction()
   {
       $data = Protocol::arguments();
       $query = new Query;
       $orderData = $product = $result = $codes = [];
       $orderData = self::orderDeData($data->requiredInt('login_uid'), $data->requiredInt('order_id'), $query);
       if (empty($orderData)) {
           Protocol::ok(new StdClass, '', 'success');
           return;
       }
       foreach ($orderData as $k => $v) {
           $product['product_id'] = $v["product_id"];
           $product['type'] = $v["type"];
           $productNameAndImg = self::productNameAndImg($v["isbn"], $v['name'], $v['image']);
           $product['image'] = $productNameAndImg['image'];
           $product['name'] = $productNameAndImg['name'];
           $product['size'] = $v["upc"] . 'x' . $v["ean"] . 'x' . $v["jan"];
           $product['quantity'] = $v["quantity"];
           $product['total'] = round($v["total"], 2);
           $product['price'] = round($v["price"], 2);
           $product['unit'] = '/' . $v["sku"];
           $product['code'] = $v["code"];
           $product['order_child_id'] = $v["order_child_id"];
           $product['payload'] = empty(json_decode($v["payload"], 1)) ? '' : json_decode($v["payload"], 1);
           $product['shipping_id'] = $v["shipping_id"];
           switch ($v["code"]){
               case 0:
               case 1:
               default:
                    if (!in_array($v["product_id"], $codes)) {
                        $result['list'][$v["code"]][] = $product;
                        $codes[] = $v["product_id"];
                    }
           }
           $newResults['shipping_firstname'] = $v["shipping_firstname"];
           $newResults['telephone'] = $v["telephone"];
           $newResults['address'] = $v["shipping_country"].$v["shipping_city"].$v["shipping_zone"].$v["shipping_address_1"];
           $newResults['order_id'] = $v["order_id"];
           $newResults['order_number'] = $v["order_number"];
           $newResults['create_time'] = strtotime($v["date_added"]) * 1000;
           $status = self::orderStatus($v["order_status_id"]);
           $newResults['order_status_id'] = $status;
           if ($v['order_status_id'] != self::$orderStatusShipped) {
               $newResults['order_status_desc'] = self::$orderStatusDesc;
           }
           $newResults['pay_method'] = $v["payment_method"];
           $newResults['total'] = round($v["order_total"], 2);
           $newResults['coupon_total'] = isset(explode('_', $v["custom_field"])[0]) ? explode('_', $v["custom_field"])[0]: 0;
           $newResults['shipping'] =  isset(explode('_', $v["custom_field"])[1]) ? explode('_', $v["custom_field"])[1] : 0;
           $newResults['product_total'] = $v["order_total"] + $newResults['coupon_total'] - $newResults['shipping'];
           $newResults['ratings_content'] = self::$ratingsContent;
           $newResults['share'] = $result['share'] = Order::orderShareInfo(1, $v["order_id"]);
       }
       foreach ($result['list'] as $order) {
           $newResult['list'][]['product_list'] = $order;
       }
       foreach ($newResult['list'] as $k => $v) {
           foreach ($v['product_list'] as $key => $val) {
               if (!empty($val['shipping_id'])) {
                   if (is_array($val['payload'])) {
                       $shippingLatest = empty(end($val['payload'])['AcceptStation']) ? self::splittingCompany($val['shipping_id']) : end($val['payload'])['AcceptStation'];
                   } else {
                       $shippingLatest = self::splittingCompany($val['shipping_id']);
                   }
               } else {
                   $shippingLatest = self::$shippingLatest;
               }
               $newResults['list'][$k]['shipping_latest'] = $shippingLatest;
               $newResults['list'][$k]['isShipInfo'] = $shippingLatest == self::$shippingLatest ? 0 : 1;
               $newResults['list'][$k]['shipping_info'] = HI\Config\INIT_DOMAIN.'/order/logistics.html?orderId='.$val['order_child_id'];
               $newResults['list'][$k]['product_list'] = $v['product_list'];
           }
       }
       Protocol::ok($newResults, '', 'success');
   }
   
   private function splittingCompany($shippingId) 
   {
       $connection = Yii::$app->db;
       $sql = "SELECT company FROM `order_splitting` AS s INNER JOIN `express_code` AS e ON s.splitting_company = e.code WHERE shipping_id = " . $shippingId . " GROUP BY shipping_id";
       $compayNmae = $connection->createCommand($sql)->queryOne();
       return $compayNmae['company'] . '  ' . $shippingId;
   }
   private function productNameAndImg($isbn, $name, $image)
   {
       if ($isbn) {
           $proDescName = OrderModel::doTableSelect('name', HI\TableName\PRODUCT_DESCRIPTION, ['language_id' => 2, 'product_id' => $isbn]);
           $productName = $proDescName['name'] . '(' . $name . ')';
       } else {
           $productName = $name;
       }
       if ($productImg = self::getProductImage($isbn)) {
           $productImage = 'admin/images/' . $productImg["image"] . '.jpg';
       } else {
           $productImage = 'admin/images/' . $image . '.jpg';
       }
       return ['name' => $productName, 'image' => $productImage];
   }

   private function orderDeData($loginUid, $orderId, $query)
   {
       $orderData = $query->select('p.product_id, o.telephone, o.shipping_firstname, o.shipping_country, o.shipping_city, o.shipping_zone, o.shipping_address_1, o.order_id, o.order_number, o.date_added, o.payment_method, o.total as order_total, o.custom_field,
           o.date_modified as date_modified, o.order_status_id, p.quantity, p.total, p.price, s.code, s.payload, s.shipping_id, s.order_child_id, d.name, r.upc, r.ean,
           r.jan, r.sku, r.isbn, r.image, cd.meta_keyword as type')
                  ->from(HI\TableName\ORDER ." as o")
                  ->leftJoin(HI\TableName\ORDER_SPLITTING ." as s", "o.order_id = s.order_id")
                  ->leftJoin(HI\TableName\ORDER_PRODUCT ." as p", "s.order_child_id = p.order_child_id")
                  ->leftJoin(HI\TableName\PRODUCT_DESCRIPTION ." as d", "p.product_id = d.product_id")
                  ->leftJoin(HI\TableName\PRODUCT ." as r", "d.product_id = r.product_id")
                  ->leftJoin(HI\TableName\PRODUCT_TO_CATEGORY." as c", "r.product_id = c.product_id")
                  ->leftJoin(HI\TableName\CATEGORY_DESCRIPTION." as cd", "c.category_id = cd.category_id")
                  ->where(['o.customer_id' => $loginUid, 'o.order_id' => $orderId, 'd.language_id' => 2, 'cd.language_id' => 2])
                  ->andWhere('cd.meta_keyword != 8')
                  ->orderBy(["p.order_product_id" => SORT_DESC])->all();
       return $orderData;
   }
   
   public function orderDelAction()
   {
       $data = Protocol::arguments();
       /* 接收参数 */
       $loginUid = $data->requiredInt('login_uid');//登录uid
       $orderId = $data->requiredInt('order_id');//订单id
       $orderNumber = $data->required('order_number');//订单号
       if ($orderInfo = OrderModel::doTableSelect('order_id', HI\TableName\ORDER, ['order_id' => $orderId, 'order_number' => $orderNumber, 'customer_id' => $loginUid, 'order_status_id' => self::$orderStatusShipped])) {
            $order = OrderModel::doTableUpdate(HI\TableName\ORDER, ['order_status_id' => self::$orderStatusDel], ['order_id' => $orderInfo]);
       } else {
           Protocol::notFound(new StdClass, '该订单暂时不能删除');
           return;
       }
       Protocol::ok('', '', 'success');
   }
   
   private function orderStatus($orderStatusId)
   {
       switch ($orderStatusId){
           case 2:
               $status = '照片未上传';
               break;
           case 3:
               $status = '订单异常';
               break;
           case 7:
               $status = '已发货';
               break;
           default:
               $status = '未发货';
       }
       return $status;
   }
   
   private function getBilling($productTotal, $recommendCount, $billingTotal)
   {
       switch (bccomp(($productTotal - $recommendCount), OrderModel::FREEPOST)) {
           case 0:
           case 1:
               //包邮
               $billingTotal = $productTotal - $recommendCount;
               $shipping = 0;
               break;
           case -1:
               if ($productTotal == 0) {
                   $billingTotal = 0;
               } else {
                   //需要计算抵达某省的邮费
                   $billingTotal = $productTotal - $recommendCount < 0 ? OrderModel::SHIPPING : ($productTotal - $recommendCount) + OrderModel::SHIPPING;
               }
               $shipping = OrderModel::SHIPPING;
       }
       return ['shipping' => $shipping, 'billingTotal' => $billingTotal];
   }
   
   private function updateProducts($location, $orderInfoId, $code)
   {
       foreach ($location as $k => $v) {
           return OrderModel::doTableUpdate(HI\TableName\ORDER_SPLITTING, ['product_ids' => json_encode($v)], ['order_id' => $orderInfoId, 'code' => $code]);
       }
   }
   
   private function orderInsert($loginUid, $orderData, $payMethod, $address, $billingTotal, $shipping, $couponTotal)
   {
       $province = Yii::$app->redis->hget(HI\User\CITY_CODE, $address['province']);
       $city = Yii::$app->redis->hget(HI\User\CITY_CODE, $address['city']);
       $district = UserModel::doGetDistrict($address['district']);
       $yKName = explode('客', OrderModel::doTableSelect('firstname', HI\TableName\CUSTOMER, ['customer_id' => $loginUid])['firstname']);
       if (isset($yKName[1])) {
           $Name = $yKName[1];
       } else {
           $Name = $yKName[0];
       }
       $parameter = [
           'order_number' => 'Hi' . $Name . date('YmdHis'), //date('Ymd') . explode('-', uuid_create())[0] . explode('-', uuid_create())[4]
           'customer_id' => $loginUid,
           'custom_field' => $couponTotal .'_'. $shipping,
           'telephone' => $address['telephone'],
           'payment_method' => $payMethod,
           'shipping_firstname' => $address['firstname'],
           'shipping_address_1' => $address['address_desc'],
           'shipping_country' => $province,
           'shipping_country_id' => $address['province'],
           'shipping_city' => $city,
           'shipping_zone' => $district,
           'shipping_zone_id' => $address['district'],
           'total' => $billingTotal,
           'order_status_id' => 1,
           'date_added' => date('Y-m-d H:i:s', time()),
           'date_modified' => date('Y-m-d H:i:s', time()),
       ];
       //订单表插入数据
       return OrderModel::doTableInsert(HI\TableName\ORDER, $parameter);
   }
   
   private function orderCoupon($orderData, $orderInfoId)
   {
       foreach ($orderData['useCoupon'] as $key3 => $val3) {
           foreach ($val3 as $k1 => $v1) {
               //优惠券信息
               $couponInfo = OrderModel::doTableSelect('name', HI\TableName\COUPON, ['coupon_id' => $k1]);
               $parameter = [
                   'order_id' => $orderInfoId,
                   'coupon_id' => $k1,
                   'name' => $couponInfo['name'],
                   'value' => $v1['recommend'], //需要算出来
               ];
               OrderModel::doTableInsert(HI\TableName\ORDER_COUPON, $parameter);
           }
       }
   }
   
   private function productShipingCity($productUseCou, $orderInfoId)
   {
       $location2 = $location1 = $location3 = $location4 = $location5 = $location6 = $location7 = $location8 = $location9 = $location10 = $location11 = $newLocation1 = $newLocation2 = $codes = [];
       //查看优惠券是否有指定城市
       foreach ($productUseCou as $k => $v) {
               //有指定城市，商品则归到该城市
               $couPonCity = OrderModel::doTableSelect('city_code', HI\TableName\COUPON, ['coupon_id' => $v]);
               $productInfo = OrderModel::doTableSelect('location, quantity_sd, quantity_zj', HI\TableName\PRODUCT, ['product_id' => $k]);
               //查看商品库存是够充足，如果有一方不充足，直接从另一方发货
               if ($productInfo && ($productInfo['quantity_sd'] <= self::$quantity || $productInfo['quantity_zj'] <= self::$quantity)) {
                   $resultQuantity = self::getProductQuantity($productInfo['quantity_sd'], $productInfo['quantity_zj'], $k, $codes, $location1, $location2);
                   $location11[] = $resultQuantity['location1'];
                   $location10[] = $resultQuantity['location2'];
                   $codes[] = $k;
               } else if ($productInfo && $productInfo['location'] != '') {
                    //无指定城市，查看该商品发货地, 有指定发货地，商品则归到该城市
                    $resultLocation = self::getProductCity($productInfo['location'], $k, $codes, $location1, $location2);
                    $location5[] = $resultLocation['location1'];
                    $location6[] = $resultLocation['location2'];
                    $codes[] = $k;
               }  else if ($couPonCity && $couPonCity['city_code'] != 2) {
                    $resultCity = self::getProductCity($couPonCity['city_code'], $k, $codes, $location1, $location2);
                    $location3[] = $resultCity['location1'];
                    $location4[] = $resultCity['location2'];
                    $codes[] = $k;
               } else if ($orderReceipt = OrderModel::doTableSelect('shipping_country', HI\TableName\ORDER, ['order_id' => $orderInfoId])) {
                    //无指定城市，查看用户的收货地是否为江浙沪
                    //如果为江浙沪
                    if (in_array($orderReceipt['shipping_country'], HI\Config\COMMON_CITY)) {
                      $connection = Yii::$app->db;
                      $date = date('Y-m-d');
                      $sql = "SELECT order_id FROM ".HI\TableName\ORDER_SPLITTING." WHERE CODE = 1 AND DATE(date_added)='$date'";
                      //查出浙江当日总订单数
                      $shipttingCount = $connection->createCommand($sql)->queryAll();
                      //要判断杭州当日订单是否大于100笔,如果小于100则归到杭州，否则归到山东
                      if (count($shipttingCount) < HI\Config\ORDER_COUNT) {
                          if (!in_array($k, $codes)) {
                              $codes[] = $k;
                              $location8[] = $k;
                          }
                      } else {
                          if (!in_array($k, $codes)) {
                              $codes[] = $k;
                              $location9[] = $k;
                          }
                      }
                    //否则不成立则归到山东
                    } else {
                      if (!in_array($k, $codes)) {
                          $codes[] = $k;  
                          $location7[] = $k;
                      }
                    }
              }
       }
       $newLocation1[0] = array_merge(self::withArray($location3), self::withArray($location5), self::withArray($location11), $location7, $location9);
       $newLocation2[1] = array_merge(self::withArray($location4), self::withArray($location6), self::withArray($location10), $location8);
       return ['location1' => $newLocation1, 'location2' => $newLocation2];
   }
   
   private function getProductQuantity($productCodeSd, $productCodeZj, $k, $codes, $location1, $location2)
   {
       if (($productCodeSd <= self::$quantity && $productCodeZj <= self::$quantity)) {
           if (!in_array($k, $codes)) {
               $codes[] = $k;
               $location2[1] = $k;
           } 
       } else if ($productCodeZj <= self::$quantity) {
           if (!in_array($k, $codes)) {
               $codes[] = $k;
               $location1[0] = $k;
           }
       } else if ($productCodeSd <= self::$quantity) {
           if (!in_array($k, $codes)) {
               $codes[] = $k;
               $location2[1] = $k;
           }
       }
       return ['location1' => $location1, 'location2' => $location2, 'codes' => $codes];
   }
   
   private function withArray($locations)
   {
       $location = [];
       foreach ($locations as $k2 => $v2) {
           foreach ($v2 as $k3 => $v3) {
               $location[] = $v3;
           }
       }
       return $location;
   }
   
   private function getProductCity($couPonCity, $k, $codes, $location1, $location2)
   {
       switch ($couPonCity) {
           case 0:
               if (!in_array($k, $codes)) {
                   $codes[] = $k;
                   $location1[$couPonCity] = $k;
               }
               break;
           case 1:
               if (!in_array($k, $codes)) {
                   $codes[] = $k;
                   $location2[$couPonCity] = $k;
               }
               break;
       }
       return ['location1' => $location1, 'location2' => $location2, 'codes' => $codes];
   }
   
   private function orderSplittingAndProductInsert($orderInfoId, $orderData)
   {
       $productIdAndorderSplittingId = [];
       $productUseCou = [];
       foreach ($orderData['productUseCou'] as $k1 => $v1) {
           $productUseCou = $v1;
       }
       $productUseCous = $productUseCou + $orderData['productNumAndPrice'];
       $resultLocaion = self::productShipingCity($productUseCous, $orderInfoId);
       foreach ($orderData['productNumAndPrice'] as $key => $val) {
          foreach ($resultLocaion as $k =>$v) {
              foreach ($v as $key1 => $val1) {
                  if (!empty($val1)) {
                      //物流信息表
                      $parameter = [
                          'order_id' => $orderInfoId,
                          'code' => $key1,
                          'date_added' => date('Y-m-d H:i:s', time()),
                      ];
                      if (!$orderSplittingId = OrderModel::doTableSelect('order_child_id', HI\TableName\ORDER_SPLITTING, ['order_id' => $orderInfoId, 'code' => $key1])) {
                          $orderSplittingId = OrderModel::doTableInsert(HI\TableName\ORDER_SPLITTING, $parameter);
                          if ($key1 == 0) {
                              //订单编号表
                              OrderModel::doTableInsert(HI\TableName\ORDER_NUMBERING_SD, ['order_child_id' => $orderSplittingId]);
                          } else {
                              //订单编号表
                              OrderModel::doTableInsert(HI\TableName\ORDER_NUMBERING_ZJ, ['order_child_id' => $orderSplittingId]);
                          }
                      }
                      $splittingId = is_array($orderSplittingId) ? $orderSplittingId['order_child_id'] : $orderSplittingId;
                      //记录物流id和商品id
                      $productIdAndorderSplittingId = [$splittingId => $val1];
                      foreach ($productIdAndorderSplittingId as $key4 => $val4) {
                          foreach ($val4 as $key2 => $val2) {
                              if ($key == $val2) {
                                  //订单商品明细表
                                  $productDescriptionInfo = OrderModel::doTableSelect('name', HI\TableName\PRODUCT_DESCRIPTION, ['product_id' => $key]);
                                  $parameter = [
                                      'order_id' => $orderInfoId,
                                      'product_id' => $key,
                                      'name' => $productDescriptionInfo['name'],
                                      'quantity' => explode('_', $val)[0],
                                      'price' => explode('_', $val)[1],
                                      'total' => explode('_', $val)[0] * explode('_', $val)[1],
                                      'order_child_id' => $key4,
                                  ];
                                  if (!OrderModel::doTableSelect('order_product_id', HI\TableName\ORDER_PRODUCT, ['order_id' => $orderInfoId, 'product_id' => $key,
                                      'order_child_id' => $key4])) {
                                      $orderProductInfoId = OrderModel::doTableInsert(HI\TableName\ORDER_PRODUCT, $parameter);
                                  }
                              }
                          }
                      }
                  }
              }
          }
    }
    return ['location1' => $resultLocaion['location1'], 'location2' => $resultLocaion['location2']];
   }
   
   /**
    * 外部使用
    * 获取订单详细信息
    */
   public function getTradeInfoAction()
   {
       $result = Thirdparty::getTradeInfo(Protocol::requiredInt('order_id'));
       Protocol::ok($result, '', 'success');
   }
   
   /**
    * 外部使用
    * 获取订单id
    */
   public function getOrderAction()
   {
       $cursor = Protocol::optional('cursor');//游标(分页参数)
       $limit = 20;
       if (Predicates::isNotNull($cursor)) {
           $cursor = json_decode(base64_decode($cursor), true);
           $orderId  = $cursor['order_id'];
       } else {
           $orderId = PHP_INT_MAX;
       }
       $query = new Query;
       $order = [];
       $order = $query->select('order_id')
          ->from(HI\TableName\ORDER)
          ->where(['order_status_id' => 15])
          ->andWhere("order_id < ". intval($orderId))
          ->limit($limit)
          ->orderBy(["order_id" => SORT_DESC])->all();
        array_walk($order, function($v, $k) use(&$result, &$lastOrderId){
            $result['order_id'][] = $v['order_id'];
            $lastOrderId = $v['order_id'];
        });
      $result['cursor'] = base64_encode(json_encode(['order_id' => $lastOrderId]));
      Protocol::ok($result);
   }
   
   private function getProductImage($productId)
   {
       $query = new Query;
       return $productImage = $query->select('image')
       ->from(HI\TableName\PRODUCT)
       ->where(['product_id' => $productId])
       ->one();
   }
   
    public function getOrderInfoAction ()
    {
        $data = Protocol::arguments();
        $connection = Yii::$app->db;
        $sql = "select oa.total_fee as oa_total, ow.total_fee as ow_total from ".HI\TableName\ORDER." as o 
                left join ".HI\TableName\ORDER_WECHATNOTIFY." as ow on ow.out_trade_no = o.order_number 
                left join ".HI\TableName\ORDER_ALINOTIFY." as oa on oa.out_trade_no = o.order_number 
                where o.order_id = ".$data->requiredInt('order_id')." and o.customer_id = ". $data->requiredInt('login_uid');
        $orderInfo = $connection->createCommand($sql)->queryOne();
        $order['total'] = 0;
        if ($orderInfo) {
            if ($orderInfo['oa_total']) {
                $order['total'] = floatval($orderInfo['oa_total']);
            }
            if ($orderInfo['ow_total']) {
                $order['total'] = floatval($orderInfo['ow_total']/100);
            }
        }
        Protocol::ok($order);
    }
   public function testAction()
   {
       $query = new Query;
       $user = $query->select('customer_id, firstname, lastname')
       ->from(HI\TableName\CUSTOMER)
       ->where(['status' => 0])
       ->all();
       foreach ($user as $k => $v) {
           $randName = HI\User\DEFAULT_NICKNAME.substr(strtoupper(uuid_create()), -5);
           $model = self::doVerifyNickname($randName);
           $result = OrderModel::doTableUpdate(HI\TableName\CUSTOMER, ['firstname' => $model, 'lastname' => $model], ['customer_id' => $v['customer_id']]);
           echo $v['customer_id'] .'--------------';
       }
       //        echo date('Ymd') . substr(explode('.', microtime(true))[0] . explode('.', microtime(true))[1], -8) . "</br>";
       
   }
   
   private function doVerifyNickname($model)
   {
       $flag = true;
       while($flag) {
           if (CouponModel::queryOneTableInfo('firstname', HI\TableName\CUSTOMER, ['firstname' => $model])) {
               $model = HI\User\DEFAULT_NICKNAME.substr(strtoupper(uuid_create()), 0, 5);
           } else {
               $flag = false;
           }
       }
       return $model;
   }
}

?>
