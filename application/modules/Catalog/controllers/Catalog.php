<?php
use Yaf\Controller_Abstract;
use yii\db\Query;
use yii\db\Exception;
use Moca\Tao\Object;

class CatalogController extends Controller_Abstract
{
    /* 从分享优惠劵库中取出个数  */
    const COUPON_NUM = 5;

    /**
     * 商品分类
     */
    public function categoryAction ()
    {
        $data = Protocol::arguments();
        if ($data->optional('version', 0) < 2 && $data->optional('platform', 0) == 1){
            Protocol::forbidden(new stdClass(), '修复图片裁切BUG请您更新版本');
        }
        $connection = Yii::$app->db;
        $myCoupon = self::myCoupon($data->optional('login_uid', 0));
        $sql = "select c.category_id, c.image, cd.description as color, cd.`name` as title1, cd.meta_title as title2, c.top as url, 
            cd.meta_description as title3, cd.meta_keyword as type, ptc.product_id, count(ptc.product_id) as total 
                from ".HI\TableName\CATEGORY." as c 
                left join ".HI\TableName\PRODUCT_TO_CATEGORY." as ptc on c.category_id = ptc.category_id 
                left join ".HI\TableName\CATEGORY_DESCRIPTION." as cd on cd.category_id = c.category_id
                left join ".HI\TableName\PRODUCT." as p on ptc.product_id = p.product_id
                where p.status = 1 and cd.language_id = 2 and cd.meta_keyword != 8 group by c.category_id order by c.sort_order asc";
        $status = 1;
        if ($data->optional('version', 0) > HI\Config\CLIENT\VERSION_DIFF) {
            $status = 0;
        }
        $freeSql = "select c.category_id, c.image, cd.description as color, cd.`name` as title1, cd.meta_title as title2,
            c.top as url, cd.meta_description as title3, cd.meta_keyword as type 
            from " . HI\TableName\CATEGORY . " as c 
            left join " . HI\TableName\CATEGORY_DESCRIPTION . " as cd on cd.category_id = c.category_id 
            where cd.language_id = 2 and cd.meta_keyword = 0 and c.status = $status";
        $freeInfo = $connection->createCommand($freeSql)->queryOne();
        $list = $connection->createCommand($sql)->queryAll();
        array_unshift($list, $freeInfo);
        //不忽略库存条件
        //where c.status = 1 and cd.language_id = 2 and (p.quantity > 0 or cd.meta_keyword = 0) group by c.category_id order by c.sort_order asc";
        $hidden = Yii::$app->redis->get(HI\SHARE\HIDE_SHARE.$data->optional('login_uid', 0));
//         $hidden = false;
        $response = [];
        $categoryInfo = [];
        foreach ($list as $key => $category) {
            if ($hidden && $category['type'] == 0) {
                continue;
            }
            $categoryInfo = [
                'category_id' => $category['category_id'],
                'image' => 'admin/images/'.$category['image'].'.jpg',
                'color' => $category['color'],
                'title1' => $category['title1'],
                'title2' => $category['title2'],
                'title3' => $category['title3'],
                'type' => $category['type'],
            ];
            $sql = "SELECT product_id FROM `product` WHERE product_id IN (SELECT product_id  FROM `product_to_category` WHERE category_id = " . $category['category_id'] . ") AND STATUS = 1";
            $total = $connection->createCommand($sql)->queryAll();
            if (count($total) == 1) {
                $categoryInfo['product_id'] = $category['product_id'];
            }
            $categoryInfo['url'] = $category['url'];
            if (in_array($category['category_id'], $myCoupon['category_id']) && $category['type'] == 1) {
                $categoryInfo['free'] = true;
            } else {
                $categoryInfo['free'] = false;
            }
            $response[] = $categoryInfo;
        }
        //$drop_down = ['color'=>'#FF9D01', 'image' => 'admin/images/'.HI\Config\DROP_DOWN[array_rand(HI\Config\DROP_DOWN, 1)].'.png'];
        Protocol::ok(['list' => $response, 'drop_down' => new StdClass], '', "success");
    }
    
