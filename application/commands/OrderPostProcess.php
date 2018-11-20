<?php
use yii\db\Query;

class OrderPostProcessCommand
{
    private static $productCache = [];
    private static $TMP_ROOT = APP_PATH . "/runtime/tmp";
    private static $DUMMY_IMAGE = APP_PATH . "/resources/white1x1.png";
    private static function parseRect($input) {
        return explode(",", str_replace(" ", "", str_replace("}", "", str_replace("{", "", $input))));
    }

    private static function uuid()
    {
        return sha1(uuid_create());
    }

    private static function evaluate($template, $vars) {
        foreach ($vars as $key => $value) {
            $template = preg_replace(sprintf('/\$\{?%s\}?/', $key), $value, $template);
        }
        return $template;
    }

    private static function getFileName($productId, $vars)
    {
        $template = @Hi\Config\Product\FILE_NAME_TEMPLATE[$productId];
        if (Predicates::isNotEmpty($template)) {
            return self::evaluate($template, $vars);
        } else {
            return $template;
        }
    }

    private static function processFrameset($order, $orderNumber, $index, &$outputs, $productId, $model, $count, $orientation, $photos)
    {
        $realPhotos = array_map(function($photo) { return "/" . $photo->path; }, $photos);
        $numPhotos = count($realPhotos);
        $files = ContentCache::loadAll($realPhotos);
        var_dump([count($files) == $numPhotos,  !in_array("", $files),  !in_array(NULL, $files)]);
        Preconditions::checkArgument(count($files) == $numPhotos && !in_array("", $files) && !in_array(NULL, $files));
        $frameset = forward_static_call_array("Frameset::builder", array_merge([$orientation], HI\Config\Product\FRAMESET_SETTING[$productId]));
        for ($idx = 0; $idx < $numPhotos; ++$idx) {
            $rect = self::parseRect($photos[$idx]->rect);
            $frameset->add($files[$idx], $rect);
        }
        $tmpPath = NULL;
        Execution::autoUnlink(function($unlink) use (&$tmpPath, $frameset) {
            $tmpPath = createTempFile();
            try {
                $frameset->build($tmpPath, HI\Config\Product\POST_PROCESS_QUALITY);
            } catch (Exception $ex) {
                error_log($ex->getTraceAsString());
                unlink($tmpPath);
                throw $ex;
            }
        });
        for ($instance = 0; $instance < $count; ++$instance) {
            $path = self::getFileName($productId, ["uuid" => self::uuid(), "orderNumber" => $orderNumber, "date" => date("Ymd")]);
            $outputs[$path] = $tmpPath;
        }
        echo "finished building product $model $index for order $order\n";
    }

