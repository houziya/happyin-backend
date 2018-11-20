<?php
use yii\db\Query;
class TestCouponListCommand
{
    const TEST_COUPON = 'coupon test';

    public static function main()
    {
        $mail = new PHPMailer(); //建立邮件发送类
        $mail->CharSet = "UTF-8";
        $address ="lzz2013520@163.com";
        $mail->IsSMTP(); // 使用SMTP方式发送
        $mail->Host = "smtp.163.com"; // 您的企业邮局域名
        $mail->SMTPAuth = true; // 启用SMTP验证功能
        $mail->Username = "lzz2013520@163.com"; // 邮局用户名(请填写完整的email地址)
        $mail->Password = "Melo1051103355"; // 邮局密码
        $mail->Port = 25;
        $mail->From = "lzz2013520@163.com"; //邮件发送者email地址
        $mail->FromName = "reallyli";
        $mail->AddAddress("$address", "lizhuangzhuang");//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
        //$mail->AddReplyTo("", "");
        
//         $mail->AddAttachment(APP_PATH . "/runtime/cache/order/1464418124.xlsx"); // 添加附件
        $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
        
        $mail->Subject = self::TEST_COUPON; //邮件标题
        $mail->Body = empty(self::queryExceptionCoupon()) ? '今天暂时没有数据' : self::queryExceptionCoupon(); //邮件内容
        
        if (!$mail->Send()) {
            echo "邮件发送失败 \n\t";
            echo "错误原因: " . $mail->ErrorInfo . "\n";
            exit;
        }
        echo "complete \n";
    }

    public static function queryExceptionCoupon()
    {
        $date = date('Y-m-d');
        $select = "c.date_added, cu.customer_id, cu.lastname, c.name, c.channel, c.code, c.nums, c.logged, a.firstname, a.phone, a.address_1, a.city, a.address_2";
        $query = (new Query())->select($select)
            ->from('coupon as c')
            ->leftJoin('customer_coupon as cc', 'c.coupon_id = cc.coupon_id')
            ->leftJoin('customer as cu', 'cc.customer_id = cu.customer_id')
            ->leftJoin('address as a','cu.customer_id = a.customer_id')
            ->where("c.date_added > '{$date}'")
            ->orderBy('c.date_added desc')
            ->all();
        $html = '';
        if ($query) {
            $html .= "<table border='1' bordercolor='#333333' cellspacing='0' cellpadding='0'><tr><th>添加时间</th><th>用户ID</th><th>用户昵称</th><th>地址信息</th><th>名称</th><th>渠道</th><th>code</th><th>数量</th></tr>";
            foreach($query as $value) {
                $html .= "<tr><td>" . $value['date_added'] . "</td><td>" . $value['customer_id'] . "</td><td>" . $value['lastname'] . "</td><td>".$value['firstname'] . "/" . $value['phone'] . "/" . $value['address_1'] . "-" . $value['city'] . "-" .$value['address_2'] . "</td><td>" . $value['name'] . "</td><td>" . $value['channel'] . "</td><td>" . $value['code'] . "</td><td>" . $value['nums'] . "</td></tr>";
            }
            $html .= "</table>";
        }
        return $html;
    }
}
?>