    /**
     * 商品列表
     */
    public function productsAction ()
    {
        $data = Protocol::arguments();
        if ($data->optional('version', 0) < 2 && $data->optional('platform', 0) == 1){
            Protocol::forbidden(new stdClass(), '修复图片裁切BUG请您更新版本');
        }
        $connection = Yii::$app->db;
        $myCoupon = self::myCoupon($data->optional('login_uid', 0));
        $filter = "";//(stristr(Protocol::userAgent(), "iPhone")) ? "" : " and p.product_id != " . HI\Config\Product\LOMO_CARDS_PRODUCT_ID;
        if ($data->optional('version', 0) < 2) {
                $filter = " and p.product_id != " . HI\Config\Product\LOMO_CARDS_PRODUCT_ID;
        }
        $sqlProductInfo = "select p.product_id, p.image, pd.`name`, cd.meta_keyword as type
                from ".HI\TableName\PRODUCT." as p 
                inner join " . HI\TableName\PRODUCT_DESCRIPTION . " as pd on pd.product_id = p.product_id 
                inner join " .HI\TableName\PRODUCT_TO_CATEGORY . " as ptc on ptc.product_id = p.product_id 
                inner join " .HI\TableName\CATEGORY_DESCRIPTION . " as cd on ptc.category_id = cd.category_id
                where pd.language_id = 2 and cd.language_id = 2 and p.status = 1 and ptc.category_id = ". $data->requiredInt('category_id'). $filter . " and p.isbn = '' order by p.sort_order asc";
        //不忽略库存
        //where p.quantity > 0 and pd.language_id = 2 and cd.language_id = 2 and p.status = 1 and ptc.category_id = ". $data->requiredInt('category_id'). $filter . " and p.isbn = '' order by p.sort_order asc";
        $productList = $connection->createCommand($sqlProductInfo)->queryAll();
        foreach ($productList as $key => $product) {
            $productList[$key]['image'] = $product['image'] ? 'admin/images/'. $product['image'].'.jpg' : '';
            $productList[$key]['free'] = false;
            if ($product['type'] == 1 && in_array($product['product_id'], $myCoupon['product_id'])) {
                $productList[$key]['free'] = true;
            }
        }
        Protocol::ok(['list' => $productList],'',"success");
    }
    
