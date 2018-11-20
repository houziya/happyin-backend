<?php
use yii\db\Query;

class CouponValidityProcessCommand
{
    public static function main()
    {
        $connection = Yii::$app->db;
        $sqlFormat = "select customer_id from customer_coupon where customer_id > %d and type = 0 group by customer_id order by customer_id asc limit 200";
        $uid = 0;
        $num = 0;
        while (true) {
            $sqlUid = sprintf($sqlFormat, $uid);
            $uids = $connection->createCommand($sqlUid)->queryAll();
            if (!$uids) {
                break;
            }
            $uidStr = '';
            foreach ($uids as $uid) {
                $uidArr[] = $uid['customer_id'];
                $uid = $uid['customer_id'];
            }
            $uidStr = implode(',', $uidArr);
            $sqlCoupon = "select cc.customer_id, cc.type, c.name, cc.date_added as da, c.use_type, c.validity as v, c.use_start, c.use_end from ".HI\TableName\CUSTOMER_COUPON." as cc 
                          left join ".HI\TableName\COUPON." as c on cc.coupon_id = c.coupon_id 
                          where cc.customer_id in ( ".$uidStr." )";
            $coupons = $connection->createCommand($sqlCoupon)->queryAll();
            if (!$coupons) {
                continue;
            }
            $pushInfo = [];
            foreach ($coupons as $coupon) {
                $date = Coupon::classificationUsedType($coupon['use_type'], $coupon['v'], $coupon['use_start'], $coupon['use_end'], $coupon['da']);
                $end = date('Y-m-d', strtotime($date['end_date']));
                if ($end == date("Y-m-d", strtotime("+1 day")) || $end == date("Y-m-d")) {
                    $pushInfo[$coupon['customer_id']][]= $coupon['name'];
                }
            }
            foreach ($pushInfo as $pushUid => $push) {
                echo '--'.$pushUid.'--';
                $count = count($push);
                if ($count == 1) {
                    $message = '您有一张优惠券明天就要过期了,不要浪费哦';
                }
                if ($count > 1) {
                    $message = '您有'.$count.'张优惠劵明天就要过期了,不要浪费哦';
                }
                MiPush::submitWorks([
                        'type' => MiPush::TO_COUPON, 
                        'uid' => $pushUid,
                        'message' => $message,
                        'payload' => ['type' => 3],
                    ]);
            }
        }
        echo "Success \n".count($pushInfo);
    }
}