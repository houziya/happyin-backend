<?php
use Yaf\Dispatcher;
use Yaf\Registry;
use yii\db\Query;
use yii\db\Expression;
class Cost
{
    public static function statistics ($orderId)
    {
        $connection = Yii::$app->db;
        $sql = "select o.order_id, o.shipping_country_id, o.order_status_id, o.date_added, op.product_id, op.name, op.quantity, os.code ,os.order_child_id, cp.print, cp.produce, 
                cp.overpack, cp.inner_pack, cp.seal_sticker, cp.fitting, opi.payload
                from ".HI\TableName\ORDER." as o 
                left join ".HI\TableName\ORDER_PRODUCT." as op on op.order_id = o.order_id 
                left join ".HI\TableName\ORDER_SPLITTING." as os on os.order_child_id = op.order_child_id  
                left join ".HI\TableName\COST_PRICE." as cp on cp.product_id = op.product_id and cp.type = os.code 
                left join ".HI\TableName\ORDER_PICTURE." as opi on opi.order_id = o.order_id 
                where o.order_id = '$orderId'";
        $orderInfos = $connection->createCommand($sql)->queryAll();
        $stat_date = date("Ymd");
        //15状态 记录成本
        foreach ($orderInfos as $orderInfo) {
            $map = [HI\Config\Product\FIVE_INCH => 1, HI\Config\Product\SIX_INCH => 1, HI\Config\Product\LOMO_CARDS_PRODUCT_ID => 2, HI\Config\Product\PHOTO_CARDS_PRODUCT_ID => 2];
            $parameter = [
                'stat_date' =>$stat_date,
                'order_id' => $orderInfo['order_id'],
                'order_child_id' => $orderInfo['order_child_id'],
                'product_id' => $orderInfo['product_id'],
                'destination' => $orderInfo['shipping_country_id'],
                'name' => $orderInfo['name'],
            ];
            if ($orderInfo['code'] == 0) {
                $parameter['origin'] = 1365;//shandong
            } else {
                $parameter['origin'] = 933;//zhejiang
            }
            $number = 0;
            $quantity = $orderInfo['quantity'];
            switch (@$map[$orderInfo['product_id']]) {
                case 1:
                    //5,6寸计算
                     $number = $orderInfo['quantity'] + 1;//感谢卡
                     $quantity = ceil($orderInfo['quantity']/50);//套数
                     $parameter['cost'] = ($number * $orderInfo['print']) + ($quantity * ($orderInfo['produce'] + $orderInfo['overpack'] + $orderInfo['inner_pack'] + $orderInfo['seal_sticker'] + $orderInfo['fitting']));
                    break;
                case 2:
                    //LOMO,照片卡
                    $payload = json_decode($orderInfo['payload'], true);
                    foreach ($payload as $pictureInfo) {
                        if ($pictureInfo['pid'] == $orderInfo['product_id']) {
                            $number = count($pictureInfo['photos']) + 1;////感谢卡
                        }
                    }
                    $parameter['cost'] = $quantity * ($orderInfo['print'] + $orderInfo['produce'] + $orderInfo['overpack'] + $orderInfo['inner_pack'] + $orderInfo['seal_sticker'] + $orderInfo['fitting']);
                    break;
                default:
                    $parameter['cost'] = $quantity * ($orderInfo['print'] + $orderInfo['produce'] + $orderInfo['overpack'] + $orderInfo['inner_pack'] + $orderInfo['seal_sticker'] + $orderInfo['fitting']);
                    break;
            }
            $parameter['number'] = $number;//实际冲印数量
            $parameter['quantity'] = $quantity;//实际套数
            
            //写入成本流水
            self::insert($connection, HI\TableName\COST_LOG, $parameter);
        }
    }
    
    private static function insert($connection, $table, $parameter)
    {
        $res = $connection->createCommand()->insert($table, $parameter)->execute();
        return $connection->getLastInsertID();
    }
    