    private static function processMemoryBox($order, $orderNumber, $index, &$outputs, $productId, $model, $count, $orientation, $photos)
    {
        $realPhotos = array_map(function($photo) { return "/" . $photo->path; }, $photos);
        $files = ContentCache::loadAll($realPhotos);
        for ($idx = 0, $numPhotos = count($realPhotos); $idx < $numPhotos; ++$idx) {
            $photo = $photos[$idx];
            if (property_exists($photo, "id") && strtolower($photo->id) == "mask") {
                $maskFile = $files[$idx];
                $maskRect = self::parseRect($photo->rect);
            } else {
                $backgroundFile = $files[$idx];
                $backgroundRect = self::parseRect($photo->rect);
            }
        }
        Preconditions::checkArgument(Predicates::isNotNull($maskFile) && Predicates::isNotNull($backgroundFile));
        $tmpPath = Execution::autoUnlink(function($unlink) use ($maskRect, $maskFile, $backgroundRect, $backgroundFile, $orientation) {
            $clipped = createTempFile();
            unlink($clipped);
            Photographer::clip($backgroundFile, $backgroundRect, 0.0, $clipped);
            $background = Photographer::loadImage($clipped);
            if ($orientation > 0) {
                $background = imagerotate($background, $orientation * (-90), 0);
                if ($orientation == 1 || $orientation == 3) {
                    $tmp = $backgroundRect[2];
                    $backgroundRect[2] = $backgroundRect[3];
                    $backgroundRect[3] = $tmp;
                }
            }
            $mask = Photographer::loadImage($maskFile, "png");
            $width = HI\Config\Product\MemoryBox\SETTING[0];
            $height = HI\Config\Product\MemoryBox\SETTING[1];
            $output = imagecreatetruecolor($width, $height);
            imagefill($output, 0, 0, imagecolorallocate($output, 255, 255, 255));
            $backgroundHeight = intval(($width * $backgroundRect[3]) / $backgroundRect[2]);
            $realHeight = abs($maskRect[3] * $width / $maskRect[2]);
            imagecopyresampled($output, $background, 0, $realHeight - 190 - $backgroundHeight, 0, 0, $width, $backgroundHeight, $backgroundRect[2], $backgroundRect[3]);
            if ($width == $maskRect[2]) {
                imagecopy($output, $mask, 0, 0, 0, 0, $maskRect[2], $maskRect[3]);
            } else {
                imagecopyresampled($output, $mask, 0, 0, 0, 0, $width, $realHeight, $maskRect[2], $maskRect[3]);
                imagefill($output, 0, $realHeight, imagecolorallocate($output, 255, 255, 255));
            }
            $tmpPath = createTempFile();
            Photographer::saveImage($output, $tmpPath, HI\Config\Product\POST_PROCESS_QUALITY);
            return $tmpPath;
        });
        for ($instance = 0; $instance < $count; ++$instance) {
            $outputs[self::getFileName($productId, ["uuid" => self::uuid(), "orderNumber" => $orderNumber, "date" => date("Ymd")])] = $tmpPath;
        }
        echo "finished building memory box product $index for order $order\n";
    }

    private static function orderInfo($orderId)
    {
        return Yii::$app->db->createCommand("select shipping_firstname, shipping_lastname, payment_firstname, payment_lastname, customer_id from " . 
            HI\TableName\ORDER. " where order_id = :orderId")->bindValue(":orderId", $orderId)->queryOne();
    }

    private static function shippingName($orderInfo)
    {
        unset($orderInfo["customer_id"]);
        return array_reduce($orderInfo, function($carry, $name) {
            if (Predicates::isEmpty($carry) && Predicates::isNotEmpty($name)) {
                $carry = $name;
            }
            return $carry;
        });
    }

    private static function numberOfPhotos($orderInfo, $orderId)
    {
        return array_reduce(Yii::$app->db->createCommand("select payload from " . HI\TableName\ORDER_PICTURE . " where order_id in (select order_id from " .
            HI\TableName\ORDER . " where customer_id = :customerId) and id <= (select id from " . HI\TableName\ORDER_PICTURE . " where order_id = :orderId) " . 
            "order by id asc")->bindValue(":orderId", $orderId)->bindValue(":customerId", $orderInfo["customer_id"])->queryAll(), function($carry, $picture) {
                if (!empty(json_decode($picture["payload"], true))){
                $carry += array_reduce(json_decode($picture["payload"]), function($count, $product) {
                    if (array_key_exists($product->pid, HI\Config\Product\PRINT_SETTING)) {
                        $count += array_reduce($product->photos, function($copies, $photo) {
                            $copies += $photo->count;
                            return $copies;
                        }, 0);
                    }
                    return $count;
                }, 0);
                return $carry;
            }}, 0);
    }

