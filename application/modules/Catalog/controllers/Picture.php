<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\web\Cookie;
use yii\web\Request;

class PictureController extends Controller_Abstract
{
    public static $appUrl = HI\Config\INIT_DOMAIN;
    public static $statusVerifyNotUploaded = 0;
    public static $prefixOrderPicture = "order/picture/";
    public static $suffixOrderPicture = ".jpg";
    public static $prefixUpload = HI\Config\QCloud\COS_UPLOAD . HI\Config\QCloud\APP_ID . '/' . HI\Config\QCloud\PUBLIC_BUCKET .'/';
    public static $tablePictureVerify = HI\TableName\ORDER_PICTURE_VERIFY;
    public function signAction() {
        $orderId = Protocol::required('order_id');//订单 id
        $sha1 = Protocol::required('sha1');//图片sha1值
        Execution::autoTransaction(Yii::$app->db, function() use ($orderId, $sha1) {
            $result = [];
            $randName = CosFile::randPicName($orderId);
            if(intval(DataBase::getTableWhereCount(self::$tablePictureVerify, "sha1=:sha1", [':sha1' => $sha1])) > 0) {
                $isCommitInfo = DataBase::getTableFields(self::$tablePictureVerify, 'sha1=:sha1', [':sha1' => $sha1], ['status', 'path']);
                if(intval($isCommitInfo['status']) === self::$statusVerifyNotUploaded) {
                    //照片未上传过
                    $result['sign'] = CosFile::getSingleSign(self::$prefixOrderPicture . $randName . self::$suffixOrderPicture);
                    $result['path'] = self::$prefixUpload . self::$prefixOrderPicture . $randName . self::$suffixOrderPicture; 
                }else {
                    //照片已上传过
                    $result['image_url'] = self::$prefixOrderPicture . $isCommitInfo['path'] . self::$suffixOrderPicture;
                }
            }else {
                DataBase::doTableInsert(self::$tablePictureVerify, ['sha1' => $sha1, 'path' => $randName]);
                $result['sign'] = CosFile::getSingleSign(self::$prefixOrderPicture . $randName . self::$suffixOrderPicture);
                $result['path'] = self::$prefixUpload . self::$prefixOrderPicture . $randName . self::$suffixOrderPicture;
            }
            Protocol::ok($result);
        });
    }
    
    public function commitAction() {
        /* 接收参数 */
        $data = Protocol::arguments();
        Execution::autoTransaction(Yii::$app->db, function() use ($data) {
            $loginUid = $data->requiredInt('login_uid');//登录uid
            $sessionKey = $data->required('session_key');//session_key
            $orderId = $data->required('order_id');//订单 id
            $orderNumber = $data->required('order_number');//订单号
            $orderInfo = $data->optional('order_info');//订单 信息(包括照片信息)
            //$productId = $data->requiredInt('product_id');//商品 id
            $result = [];
            $notice = "";
            if(Order::verifyOrderOwner($orderNumber, $loginUid)) {
                $orderStatusId = Order::getOrderStatus($orderNumber);
                if ($orderStatusId < 2) {
                    $notice = "the order status is not normal";
                    Protocol::forbidden(new stdClass(), $notice);
                }
                if ($orderStatusId > 2) {
                    $result['order_id'] = $orderId;
                    $result['share'] = Order::orderShareInfo(1, $orderId);
                    Protocol::ok($result);
                    exit;
                }
                if(Predicates::isNotEmpty($orderInfo)) {
                    if(!Order::checkOrderProduct($orderId, $orderInfo)) {
                        error_log("commit order info check log first");
                        Log::commitLogger()->error('check_order_info_error:', ["order_info" => json_decode($orderInfo, true)]);
                        error_log("commit order info check log last");
                        if (HI\Config\VERIFY_WHEN_COMMIT) {
                            Protocol::badRequest(Notice::get()->invalidCommit());
                        }
                    }
                    if(DataBase::doTableInsert(HI\TableName\ORDER_PICTURE, ["order_id" => $orderId, "payload" => $orderInfo])) {
                        $orderStatus = Accessor::either(Order::getOrderStatusId("上传照片完成"), 5);
                        Order::$updateStatus = DataBase::doTableUpdate(HI\TableName\ORDER, ["order_status_id" => $orderStatus], "order_id = :o_id and order_number = :o_num", [":o_id" => $orderId, ":o_num" => $orderNumber]);
                        Order::insertOrderLog($orderId, ['status' => $orderStatus, 'status_desc' => "上传照片完成"], $loginUid);
                        Cost::statistics($orderId);//消费统计
                        DataBase::doTableUpdate(self::$tablePictureVerify, ['status' => 1], ['in', 'path', $this->returnPictureRandName($orderInfo)]);
                    }
                }else {
                    $orderStatus = Accessor::either(Order::getOrderStatusId("上传异常"), 3);
                    DataBase::doTableUpdate(HI\TableName\ORDER, ["order_status_id" => $orderStatus], "order_id = :o_id and order_number = :o_num", [":o_id" => $orderId, ":o_num" => $orderNumber]);
                }
                $result['order_id'] = $orderId;
                $result['share'] = Order::orderShareInfo(1, $orderId);
                Protocol::ok($result);
            }else {
                $notice = "the order status is not normal";
                Protocol::forbidden(new stdClass(), $notice);
            }            
        });
        if(Order::$updateStatus) {
            AsyncTask::submit(HI\Config\Queue\ORDER_POST_PROCESS, $data->requiredInt('order_id'));
        }
    }
    public function productAction() {
        $url = explode('/', $_SERVER["REQUEST_URI"]);
        if($url[1] == "s") {
            $productId = $url[2];
            $type = "share";
            self::doRedirect($productId, $type);
        }
    }
    
    private static function returnPictureRandName($orderInfo) {
        $orderInfo = json_decode($orderInfo, true);
        $path = [];
        array_walk($orderInfo, function (&$value, $key) use (&$path) {
            $picturePath = [];
            array_walk($value['photos'], function (&$subValue, $subKey) use (&$picturePath) {
                $picturePath[] = explode('.', explode('/', $subValue['path'])[2])[0];
            });
            $path = array_merge($path, $picturePath);
        });
        return $path;
    }
    
    private function doRedirect($productId, $type)
    {
        Header("Location: " . HI\APP_URL . "share/share.html?product_id=" . $productId . "&target=" . $type);
    }
    
    public function testAction() {
        exit;
        $e = new Express();
        echo $e -> getInfo('SF', 304642675896);
        echo 1234;
    }
}
?>
