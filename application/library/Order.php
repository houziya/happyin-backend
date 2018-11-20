<?php
use Yaf\Dispatcher;
use Yaf\Registry;
use yii\db\Query;
use yii\db\Expression;
class Order
{
    const ORDER_SHARE = 1;
    const PRODUCT_SHARE = 0;
    /* 红包小时时间 */
    const EXPIRE_DATE = 5;

    /* 分享渠道限制 */
    public static $updateStatus = FALSE;
    public static $productChanneLimit = 31; //商品分享
    public static $orderChanneLimit = 31; //红包 订单完成后分享
    public static $appShareChannelLimit = 31; //应用分享
    public static $shareFreeChannelLimit = 3; //终身免费分享
    public static $hzOrderStatusIds = [7, 15, 18, 19, 20];//杭州订单状态列表
    public static $hzSearchOrderStatus = [15 => "新订单", 20 => "待发货", 7 => "已发货"];//杭州订单状态列表
    public static $hzSplittingCompanys = ['SF', 'ZTO', 'YD', 'RFD', 'YTO', 'HTKY', 'EMS'];
    public static $configCompleteStatus = [7];
    public static $configProcessingStatus = [2, 5, 15, 18, 19, 20];
    public static $configHzProcessingStatus = [15, 18, 19, 20];
    public static $sdCode = 0;
    public static $zjCode = 1;
    public static $productLocation = [0 => "山东", 1 => '浙江'];
    public static $freeShipping = 20;//满20包邮
    public static $postage = "8.00";//邮费
    /*
     * 验证订单状态
     *  */
    public static function verifyOrderStatus($orderId, $status) {
        if(!is_int($status)) {
            $status = self::getOrderStatusId($status);
        }
        return ((new Query())
            ->select("order_status_id")
            ->from(HI\TableName\ORDER)
            ->where("order_number = '" . $orderId . "'")
            ->one()["order_status_id"]) == $status;
    }
    
    public static function getOrderStatus($orderId)
    {
        return ((new Query())
                ->select("order_status_id")
                ->from(HI\TableName\ORDER)
                ->where("order_number = '" . $orderId . "'")
                ->one()["order_status_id"]);
    }
    /*
     * 验证订单所属人
     *  */
    public static function verifyOrderOwner($orderId, $uid) {
        return ((new Query())
            ->select("customer_id")
            ->from(HI\TableName\ORDER)
            ->where("order_number = '" . $orderId . "'")
            ->one()["customer_id"]) == $uid;
    }
    /*
     * 订单分享信息
     *  */
    public static function orderShareInfo(...$parameter)
    {
        $type = $parameter[0];
        $date = CouponModel::queryOneTableInfo('date_added', CouponModel::$order, ['order_id' => $parameter[1]]);
        $exprieDate = empty($date['date_added']) ? '' : ((self::EXPIRE_DATE * 60 * 60) + strtotime($date['date_added'])) * 1000;
        $content = [
            "title" => "分享现金劵给小伙伴",
            "content" => "可以获得超值现金大礼包哦~",
            "expire_date" => $exprieDate
        ];
        switch ($type) {
            case self::PRODUCT_SHARE :
                $content['share_limit'] = self::$productChanneLimit;
                return $content;
                break;
            case self::ORDER_SHARE :
                $content['share_limit'] = self::$orderChanneLimit;
                return $content;
                break;
            default :
                $content['share_limit'] = self::$orderChanneLimit;
                return $content;
                break;
        }
    }

    /* 根据订单状态文字查找 order_status_id */
    public static function getOrderStatusId($statusString) {
        return (new Query())
            ->select("order_status_id")
            ->from("order_status")
            ->where("name = '" . $statusString . "' and language_id = 2")
            ->one()["order_status_id"];
    }
    
    /* 订单更新日志*/
    public static function insertOrderLog($orderId, $payload = [], $operator = 0) {
        DataBase::doTableInsert(HI\TableName\ORDER_LOG, ['order_id' => $orderId, 'payload' => json_encode($payload), 'operator' => $operator, 'date_added' => date("Y-m-d H:i:s", time())]);
    }
    
    /* 获取杭州订单状态 */
    public static function getHzOrderStatus() {
        return (new Query())
        ->select("order_status_id, name")
        ->from("order_status")
        ->where("language_id = :language_id", [':language_id' => 2])
        ->andWhere(['in', 'order_status_id', self::$hzOrderStatusIds])
        ->all();
    }
    