    public function detailAction ()
    {
        $data = Protocol::arguments();
        if ($data->optional('version', 0) < 2 && $data->optional('platform', 0) == 1){
            Protocol::forbidden(new stdClass(), '修复图片裁切BUG请您更新版本');
        }
        /* 商品状态(用于特殊下架商品优惠券跳转参数) */
        $productStatus = CouponModel::queryOneTableInfo('status', HI\TableName\PRODUCT, ['product_id' => $data->required('product_id')]);
        if (empty($productStatus['status'])) { 
            Protocol::notFound(new StdClass, '该商品已下架');
        }
        $connection = Yii::$app->db;
        $myCoupon = self::myCoupon($data->optional('login_uid', 0));
        $sqlExtentProduct = "select product_id from " .HI\TableName\PRODUCT. " where isbn = ". $data->requiredInt('product_id');
        $productIds = $connection->createCommand($sqlExtentProduct)->queryAll();
        $productIds[]['product_id'] = $data->requiredInt('product_id');
        $products = [];
        foreach ($productIds as $productId) {
            $products[] = $productId['product_id'];
        }
        $productStr = implode(',', $products);
        $sqlProduct = "select p.product_id, p.sku as unit_str, pd.description as `desc`, pd.`name`, p.quantity, p.length, pd.meta_description as render_color, pd.meta_keyword as select_color,p.image as thumb, cd.`name` as title1, cd.meta_title as title2, cd.meta_description as title3,
                cd.description as color, p.width, p.height, p.price, p.jan, p.upc, p.ean, pr.size as size_limit, pr.quantity as quantity_limit, pr.preview, cd.meta_keyword as type
                from ".HI\TableName\PRODUCT." as p 
                inner join " . HI\TableName\PRODUCT_DESCRIPTION . " as pd on pd.product_id = p.product_id
                left join " .HI\TableName\PRINT_TO_PRODUCT . " as ptp on ptp.product_id = p.product_id
                left join " .HI\TableName\PRINT_TYPE . " as pr on pr.print_id = ptp.print_id
                inner join " .HI\TableName\PRODUCT_TO_CATEGORY . " as ptc on ptc.product_id = p.product_id
                left join " .HI\TableName\CATEGORY_DESCRIPTION . " as cd on ptc.category_id = cd.category_id
                where pd.language_id = 2 and cd.language_id = 2 and p.status = 1 and p.product_id in (".$productStr.")";
        $productInfos = $connection->createCommand($sqlProduct)->queryAll();
        $sqlProductImage = "select pi.product_id, pi.image, pi.sort_order from ". HI\TableName\PRODUCT_IMAGE ." as pi where pi.product_id in (". $productStr .") order by pi.sort_order asc";
        $productImages = $connection->createCommand($sqlProductImage)->queryAll();
        $images = [];
        foreach ($productImages as $image) {
            $images[$image['product_id']][] = $image['image'] ? 'admin/images/'. $image['image'].'.jpg' : '';
        }
        $sqlAttribute = "select product_id, text from ". HI\TableName\PRODUCT_ATTRIBUTE ." where product_id in (". $productStr .") and language_id = 2";
        $productAttributes = $connection->createCommand($sqlAttribute)->queryAll();
        $attributes = [];
        if ($productAttributes) {
            foreach ($productAttributes as $productAttribute) {
                $attribute = explode('=', $productAttribute['text']);
                switch ($attribute[0]) {
                    case 'poster_matrix':
                        $attributes[$productAttribute['product_id']][$attribute[0]] = '{'.$attribute[1].'}';
                        break;
                    case 'default_cover':
                        $attributes[$productAttribute['product_id']][$attribute[0]] = explode(',', $attribute[1]);
                        break;
                    default:
                        $attributes[$productAttribute['product_id']][$attribute[0]] = $attribute[1];
                        break;
                }
            }
        }
        $format = [];
        $child = [];
        foreach ($productInfos as $productInfo) {
            if ($productInfo['type'] == 8) {
                continue;
            }
            if ($productInfo['product_id'] != $data->required('product_id')) {
                $child[] = self::formatChildData($productInfo);
            } else {
                $format = self::formatFatherData($productInfo, $attributes, $images, $myCoupon);
            }
        }
        $format['child'] = $child;
        Protocol::ok(['list' => $format, 'share' => Order::orderShareInfo(Order::PRODUCT_SHARE, 0)],'',"success");
    }
    