    private static function processPrint($order, $orderNumber, $index, &$outputs, $border, $productId, $model, $count, $photos, &$totalPrintings, &$shippingName)
    {
        $realPhotos = array_map(function($photo) { return "/" . $photo->path; }, $photos);
        $numPhotos = count($realPhotos);
        $files = ContentCache::loadAll($realPhotos);
        Preconditions::checkArgument(count($files) == $numPhotos && !in_array("", $files) && !in_array(NULL, $files));
        $uuid = self::uuid();
        $realIndex = 1;
        for ($instance = 0; $instance < $count; ++$instance) {
            for ($idx = 0; $idx < $numPhotos; ++$idx) {
                $rect = self::parseRect($photos[$idx]->rect);
                $tmpPath = createTempFile();
                Photographer::clip($files[$idx], $rect, $border, $tmpPath);
                if ($productId == HI\Config\Product\LOMO_CARDS_PRODUCT_ID || $productId == HI\Config\Product\PHOTO_CARDS_PRODUCT_ID) {
                    $setting = ($productId == HI\Config\Product\LOMO_CARDS_PRODUCT_ID) ? HI\Config\Product\LOMO_CARDS_SETTING : HI\Config\Product\PHOTO_CARDS_SETTING;
                    $size = getimagesize($tmpPath);
                    $flip = ($size[0] > $size[1]);
                    Photographer::resize($tmpPath, "jpeg", $setting[0], $setting[1], HI\Config\Product\POST_PROCESS_QUALITY, $setting[2], $flip);
                    for ($tmp = 0; $tmp < $photos[$idx]->count; ++$tmp) {
                        $outputs[self::getFileName($productId, ["uuid" => $uuid, "orderNumber" => $orderNumber, "date" => date("Ymd"), "index" => $realIndex])] = $tmpPath;
                        $realIndex += 1;
                    }
                } else {
                    $outputs[self::getFileName($productId, ["uuid" => $uuid, "orderNumber" => $orderNumber, "date" => date("Ymd"), "count" => $count,
                        "photoCount" => $photos[$idx]->count, "index" => $idx + 1, "pageIndex" => intval($idx / 2) + 1, "instance" => $index,
                        "totalPhotoCount" => $numPhotos, "pageOffset" => ($idx & 1) + 1])] = $tmpPath;
                }
            }
        }
        if ($productId == HI\Config\Product\LOMO_CARDS_PRODUCT_ID || $productId == HI\Config\Product\PHOTO_CARDS_PRODUCT_ID) {
            $numPhotos = $realIndex - 1;
        }
        if (array_key_exists($productId, HI\Config\Product\THANKS_CARD_SETTING)) {
            $setting = HI\Config\Product\THANKS_CARD_SETTING[$productId];
            if (Predicates::isNull($totalPrintings)) {
                $orderInfo = self::orderInfo($order);
                $totalPrintings = self::numberOfPhotos($orderInfo, $order);
                $shippingName = self::shippingName($orderInfo);
                if (mb_strlen($shippingName) > 4) {
                    $shippingName = mb_substr($shippingName, 0, 4);
                }
            }
            $nameFont = APP_PATH . "/resources/Catoon.ttf";
            $numberFont = APP_PATH . "/resources/FounderBold.ttf";
            $color = [232, 160, 133];
            $nameSetting = $setting[1][mb_strlen($shippingName) - 1];
            $numberLength = mb_strlen($totalPrintings);
            $numberSetting = $setting[2][$numberLength - 1];
            $image = Card::generate(APP_PATH . "/resources/" . $setting[0] . "-" . $numberLength . ".png", [
                Card::createDrawable($color, $nameFont, $shippingName, $nameSetting["x"], $nameSetting["y"], 
                                     $nameSetting["width"], $nameSetting["height"], $nameSetting["fontSize"]), 
                Card::createDrawable($color, $numberFont, $totalPrintings, $numberSetting["x"], $numberSetting["y"], 
                                     $numberSetting["width"], $numberSetting["height"], $numberSetting["fontSize"])], "png");
            $tmpPath = createTempFile();
            Photographer::saveImage($image, $tmpPath, HI\Config\Product\POST_PROCESS_QUALITY);
            if ($productId == HI\Config\Product\LOMO_CARDS_PRODUCT_ID || $productId == HI\Config\Product\PHOTO_CARDS_PRODUCT_ID) {
                $setting = ($productId == HI\Config\Product\LOMO_CARDS_PRODUCT_ID) ? HI\Config\Product\LOMO_CARDS_SETTING : HI\Config\Product\PHOTO_CARDS_SETTING;
                $size = getimagesize($tmpPath);
                Photographer::resize($tmpPath, "jpeg", $setting[0], $setting[1], HI\Config\Product\POST_PROCESS_QUALITY, $setting[2], false);
            }
            $outputs[self::getFileName($productId, ["uuid" => $uuid, "orderNumber" => $orderNumber, "date" => date("Ymd"), "count" => $count, "instance" => "感谢卡",
                "photoCount" => 1, "index" => "感谢卡", "pageIndex" => intval($numPhotos / 2) + 1, "pageOffset" => ($numPhotos & 1) + 1])] = $tmpPath;
        }
        echo "finished building print product $index for order $order\n";
    }