    /* 检查订单commit商品 是否一致 */
    public static function checkOrderProduct($orderId, $orderInfo, $paidSink = null) {
        if (Predicates::isNotEmpty($orderId) && Predicates::isNotEmpty($orderInfo)) {
            $orderPictures = json_decode($orderInfo);
            $paidProducts = array_reduce(DataBase::getTableDataRows(HI\TableName\ORDER_PRODUCT, "order_id = :o_id", [':o_id' => $orderId], "product_id, quantity"), function($carry, $payment) {
                $carry[$payment["product_id"]] = $payment["quantity"];
                return $carry;
            }, []);
            if (Predicates::isNotNull($paidSink)) {
                $paidSink($paidProducts);
            }
            $countPhotos = function($photos) {
                return array_reduce($photos, function($count, $photo) { return $count + $photo->count; }, 0);
            };
            $count = [];
            foreach ($orderPictures as $product) {
                if (!array_key_exists($product->pid, $paidProducts)) {
                    return false;
                }
                $paid = $paidProducts[$product->pid];
                $limit = [
                    HI\Config\Product\LOMO_CARDS_PRODUCT_ID => 24,
                    HI\Config\Product\PHOTO_CARDS_PRODUCT_ID => 14,
                    HI\Config\Product\BIG_POSTER => 54,
                    HI\Config\Product\GRID_ONE_BLACK => 1,
                    HI\Config\Product\GRID_ONE_WHITE => 1,
                    HI\Config\Product\GRID_TWO_BLACK => 4,
                    HI\Config\Product\GRID_TWO_WHITE => 4,
                    HI\Config\Product\GRID_THREE_BLACK => 9,
                    HI\Config\Product\GRID_THREE_WHITE => 9,
                    HI\Config\Product\GRID_FOUR_BLACK => 16,
                    HI\Config\Product\GRID_FOUR_WHITE => 16,
                    HI\Config\Product\PICTURE_ALBUM => 6
                ];
                $numPhotos = $countPhotos($product->photos);
                if (array_key_exists($product->pid, $limit) && $countPhotos($product->photos) > $limit[$product->pid]) {
                    return false;
                }
                if ($product->pid == HI\Config\Product\FIVE_INCH || $product->pid == HI\Config\Product\SIX_INCH) {
                    @$count[$product->pid] += $countPhotos($product->photos);
                } else {
                    @$count[$product->pid] += $product->count;
                }
            }
            foreach ($count as $productId => $cnt) {
                if ($cnt > $paidProducts[$productId]) {
                    return false;
                }
            }
        }
        return true;
    }
    /* 杭州山东后台设置每页数据数量 */
    public static function setLimitAdmin($limit = 20) {
        return yii::$app->redis->set("config_limit_admin", $limit);
    }
    
    public static function getLimitAdmin() {
        return yii::$app->redis->get("config_limit_admin");
    }
    /* 发货 */
    public static function shipping($orderIds = null, $code = 0, $userId = 0, $userType = 0) {
        if($orderIds && is_array($orderIds)) {
            $result = DataBase::doTableUpdate('order', ['order_status_id' => 7], ['in', 'order_id', $orderIds]);
            $resultSplitting = DataBase::doTableUpdate('order_splitting', ['order_status_id' => 7], "order_id in (" . implode(',', $orderIds) . ") and code = " . $code);
            if($result && $resultSplitting) {
                foreach ($orderIds as $orderId) {
                    $orderInfo = DataBase::getTableFields('order', "order_id = :o_id", [":o_id" => $orderId], ['order_number', 'customer_id']);
                    $payload = [
                        'uid' => $orderInfo['customer_id'],
                        'message' => '您的订单已发货，点击查看详情',
                        'type' => MiPush::TO_EXPRESS,
                        'payload' => ['type' => 2, 'order_id' => $orderId]
                    ];
                    MiPush::submitWorks($payload);
                    Order::insertOrderLog($orderId, ['status' => 7, 'status_desc' => "已发货", "user_type" => $userType], $userId);
                }
            }
        }
    }
    /* 商品发货地 */
    public static function getProductLocation($code) {
        switch ($code) {
            case strval(self::$sdCode):
                return self::$productLocation[$code];
                break;
            case self::$zjCode:
                return self::$productLocation[$code];
                break;
            default :
                return "";
                break;
        }
    }
}
?>