    public function shareAction()
    {
        $data = Protocol::arguments();
        switch ($data->requiredInt('target')) {
            case 0 : //商品分享
                $productImg = CouponModel::queryOneTableInfo('image', HI\TableName\PRODUCT_IMAGE, ['product_id' => $data->requiredInt('product_id')]);
                $url = HI\APP_URL . "ps/" . $data->requiredInt('product_id');
                $shareInfo = $this->doGetContent($data->required('type'), $url);
                $shareInfo['image'] = 'admin/images/'.Preconditions::checkArgument($productImg['image']).'.jpg';
                $shareInfo['url'] = $url;
                if ($data->required('type') == 16) {
                    $shareInfo['wb_content'] = "我在Happyin上发现了一件商品，很符合你的气质： ".$url;
                    $shareInfo['big_image'] = $shareInfo['image'];
                }
                Protocol::ok($shareInfo);
                return;
                break;
            case 1 : //订单完成后的分享
                /* 随机code */
                $code = $this->doGetRandCode(1, $data->requiredInt('order_id'));
                $url = HI\APP_URL . "os/" . $code;
                /* 通过类型获取文案信息 */
                $shareInfo = $this->doGetOrderContent($data->required('type'), $url);
                $shareInfo['image'] = 'admin/images/logo.jpg';
                $shareInfo['url'] = $url;
                $shareInfo['user_info'] = $code;
                Protocol::ok($shareInfo);
                return;
                break;
            case 2 : //分享应用直接跳转到h5页面
                $appShare['url'] = HI\APP_URL . "as/1";
                $appShare['title'] = '对不起！让您花钱洗了这么多年照片';
                $appShare['content'] = '终身免费手机照片冲印APP';
                $appShare['image'] = 'admin/images/logo.jpg';
                if ($data->required('type') == 16) {
                    $appShare['wb_content'] = "对不起！让你花钱洗照片这么多年！终身免费手机照片冲印APP:Happyin！:" . $appShare['url'];
                    $appShare['big_image'] = 'admin/images/wb.jpg';
                }
                Protocol::ok($appShare);
                return;
                break;
            case 3 : //app内 H5分享
                $code = $this->doGetRandCode(3, $data->requiredInt('login_uid'));
                $url = HI\APP_URL . "bs/" . $code;
                $bannerShare = $this->doGetOrderContent($data->required('type'), $url);
                $bannerShare['image'] = 'admin/images/logo.jpg';
                $bannerShare['url'] = $url;
                $bannerShare['user_info'] = $code;
                Protocol::ok($bannerShare);
                return;
                break;
            default :
                Procotol::badRequest(NULL, NULL, $data->requiredInt('target').' Undefined');
        }
    }
    /* 得到随机的code */
    private function doGetRandCode($type, $param)
    {
        $randCode = substr(strrchr(uuid_create(), "-"), 1);
        switch ($type) {
            case 1 :
                if (!$share = CouponModel::queryOneTableInfo('code', CouponModel::$shareCoupon, ['secret' => $param, 'share_type' => 1])) {
                    $flag = true;
                }
                break;
            case 3 :
                if (!$share = CouponModel::queryOneTableInfo('code', CouponModel::$shareCoupon, ['secret' => $param, 'share_type' => 3])) {
                    $flag = true;
                } elseif (Predicates::isNull(UserModel::queryThisMonthShare($param))) {
                    $flag = true;
                }
                break;
            default :
        }
        if (isset($flag)) {
            /* 生成对应code 的优惠劵 sercet */
            try{
                CouponModel::doRandomlyCoupon($randCode, $type, $param);
            } catch (Exception $e) {
                $randCode = substr(strrchr(uuid_create(), "-"), 1);
                CouponModel::doRandomlyCoupon($randCode, $type, $param);
            }
            $share['code'] = $randCode;
        }
        return $share['code'];
    }

    /* 得到分享标题和 内容 */
    private function doGetContent($type, $url)
    {
        switch ($type) {
            /* WAY_TYPE_WXFRIEND */
            case 1:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
            /* SHARE_PATHWAY_TYPE_WXCIRCLE */
            case 2:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
            /* SHARE_PATHWAY_TYPE_QQFRIEND,*/
            case 4:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
            /* SHARE_PATHWAY_TYPE_QQZONE */
            case 8:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
            /* SHARE_PATHWAY_TYPE_WB */
            case 16:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
            /* default-url */
            default:
                $inviteInfo['title'] = '我在Happyin上发现了一件商品，很符合你的气质';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
        }
        return $inviteInfo;
    }

    /* 得到订单, banner位 分享标题和 内容  */
    private function doGetOrderContent($type, $url)
    {
        switch ($type) {
            /* WAY_TYPE_WXFRIEND */
            case 1 :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
                /* SHARE_PATHWAY_TYPE_WXCIRCLE */
            case 2 :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
                /* SHARE_PATHWAY_TYPE_QQFRIEND,*/
            case 4 :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
                /* SHARE_PATHWAY_TYPE_QQZONE */
            case 8 :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
                break;
                /* SHARE_PATHWAY_TYPE_WB */
            case 16 :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                $inviteInfo['wb_content'] = "对不起！让你花钱洗照片这么多年！终身免费手机照片冲印APP:Happyin！" . $url;
                $inviteInfo['big_image'] = 'admin/images/wb.jpg';
                return $inviteInfo;
                break;
                /* default-url */
            default :
                $inviteInfo['title'] = '对不起！让您花钱洗了这么多年照片';
                $inviteInfo['content'] = '终身免费手机照片冲印APP';
                return $inviteInfo;
        }
        return $inviteInfo;
    }

