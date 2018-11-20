<?php
use yii\db\Query;

class PushUserShareProcessCommand
{
    const SELECT_LIMIT = 200;
    
    public static function main()
    {
        $connection = Yii::$app->db;
        $sqlFormat = "select customer_id from ".HI\TableName\CUSTOMER." where customer_id > %d and approved = 1  and status = 0 group by customer_id order by customer_id asc limit ".self::SELECT_LIMIT;
        $uid = 0;
        $approved = 0;
        while (true) {
            $sqlUid = sprintf($sqlFormat, $uid);
            /* uid */
            $uids = $connection->createCommand($sqlUid)->queryAll();
            if (!$uids) {
                break;
            }
            $pushInfo = ['type' => 1, 'url' => HI\Config\Coupon\REDIRECT_URI . 'order/freecoupon.html', 'title' => 'happyin'];
            foreach ($uids as $value) {
              if (!UserModel::queryThisMonthShare($value['customer_id'])) {
                  if (date('d') == 1) {
                      $message = '亲爱的终身免费冲印用户，请点击领取您本月的免费冲印券';
                  } else {
                      $message = '都月底了，您的免费冲印券还没领呢';
                  }
                  echo '--'.$value['customer_id'].'--';
                  MiPush::submitWorks(['type' => MiPush::TO_COUPON, 'uid' => $value['customer_id'], 'message' => $message, 'payload' => $pushInfo]);
              }
              /* 最新的uid */
              $uid = $value['customer_id'];
            }
            echo "Success \n";
        }
    }
}