    private static function processPictureAlbum($order, $orderNumber, $index, &$outputs, $productId, $model, $count, $photos)
    {
        $realPhotos = array_map(function($photo) { return "/" . $photo->path; }, $photos);
        $numPhotos = count($realPhotos);
        $files = ContentCache::loadAll($realPhotos);
        var_dump([count($files) == $numPhotos,  !in_array("", $files),  !in_array(NULL, $files)]);
        Preconditions::checkArgument(count($files) == $numPhotos && !in_array("", $files) && !in_array(NULL, $files));
        $framesets = [
            forward_static_call_array("Frameset::builder", array_merge([0], HI\Config\Product\PICTURE_ALBUM_SETTING)),
            forward_static_call_array("Frameset::builder", array_merge([0], HI\Config\Product\PICTURE_ALBUM_SETTING))
        ];
        for ($idx = 0; $idx < 4; ++$idx) {
            $rect = self::parseRect($photos[$idx]->rect);
            $framesets[0]->add($files[$idx], $rect);
        }
        $framesets[1]->add(self::$DUMMY_IMAGE, [0, 0, 1, 1]);
        for ($idx = 4; $idx < 6; ++$idx) {
            $rect = self::parseRect($photos[$idx]->rect);
            $framesets[1]->add($files[$idx], $rect);
        }
        $framesets[1]->add(self::$DUMMY_IMAGE, [0, 0, 1, 1]);
        $tmpPath = Execution::autoUnlink(function($unlink) use (&$tmpPath, $framesets) {
            $tmpPath = [createTempFile(), createTempFile()];
            try {
                $framesets[0]->build($tmpPath[0], HI\Config\Product\POST_PROCESS_QUALITY);
                $framesets[1]->build($tmpPath[1], HI\Config\Product\POST_PROCESS_QUALITY);
                return $tmpPath;
            } catch (Exception $ex) {
                error_log($ex->getTraceAsString());
                unlink($tmpPath[0]);
                unlink($tmpPath[1]);
                throw $ex;
            }
        });
        $uuid = self::uuid();
        $outputs[self::getFileName($productId, ["uuid" => $uuid, "orderNumber" => $orderNumber, "date" => date("Ymd"), "count" => $count, "pageIndex" => 1, "side" => "正面"])] = $tmpPath[0];
        $outputs[self::getFileName($productId, ["uuid" => $uuid, "orderNumber" => $orderNumber, "date" => date("Ymd"), "count" => $count, "pageIndex" => 2, "side" => "反面"])] = $tmpPath[1];

        echo "finished building album $index for order $order\n";
        return;
    }

    private static function processProduct($order, $orderNumber, $index, &$outputs, $productId, $model, $count, $image)
    {
        echo "finished building product $model $index for order $order\n";
        return;
    }

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

    private static function getOrderSerialNumber($code, $orderChildId)
    {
        $table = $code == 0 ? HI\TableName\ORDER_NUMBERING_SD : HI\TableName\ORDER_NUMBERING_ZJ;
        return Yii::$app->db->createCommand("SELECT order_numbering_id FROM " . $table . " WHERE order_child_id = " . $orderChildId)->queryOne()['order_numbering_id'];
    }