    /* 重定向 url到 H5页面 */
    public function redirectionAction()
    {
        $url = explode('/', $_SERVER["REQUEST_URI"]);
        if (isset($url)) {
            $code = str_replace("?", "&", $url[2]);
            CStat::returnRedisCount($url[1]);
            switch ($url[1]) {
                case 'ps' : //商品分享短地址
                    Header("Location: " . HI\APP_URL . "order/productshare.html?productId=" . $code . "&target=product");
                    return;
                case 'os' : //订单分享
                    Header("Location: " . HI\APP_URL . "order/coupon.html?ident=" . $code . "&target=order");
                    return;
                case 'as' : //应用分享
                    Header("Location: " . HI\APP_URL . "order/freecouponshare.html?target=appShare");
                    return;
                case 'bs' : //banner位分享
//                     Header("Location: " . HI\APP_URL . "order/coupon.html?ident=" . $code . "&target=bannerShare");
                    Header("Location: " . HI\APP_URL . "order/freecouponshare.html?target=bannerShare");
                    return;
                default :
                    throw new InvalidArgumentException('Invalid ident type ');
            }
        }
    }

    private function formatSize ($data, $unit)
    {
        $result = new stdClass();
        if ($unit == 'inch') {
            if ($data['length'] > 0) {
                $result->length = rtrim(rtrim($data['length'], '0'), '.');
            }
            if ($data['width'] > 0) {
                $result->width = rtrim(rtrim($data['width'], '0'), '.');
            }
            if ($data['height'] > 0) {
                $result->height = rtrim(rtrim($data['height'], '0'), '.');
            }
        } else {
            if ($data['upc'] > 0) {
                $result->length = rtrim(rtrim($data['upc'], '0'), '.');
            }
            if ($data['ean'] > 0) {
                $result->width = rtrim(rtrim($data['ean'], '0'), '.');
            }
            if ($data['jan'] > 0) {
                $result->height = rtrim(rtrim($data['jan'], '0'), '.');
            }
        }
        return $result;
       
    }
    
    private function myCoupon ($loginUid)
    {
        if (!$loginUid) {
            return ['category_id' => [], 'product_id' => []];
        }
        $coupons = CouponModel::queryCouponList($loginUid);
        $couponValid = [];
        if ($coupons) {
            foreach ($coupons as $coupon) {
                $date = Coupon::classificationUsedType($coupon['use_type'], $coupon['v'], $coupon['use_start'], $coupon['use_end'], $coupon['da']);
                $status = Coupon::doGetCouponStatus($date['end_date'], $coupon['da'], $coupon['type'], $coupon['use_type'], $coupon['use_start']);
                if (in_array($status, Coupon::$usableCoupon)){
                    $couponValid[] = $coupon['coupon_id'];
                }
            }
        }
        if ($couponValid) {
            $couponValidStr = implode(',', $couponValid);
            $connection = Yii::$app->db;
            $sqlProduct = "select ptc.category_id, ptc.product_id, p.isbn
                from ".HI\TableName\COUPON_PRODUCT." as cp
                left join ".HI\TableName\PRODUCT_TO_CATEGORY." as ptc on ptc.product_id = cp.product_id
                left join ".HI\TableName\PRODUCT." as p on p.product_id = cp.product_id
                where cp.coupon_id in( ".$couponValidStr." )";
            $categoryList = $connection->createCommand($sqlProduct)->queryAll();
            $categoryIds = [];
            $productIds = [];
            foreach ($categoryList as $data) {
                $categoryIds[] = $data['category_id'];
                $productIds[] = $data['product_id'];
                if ($data['isbn']) {
                    $productIds[] = $data['isbn'];
                }
            }
            return ['category_id' => $categoryIds, 'product_id' => $productIds];
        } else {
            return ['category_id' => [], 'product_id' => []];
        }
    }
    