    public static function export ()
    {
        //创建一个excel
        $stat_date = date("Ymd");
        $objPHPExcel = new PHPExcel();
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition:inline;filename=$stat_date-成本核算.xls");
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        
        //设置excel的属性：
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        $objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
        $objPHPExcel->getProperties()->setCategory("Test result file");
        
        //设置当前的sheet
        $objPHPExcel->setActiveSheetIndex(0);
        //设置sheet的name
        $objPHPExcel->getActiveSheet()->setTitle('data');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . 1, '日期');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . 1, '订单号');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . 1, '发货地');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . 1, '5英寸(张)');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . 1, '5英寸(套)');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . 1, '6英寸(张)');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . 1, '6英寸(套)');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . 1, 'LOMO卡(张)');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . 1, 'LOMO卡(套)');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . 1, '照片卡(张)');
        $objPHPExcel->getActiveSheet()->setCellValue('K' . 1, '照片卡(套)');
        $objPHPExcel->getActiveSheet()->setCellValue('L' . 1, '方格1*1(黑)');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . 1, '方格1*1(白)');
        $objPHPExcel->getActiveSheet()->setCellValue('N' . 1, '方格2*2(黑)');
        $objPHPExcel->getActiveSheet()->setCellValue('O' . 1, '方格2*2(白)');
        $objPHPExcel->getActiveSheet()->setCellValue('P' . 1, '方格3*3(黑)');
        $objPHPExcel->getActiveSheet()->setCellValue('Q' . 1, '方格3*3(白)');
        $objPHPExcel->getActiveSheet()->setCellValue('R' . 1, '方格4*4(黑)');
        $objPHPExcel->getActiveSheet()->setCellValue('S' . 1, '方格4*4(白)');
        $objPHPExcel->getActiveSheet()->setCellValue('T' . 1, '大海报');
        $objPHPExcel->getActiveSheet()->setCellValue('U' . 1, '记忆盒子');
        $objPHPExcel->getActiveSheet()->setCellValue('V' . 1, '相册件数');
        $objPHPExcel->getActiveSheet()->setCellValue('W' . 1, '小画册件数');
        $objPHPExcel->getActiveSheet()->setCellValue('X' . 1, '运费');
        
        $connection = Yii::$app->db;
        $sql = "select cl.order_id, cl.order_child_id, cl.product_id, cl.quantity, cl.name, cl.number, cl.origin, s.shipping 
                from ".HI\TableName\COST_LOG." as cl 
                left join ".HI\TableName\SHIPMENT." as s on s.delivery_place = cl.origin and s.receipt = cl.destination
                where stat_date = '$stat_date'";
        $costInfos = $connection->createCommand($sql)->queryAll();
        $orderInfos = [];
        foreach ($costInfos as $costInfo) {
            $orderInfos[$costInfo['order_child_id']]['product'][$costInfo['product_id']] = $costInfo;
            $orderInfos[$costInfo['order_child_id']]['order_id'] = $costInfo['order_id'];
            $orderInfos[$costInfo['order_child_id']]['origin'] = $costInfo['origin'];
            $orderInfos[$costInfo['order_child_id']]['shipping'] = $costInfo['shipping'];
        }
        $num = 2;
        foreach ($orderInfos as $key => $orderInfo) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $num, date("Y.m.d"));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $orderInfo['order_id']);
            
            if ($orderInfo['origin'] == 1365) {
                if (!in_array($orderInfo['product']['product_id'], [HI\Config\Product\FIVE_INCH, HI\Config\Product\SIX_INCH, 81])){
                    $objPHPExcel->getActiveSheet()->setCellValue('X' . $num, 8);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('X' . $num, 6);
                }
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('X' . $num, $orderInfo['shipping']);
            }
            
            if ($orderInfo['origin'] == 933) {
                $origin = '杭州';
            } elseif($orderInfo['origin'] == 1365) {
                $origin = '山东';
            } else {
                $origin = '未知';
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $origin);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $num, 0);
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $num, 0);
            foreach ($orderInfo['product'] as $product_id => $order) {
                if ($product_id == HI\Config\Product\FIVE_INCH) {
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $num, $order['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\SIX_INCH) {
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $order['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\LOMO_CARDS_PRODUCT_ID) {
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $num, $order['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\PHOTO_CARDS_PRODUCT_ID) {
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $num, $order['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_ONE_BLACK) {//1*1黑
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_ONE_WHITE) {//1*1白
                    $objPHPExcel->getActiveSheet()->setCellValue('M' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_TWO_BLACK) {//2*2黑
                    $objPHPExcel->getActiveSheet()->setCellValue('N' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_TWO_WHITE) {//2*2白
                    $objPHPExcel->getActiveSheet()->setCellValue('O' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_THREE_BLACK) {//3*3黑
                    $objPHPExcel->getActiveSheet()->setCellValue('P' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_THREE_WHITE) {//3*3白
                    $objPHPExcel->getActiveSheet()->setCellValue('Q' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_FOUR_BLACK) {//4*4白
                    $objPHPExcel->getActiveSheet()->setCellValue('R' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\GRID_FOUR_WHITE) {//4*4白
                    $objPHPExcel->getActiveSheet()->setCellValue('S' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\BIG_POSTER) {//dahaibao
                    $objPHPExcel->getActiveSheet()->setCellValue('T' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\MEMORY_BOX_PRODUCT_ID) {//jiyihezi
                    $objPHPExcel->getActiveSheet()->setCellValue('U' . $num, $order['quantity']);
                }
                $pictureArray = [
                    HI\Config\Product\PHOTO_GALLERY_THREE,
                    HI\Config\Product\PHOTO_GALLERY_FIVE,
                    HI\Config\Product\PHOTO_GALLERY_SIX,
                ];
                if (in_array($product_id, $pictureArray)) {//相册
                    $objPHPExcel->getActiveSheet()->setCellValue('V' . $num, $order['quantity']);
                }
                if ($product_id == HI\Config\Product\PICTURE_ALBUM) {//小画册
                    $objPHPExcel->getActiveSheet()->setCellValue('W' . $num, $order['quantity']);
                }
            }
            $num = $num + 1;
        }
        $objWriter->save('php://output');
    }
}