    private static function populateExcelRow($excel, $rowIndex, $order, $productId, $products, $total)
    {
        $sheet = $excel->getActiveSheet(0);
        $sheet->setCellValue("A${rowIndex}", $order['order_number'] . "_" . $productId);
        $sheet->setCellValue("B${rowIndex}", $order['customer']);
        $sheet->setCellValue("C${rowIndex}", 'HappyIn');
        $sheet->setCellValue("D${rowIndex}", $order['date_added']);
        $sheet->setCellValue("E${rowIndex}", '已支付');
        $sheet->setCellValue("F${rowIndex}", json_encode(["products" => $products], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $sheet->setCellValue("G${rowIndex}", self::getOrderSerialNumber($order['code'], $order['order_child_id']));
        $sheet->setCellValue("N${rowIndex}", $order['shipping_country']);
        $sheet->setCellValue("O${rowIndex}", $order['shipping_city']);
        $sheet->setCellValue("P${rowIndex}", $order['shipping_zone']);
        $sheet->setCellValue("Q${rowIndex}", $order['shipping_address_1']);
        $sheet->setCellValue("R${rowIndex}", '');
        $sheet->setCellValue("S${rowIndex}", $order['shipping_firstname']);
        $sheet->setCellValue("T${rowIndex}", $order['telephone']);
        $sheet->setCellValue("U${rowIndex}", '');
        $sheet->setCellValue("V${rowIndex}", '');
        $sheet->setCellValue("W${rowIndex}", '');
        $sheet->setCellValue("X${rowIndex}", '');
        $sheet->setCellValue("Y${rowIndex}", '');
        $sheet->setCellValue("Z${rowIndex}", '');
        $sheet->setCellValue("K${rowIndex}", $total);
        $sheet->setCellValue("L${rowIndex}", $total);
        $sheet->setCellValue("M${rowIndex}", 0);
        $sheet->getStyle("A${rowIndex}:Q${rowIndex}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A${rowIndex}:Q${rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $sheet->getRowDimension($rowIndex)->setRowHeight(20);
    }

    private static function createExcel($orderNumber, $child_id)
    {
        // Create new PHPExcel object
        $excel = new PHPExcel();
        // Set properties
        $excel->getProperties()->setCreator("ctos")
        ->setLastModifiedBy("happyin")
        ->setTitle($orderNumber)
        ->setSubject($orderNumber)
        ->setDescription($orderNumber)
        ->setKeywords($orderNumber)
        ->setCategory($orderNumber);
        
        $columns = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        foreach ($columns as $c) {
            $excel->getActiveSheet()->getColumnDimension($c)->setWidth(20);
        }
        $sheet = $excel->setActiveSheetIndex(0);
        // 设置行高度
        $sheet->getRowDimension('1')->setRowHeight(32);
        $sheet->getRowDimension('2')->setRowHeight(25);
        // 字体和样式
        $sheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Z1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:Z1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        // 表头
        foreach (['订单编号', '购买者账号唯一', '卖家名称', '创建时间', '订单状态', '产品信息', '预留', '预留', '预留', '预留',
            '应付金额(产品金额+运费)', '实付金额(产品金额+运费)', '应付邮费', '省', '市', '区', '地址', '邮编', '姓名', 
            '电话', '物流单号', '物流公司', '发票信息', '订单备注', '优惠券码', '优惠券金额'] as $index => $value) {
            $sheet->setCellValue($columns[$index] . "1", $value);
        }
        // 内容
        $order_id = null;
        $orders = Yii::$app->db->createCommand("SELECT s.product_ids, s.splitting_company, s.shipping_id, s.code, o.*, op.*, c.firstname as customer
                from ".HI\TableName\ORDER." o left join ".HI\TableName\ORDER_SPLITTING." s on s.order_id = o.order_id
                left join ".HI\TableName\CUSTOMER." c on c.customer_id = o.customer_id
                right join ".HI\TableName\ORDER_PRODUCT." op on op.order_id = o.order_id where s.order_child_id = $child_id
                ")->queryAll();
        $lastProducts = [];
        $lastTotal = 0;
        $currentOrder = null;
        $rowIndex = 2;
        for ($i = 0, $len = count($orders); $i < $len; $i++) {
            $currentOrder = $orders[$i];
            $productId = @$currentOrder['product_id'];
            if (!in_array($productId, json_decode($currentOrder['product_ids'], true))) {
                continue;
            }
            $quantity = $currentOrder['quantity'];
            if (array_key_exists($productId, HI\Config\Product\THANKS_CARD_SETTING) && $productId != HI\Config\Product\PHOTO_CARDS_PRODUCT_ID && $productId != HI\Config\Product\LOMO_CARDS_PRODUCT_ID) {
                $quantity += 1;
            }
            $lastProducts[] = [
                "goodsname" => self::getModel($productId),
                "price" => $currentOrder['price'],
                "sku" => self::getSKU($productId),
                "nums" => $quantity,
                "sku_id" => self::getSKUId($productId)
            ];
            $lastTotal += $quantity * $currentOrder['price'];
            $order_id = $currentOrder['order_id'];
            self::populateExcelRow($excel, $rowIndex, $currentOrder, $currentOrder['product_id'], $lastProducts, $lastTotal);
            $lastTotal = 0;
            $lastProducts = [];
            ++$rowIndex;
        }
        $objWriter = new PHPExcel_Writer_Excel2007($excel);
        $path = APP_PATH . "/runtime/cache/order/".$child_id.".xlsx";
        $objWriter->save($path);
        return $path;
    }

    public static function main() {
        ini_set('memory_limit', '1024M');
        try {
            mkdir(APP_PATH . "/runtime/cache/order", 0755, true);
        } catch (Exception $ex) {
        }
        AsyncTask::consume(HI\Config\Queue\ORDER_POST_PROCESS, function($task) {
            $error = null;
            $orderId = $task->payload;
            self::clearProduct();
            $sleepTime = 1;
            for ($retry = 0; $retry < HI\Config\Queue\ORDER_POST_PROCESS_RETRY; ++$retry) {
                try {
                   (new Query())->select('version()')->all();
                } catch (Exception $e) {
                    Yii::$app->db->close();
                    Yii::$app->db->open();
                    error_log($e->getTraceAsString());
                }
                try {
                    if (!Predicates::isEmpty($orderNumber = Yii::$app->db->createCommand("SELECT order_number FROM " . HI\TableName\ORDER . " WHERE order_id = " . $orderId)->queryAll()) &&
                        !Predicates::isEmpty($payload = Yii::$app->db->createCommand("SELECT payload FROM " . HI\TableName\ORDER_PICTURE . " WHERE order_id = " . $orderId)->queryAll())) {
                        echo "Processing order " . $orderId . "\n";
                        $orderNumber = $orderNumber[0]["order_number"];
                        $index = 0;
                        $parcelManufactureMapping = [];
                        $mapping = array_reduce(Yii::$app->db->createCommand("SELECT order_child_id, code, product_ids FROM " .
                            HI\TableName\ORDER_SPLITTING . " where order_id = " . $orderId)->queryAll(), function($carry, $item) use (&$parcelManufactureMapping) {
                                $products = json_decode($item["product_ids"]);
                                $id = $item["order_child_id"];
                                $parcelManufactorMapping[$id] = $item["code"];
                                if (Predicates::isNull(@$carry["default"])) {
                                    $carry["default"] = $id;
                                }
                                if (Predicates::isNull($products)) {
                                    $carry["default"] = $id;
                                    return $carry;
                                } else {
                                    return array_reduce($products, function($carry, $product) use ($id) {
                                        $carry[$product] = $id;
                                        return $carry;
                                    }, $carry);
                                }
                            }, []);
                        $parcels = array_reduce(array_unique(array_values($mapping)), function($carry, $parcelId) {
                            $carry[$parcelId] = [];
                            return $carry;
                        }, []);
                        $totalPrintings = null;
                        $shippingName = null;
                        $productToOrderNumber = [];
                        $parcels = array_reduce(json_decode($payload[0]["payload"]), function($carry, $product) use (&$index, $orderId, $orderNumber, $mapping, $parcelManufactureMapping, &$productToOrderNumber) {
                            $productId = $product->pid;
                            $count = $product->count;
                            $photos = $product->photos;
                            $model = self::getModel($productId);
                            $parcelId = array_key_exists($productId, $mapping) ? $mapping[$productId] : $mapping["default"];
                            $parcel = &$carry[$parcelId];
                            if ($productId == HI\Config\Product\MEMORY_BOX_PRODUCT_ID) {
                                self::processMemoryBox($orderId, "${orderNumber}_${productId}", $index, $parcel, $productId, $model, $count, $product->orientation, $photos);
                            } else if ($productId == HI\Config\Product\PICTURE_ALBUM) {
                                self::processPictureAlbum($orderId, "${orderNumber}_${productId}", $index, $parcel, $productId, $model, $count, $photos);
                            } else if (array_key_exists($productId, HI\Config\Product\PRINT_SETTING)) {
                                self::processPrint($orderId, "${orderNumber}_${productId}", $index, $parcel, HI\Config\Product\PRINT_SETTING[$productId], $productId, 
                                    $model, $count, $photos, $totalPrintings, $shippingName);
                            } else if (array_key_exists($productId, HI\Config\Product\FRAMESET_SETTING)) {
                                self::processFrameset($orderId, "${orderNumber}_${productId}", $index, $parcel, $productId, $model, $count, Accessor::either(@$product->orientation, 0), $photos);
                            } else {
                                //self::processProduct($orderId, $orderNumber, $index, $parcel, $productId, $model, $count, self::getImage($productId));
                            }
                            $index++;
                            return $carry;
                        }, $parcels);
                        foreach($parcels as $parcelId => $parcel) {
                          Execution::autoUnlink(function($unlink) use ($parcel, $parcelId, $orderId, $orderNumber) {
                              $tmpFile = createTempFile();
                              $unlink($tmpFile);
                              foreach($parcel as $k => $v) {
                                  $unlink($v);
                              }
                              $excelPath = self::createExcel($orderNumber, $parcelId);
                              $parcel["$orderNumber.xlsx"] = $excelPath;
                              if (Predicates::isNotEmpty($parcel)) {
                                Zipper::zip($parcel, $tmpFile);
                              }
                              $parcelName = $orderNumber . "-" . self::uuid();
                              $targetFile = "/order/parcel/" . $parcelName . ".zip";
                              $result = CosFile::uploadTo($tmpFile, $targetFile);
                              if ($result["code"] != 0) {
                                  throw new Exception("Could not upload " . $tmpFile . " to " . $targetFile);
                              } else {
                                  Preconditions::checkArgument(Yii::$app->db->createCommand()->update(HI\TableName\ORDER_SPLITTING,
                                      ['parcle' => $parcelName], ["order_child_id" => $parcelId])->execute());
                                  $log = "Uploaded file " . $tmpFile . " to " . $targetFile;
                                  error_log($log);
                                  echo $log . "\n";
                              }
                          });
                        }
                        Execution::autoTransaction(Yii::$app->db, function() use ($orderId) {
                            if (Yii::$app->db->createCommand()->update(HI\TableName\ORDER, ['order_status_id' => Hi\Config\Order\PROCESSED_STATUS_ID], ["order_id" => $orderId])->execute()) {
                                Yii::$app->db->createCommand()->update(HI\TableName\ORDER_SPLITTING, ['order_status_id' => Hi\Config\Order\PROCESSED_STATUS_ID], ["order_id" => $orderId])->execute();
                                Order::insertOrderLog($orderId, ['status' => Hi\Config\Order\PROCESSED_STATUS_ID, 'status_desc' => "照片已合成"], 0);
                            }
                        });
                    } else {
                        $error = "Invalid order id " . $orderId;
                        error_log($error);
                        throw new Exception($error);
                    }
                    AsyncTask::submit(HI\Config\PackagePostSd, $orderId);
                    AsyncTask::submit(HI\Config\PackagePostZj, $orderId);
                    return true;
                } catch (Exception $ex) {
                    $error = $ex->getMessage() . "\n" . $ex->getTraceAsString();
                }
                if (Predicates::isNotNull($error)) {
                    error_log($error);
                    echo $error . "\n";
                }
                sleep($sleepTime);
                if ($sleepTime < 8) {
                    $sleepTime *= 2;
                }
            }
            return Execution::autoTransaction(Yii::$app->db, function() use ($orderId, $error) {
                return Yii::$app->db->createCommand()->insert(HI\TableName\FAILED_ASYNC_TASK, [
                    'subject' => HI\Config\Queue\ORDER_POST_PROCESS,
                    'payload' => $orderId,
                    'error' => $error])->execute();
            });
        }, 2, 1024);
    }
};

?>