    private function formatFatherData ($productInfo, $attributes, $images, $myCoupon)
    {
        $format = [
            'product_id' => $productInfo['product_id'],
            'desc' => $productInfo['desc'],
            'name' => $productInfo['name'],
            'color' => $productInfo['color'],
            'quantity' => 10000,//库存定值$productInfo['quantity'],
            'thumb' => 'admin/images/'.$productInfo['thumb'].'.jpg',
            'type' => $productInfo['type'],
            'price' => rtrim(rtrim($productInfo['price'], '0'), '.'),
            'size_limit' => '{'.$productInfo['size_limit'].'}',
            'attribute' => @$attributes[$productInfo['product_id']] ? $attributes[$productInfo['product_id']] : new stdClass(),
            'quantity_limit' => $productInfo['quantity_limit'],
            'title1' => $productInfo['title1'],
            'title2' => $productInfo['title2'],
            'title3' => $productInfo['title3'],
            'unit_str' => $productInfo['unit_str'] ? '/'.$productInfo['unit_str'] : '',
            'inch' => self::formatSize($productInfo, 'inch'),
            'cm' => self::formatSize($productInfo, 'cm'),
            'preview' => self::formatPreview($productInfo),
            'images' => $images[$productInfo['product_id']],
        ];
        $format['free'] = false;
        if ($productInfo['type'] == 1 && in_array($productInfo['product_id'], $myCoupon['product_id'])) {
            $format['free'] = true;
        }
        return $format;
    }
    
    private function formatChildData ($productInfo)
    {
        return  [
            'product_id' => $productInfo['product_id'],
            'price' => rtrim(rtrim($productInfo['price'], '0'), '.'),
            'quantity' => 1000,//$productInfo['quantity'],
            'name' => $productInfo['name'],
            'render_color' => $productInfo['render_color'],
            'select_color' => $productInfo['select_color'],
            'color' => $productInfo['color'],
            'quantity_limit' => $productInfo['quantity_limit'],
            'preview' => self::formatPreview($productInfo),
        ];
    }
    private function formatPreview ($productInfo)
    {
        $preview = new stdClass();
        if (@$productInfo['preview']) {
            $preview = json_decode($productInfo['preview']);
            $preview->preview_bg_url = @$preview->image ? 'admin/images/'.$preview->image.'.jpg' : '';
            $area = explode(',', $preview->area);
            $preview->preview_image_rect = '{{'.$area[0].','.$area[1].'},{'.$area[2].','.$area[3].'}}';
            $preview->preview_bg_size = '{'.$preview->size.'}';
            unset($preview->size);
            unset($preview->area);
            unset($preview->image);
        }
        return $preview;
    }
    
    public function testAction ()
    {
        //Cost::export();
        $origin = [ 1 => 'https://10.141.54.20:1234/sink', 2 => 'https://10.141.54.20:1234/sink'];
        $orderId = 4691;
        $sql = "select o.order_id, o.order_number, o.telephone, o.shipping_firstname, o.shipping_country, o.shipping_city, o.shipping_zone, o.shipping_address_1, o.total, o.shipping_country_id, op.name, op.price, op.total, op.quantity, o.date_added, os.code, os.shipping_id, os.parcle, os.splitting_company, os.order_child_id 
            from ".HI\TableName\ORDER." as o
            left join ".HI\TableName\ORDER_PRODUCT." as op on op.order_id = o.order_id
            left join ".HI\TableName\ORDER_SPLITTING." as os on os.order_id = o.order_id
            where o.order_id = ".$orderId;
        $orderInfo = Yii::$app->db->createCommand($sql)->queryAll();
        $sdProduct = $hzProduct = [];
        foreach ($orderInfo as $order) {
            $info = [
                'order_number' => $order['order_number'],
                'shipping_firstname' => $order['shipping_firstname'],
                'shipping_country' => $order['shipping_country'],
                'shipping_city' => $order['shipping_city'],
                'shipping_zone' => $order['shipping_zone'],
                'total' => $order['total'],
                'date_added' => $order['date_added'],
                'shipping_id' => $order['shipping_id'],
                'splitting_company' => $order['splitting_company'],
                'parcle' => "http://".HI\Config\DOWNLOAD_DOMAIN."/order/parcel/".$order['parcle'].".zip",
            ];
            
            if ($order['code'] == 0) {
                $sdProduct[] = ['name' => $order['name'], 'price' => $order['price'], 'quantity' => $order['quantity'], 'total' => $order['price'] * $order['quantity']];
                $sqlNumber = "select order_numbering_id from ".HI\TableName\ORDER_NUMBERING_SD." where order_child_id = ".$order['order_child_id'];
                $info['order_numbering'] = Yii::$app->db->createCommand($sqlNumber)->queryOne()['order_numbering_id'];
            }
            
            if ($order['code'] == 1) {
                $hzProduct[] = ['name' => $order['name'], 'price' => $order['price'], 'quantity' => $order['quantity'], 'total' => $order['price'] * $order['quantity']];
                $sqlNumber = "select order_numbering_id from ".HI\TableName\ORDER_NUMBERING_ZJ." where order_child_id = ".$order['order_child_id'];
                $info['order_numbering'] = Yii::$app->db->createCommand($sqlNumber)->queryOne()['order_numbering_id'];
            }
        }
        $case = @$argv[1] ? $argv[1] : 1;
        switch ($case) {
            case 1:
                if ($sdProduct) {
                    $info['products'] = $sdProduct;
                    $resultSd = false;
                    while (!$resultSd) {
                        $resultSd = self::post($origin[$case], json_encode($info), $timeout = 300);
                        sleep(3);
                    }
                }
                break;
            case 2:
                if ($hzProduct) {
                    $info['products'] = $hzProduct;
                    $resultHz = false;
                    while (!$resultHz) {
                        $resultHz = self::post($origin[$case], json_encode($info), $timeout = 300);
                        sleep(3);
                    }
                }
                break;
        }
        echo 'ok';
    }
    
