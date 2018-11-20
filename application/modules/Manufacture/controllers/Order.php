<?php
use Yaf\Controller_Abstract;
use yii\db\Query;

class OrderController extends Controller_Abstract
{
    private static $productCache = [];

    private static function clearProduct()
    {
        array_splice(self::$productCache, 0);
    }

    private static function getProduct($productId)
    {
        if (!array_key_exists($productId, self::$productCache)) {
            self::$productCache[$productId] = Yii::$app->db->createCommand("SELECT mpn, standard_explain, model, image FROM " . HI\TableName\PRODUCT . " WHERE product_id = " . $productId)->queryOne();
        }
        return self::$productCache[$productId];
    }

    private static function getImage($productId)
    {
        return self::getProduct($productId)["image"];
    }

    private static function getModel($productId)
    {
        return self::getProduct($productId)["model"];
    }

    private static function getSKU($productId)
    {
        return self::getProduct($productId)["standard_explain"];
    }

    private static function getSKUId($productId)
    {
        return self::getProduct($productId)["mpn"];
    }

    public function pullAction()
    {
        $manufactureId = Preconditions::checkNotNull(HI\Config\MANUFACTURE[Protocol::required("token")]);
        $cursor = Protocol::optional("cursor", "");
        $limit = Protocol::optionalIntInRange("limit", 10, 100, 10);
        if (Predicates::isNotEmpty($orderNumber = Protocol::optional("orderNumber"))) {
            $newCursor = "";
            $orders = array_reduce(Yii::$app->db->createCommand("SELECT s.order_child_id from " . HI\TableName\ORDER_SPLITTING . " s left join " . HI\TableName\ORDER . 
                " o on s.order_id = o.order_id where o.order_number = :orderNumber")->bindValue(":orderNumber", $orderNumber)->queryAll(), function($carry, $order) {
                $carry[] = $order["order_child_id"];
                return $carry;
            });
        } else {
            $newCursor = uuid_create();
            $orders = Execution::autoTransaction(Yii::$app->db, function() use ($manufactureId, $cursor, $newCursor, $limit) {
                if (Predicates::isNotEmpty($cursor)) {
                    Yii::$app->db->createCommand("UPDATE " . HI\TableName\ORDER_SPLITTING . " SET fetched = :cursor WHERE " . 
                        "order_child_id in (SELECT order_child_id from " . HI\TableName\MANUFACTURE_ORDER . " WHERE fetch_cursor " . 
                        "= :cursor and code = :code) and parcle IS NOT NULL")->bindValue(":cursor", $cursor)->
                        bindValue(":code", $manufactureId)->execute();
                    Yii::$app->db->createCommand("REPLACE INTO " . HI\TableName\MANUFACTURE_ORDER_ARCHIVE . " (order_child_id, order_id, code, " . 
                        "fetch_cursor, fetch_time) (SELECT order_child_id, order_id, code, fetch_cursor, fetch_time FROM " . 
                        HI\TableName\MANUFACTURE_ORDER . " WHERE fetch_cursor = :cursor and code = :code)")->bindValue(":cursor", $cursor)->
                        bindValue(":code", $manufactureId)->execute();
                    Yii::$app->db->createCommand("DELETE FROM " . HI\TableName\MANUFACTURE_ORDER . " WHERE fetch_cursor = :cursor and code = :code")->
                        bindValue(":cursor", $cursor)->bindValue(":code", $manufactureId)->execute();
                }
                if (Yii::$app->db->createCommand("INSERT INTO " . HI\TableName\MANUFACTURE_ORDER . " (order_child_id, order_id, code, fetch_cursor) " .
                    "(SELECT order_child_id, order_id, code, :cursor FROM " . HI\TableName\ORDER_SPLITTING . " WHERE fetched IS NULL AND parcle IS NOT NULL " . 
                    "AND code = :code ORDER BY order_child_id LIMIT :limit)")->bindValue(":cursor", $newCursor)->bindValue(":code", $manufactureId)->
                    bindValue(":limit", $limit)->execute() > 0) {
                    return array_reduce((new Query())->select("order_child_id")->from(HI\TableName\MANUFACTURE_ORDER)->
                        where(["fetch_cursor" => $newCursor, "code" => $manufactureId])->all(), function($carry, $order) {
                        $carry[] = $order["order_child_id"];
                        return $carry;
                    });
                }
                return [];
            });
        }
        if (Predicates::isNotEmpty($orders)) {
            $orders = Yii::$app->db->createCommand("SELECT s.product_ids, s.splitting_company, s.shipping_id, s.code, s.parcle as parcel, o.*, op.*, c.firstname as customer from " .
                HI\TableName\ORDER . " o left join " . HI\TableName\ORDER_SPLITTING . " s on s.order_id = o.order_id left join " . HI\TableName\CUSTOMER . 
                " c on c.customer_id = o.customer_id right join " . HI\TableName\ORDER_PRODUCT . " op on op.order_id = o.order_id where s.order_child_id in (" . 
                implode(",", $orders) . ")")->queryAll();
            $result = [];
            $lastProducts = [];
            $lastTotal = 0;
            $lastOrderId = null;
            $lastOrder = null;
            foreach ($orders as $currentOrder) {
                $productId = @$currentOrder['product_id'];
                $productIdSet = json_decode($currentOrder['product_ids'], true);
                if (Predicates::isNull($productIdSet) || !in_array($productId, $productIdSet)) {
                    continue;
                }
                $quantity = $currentOrder['quantity'];
                if (array_key_exists($productId, HI\Config\Product\THANKS_CARD_SETTING) && $productId != HI\Config\Product\PHOTO_CARDS_PRODUCT_ID && $productId != HI\Config\Product\LOMO_CARDS_PRODUCT_ID) {
                    $quantity += 1;
                }
                $orderId = $currentOrder['order_id'];
                if ($orderId != $lastOrderId) {
                    if (Predicates::isNotNull($lastOrderId)) {
                        $result[] = [
                            "orderNumber" => $currentOrder["order_number"],
                            "customer" => $currentOrder["customer"],
                            "purchaseTime" => $currentOrder['date_added'],
                            "orderStatus" => "已支付",
                            "products" => $lastProducts,
                            "shipping" => [
                                "name" => $currentOrder['shipping_firstname'],
                                "phone" => $currentOrder['telephone'],
                                "province" => $currentOrder['shipping_country'],
                                "city" => $currentOrder['shipping_city'],
                                "county" => $currentOrder['shipping_zone'],
                                "address1" => $currentOrder['shipping_address_1'],
                                "address2" => $currentOrder['shipping_address_2'],
                            ],
                            "grandTotal" => $lastTotal,
                            "parcel" => "http://" . HI\Config\DOWNLOAD_DOMAIN . "/order/parcel/" . $currentOrder["parcel"] . ".zip"
                        ];
                        $lastTotal = 0;
                        $lastProducts = [];
                    }
                    $lastOrderId = $orderId;
                }
                $subtotal = $quantity * $currentOrder['price'];
                $lastProducts[] = [
                    "model" => self::getModel($productId),
                    "price" => $currentOrder['price'],
                    "sku" => self::getSKU($productId),
                    "quantity" => $quantity,
                    "skuId" => self::getSKUId($productId),
                    "subTotal" => $subtotal
                ];
                $lastTotal += $subtotal;
                $lastOrder = $currentOrder;
            }
            if (Predicates::isNotNull($lastOrder)) {
                $currentOrder = $lastOrder;
                $result[] = [
                    "orderNumber" => $currentOrder["order_number"],
                    "customer" => $currentOrder["customer"],
                    "purchaseTime" => $currentOrder['date_added'],
                    "orderStatus" => "已支付",
                    "products" => $lastProducts,
                    "shipping" => [
                        "name" => $currentOrder['shipping_firstname'],
                        "phone" => $currentOrder['telephone'],
                        "province" => $currentOrder['shipping_country'],
                        "city" => $currentOrder['shipping_city'],
                        "county" => $currentOrder['shipping_zone'],
                        "address1" => $currentOrder['shipping_address_1'],
                        "address2" => $currentOrder['shipping_address_2'],
                    ],
                    "grandTotal" => $lastTotal,
                    "parcel" => "http://" . HI\Config\DOWNLOAD_DOMAIN . "/order/parcel/" . $currentOrder["parcel"] . ".zip"
                ];
            }
            $orders = $result;
        }

        Protocol::ok(["cursor" => $newCursor, "orders" => $orders]);
    }
    
    public function expressAction ()
    {
        $manufactureId = Preconditions::checkNotNull(HI\Config\MANUFACTURE[Protocol::required("token")]);
    }
}