    public function post($url, $post_data = '', $timeout = 5){
    
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POST, 1);
        if($post_data != ''){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //严格认证
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    
    }
    public function test1Action ()
    {
        $postData = ['parternID' => 'KLYKJ', 'serviceType' => 'RequestQuery', 'mailNo'=> '70180121627478,70180125623355'];
        $postData['digest'] = base64_encode(md5($postData['mailNo'].'klykjdifu8kcx', true));
        $o = "";
        foreach ( $postData as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $postData = substr($o, 0, -1);
        $url = 'http://edi-q9.ns.800best.com/ems/api/process';
        $result = json_decode(self::request_post($url, $postData), true);
        foreach ($result['traceLogs'] as $traceLog) {
            $update = [];
            foreach ($traceLog['traces'] as $trace) {
                $update[] = [
                'AcceptTime' => $trace['acceptTime'],
                'AcceptStation' => $trace['acceptAddress'],
                'Remark' => $trace['remark'],
                ];
                if ($trace['scanType'] == '签收') {
                    $status = 3;
                }
                if (strpos(@$trace['acceptAddress'], '派送') !== false) {
                    $redisKey = 'HTKY_'.$traceLog['mailNo'];
                    if (Yii::$app->redis->get($redisKey) === false){
                        $connection = Yii::$app->db;
                        $sqlOrder = "select o.order_id, o.customer_id as uid from ".HI\TableName\ORDER_SPLITTING." as os
                                    inner join ".HI\TableName\ORDER." as o on o.order_id = os.order_id
                                    where os.shipping_id = ".$traceLog['mailNo'];
                        $orderInfo = $connection->createCommand($sqlOrder)->queryOne();
                        if ($orderInfo) {
                            $payload = [
                            'uid' => $orderInfo['uid'],
                            'message' => '您的订单编号:'.$orderInfo['order_id'].'正在配送中,请注意查收',
                            'type' => MiPush::TO_EXPRESS,
                            'payload' => ['type' => 2, 'order_id' => $orderInfo['order_id']],
                            ];
                            MiPush::submitWorks($payload);
                            Yii::$app->redis->set($redisKey, 1);
                            Yii::$app->redis->expire($redisKey, 3600*12);
                        }
                    }
                }
            }
            var_dump($update);
        }
    }
    
    
    
    public function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
    
        return $data;
    }
    
    
    public function getBytes($str) 
    {
        $len = strlen($str);
        $bytes = array();
        for($i=0;$i<$len;$i++) {
            if(ord($str[$i]) >= 128){
                $byte = ord($str[$i]) - 256;
            }else{
                $byte = ord($str[$i]);
            }
            $bytes[] =  $byte ;
        }
        return $bytes;
    }
    
    
    public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }
    
        return $str;
    }
}
