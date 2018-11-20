<?php
    //日期
    define('HI\\User\\MINUTE', 60);
    define('HI\\User\\HOUR', 3600);
    define('HI\\User\\DAY', 86400);
    define('HI\\User\\WEEK', 604800);
    define('HI\\User\\MONTH', 2592000);
    define('HI\\User\\YEAR', 31536000);
    //注册来源
    define('HI\\User\\REGISTER_TYPE_PHONE', 0);
    define('HI\\User\\REGISTER_TYPE_QQ', 1);
    define('HI\\User\\REGISTER_TYPE_SINA', 2);
    define('HI\\User\\REGISTER_TYPE_WECHAT', 3);
    //注册渠道
    define('HI\\User\\REGISTER_PLATFORM_IOS', 0);
    define('HI\\User\\REGISTER_PLATFORM_ANDROID', 1);
    define('HI\\User\\REGISTER_PLATFORM_H5_IOS', 2);
    define('HI\\User\\REGISTER_PLATFORM_H5_ANDROID', 3);
    define('HI\\User\\REGISTER_PLATFORM_H5_OTHERS', 4);
    //性别
    define('HI\\User\\REGISTER_GENDER_MALE', 1);
    define('HI\\User\\REGISTER_GENDER_FEMALE', 0);
    //帐号状态
    define('HI\\User\\ACCOUNT_NORMAL', 1);                //正常状态
    define('HI\\User\\ACCOUNT_UNLINK', 0);                //解绑状态
    //验证码可验证次数
    define('HI\\User\\ATTEMPTS_TIMES', 2);
    //默认头像
    define('HI\\User\\DEFAULT_AVATAR', 'profile/avatar/default.jpg');
    //验证码验证 文字提示
    define('HI\\User\\PHONE_CONTENT', '手机号已注册');
    define('HI\\User\\USER_NOT_EXIST', '账号不存在');
    define('HI\\User\\CAPTCHA_CONTENT', '验证码错误');
    //用户状态
    define('HI\\User\\STATUS_NORMAL', 0);
    //发送验证码信息文字
    define('HI\\User\\CAPTCHA_MESSAGE', '快乐印验证码:');
    //密码加密次数
    define('HI\\User\\ENCRYPT_NUM', 1000);

    //微信验证
    define('HI\\Config\\WECHAT_APPID', 'wxea7a5f9ccf96e433');
    define('HI\\Config\\WECHAT_SECRET', 'd4624c36b6795d1d99dcf0547af5443d');
    define('HI\\Config\\WECHAT_TOKEN', 'https://api.weixin.qq.com/sns/oauth2/access_token?');
    define('HI\\Config\\WECHAT_REFRESH_TOKEN', 'https://api.weixin.qq.com/sns/oauth2/refresh_token?');
    define('HI\\Config\\WECHAT_USER', 'https://api.weixin.qq.com/sns/userinfo?access_token=');
    define('HI\\Config\\WECHAT_UNIQUE_TOKEN', 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&');
    
    //校验session的类型
    define('HI\\User\\SESSION_KEY', 0);     //用户session
    define('HI\\User\\TUBE_SESSION_KEY', 1);    //用户tube-session

    // Configurable constants for deployment
    //验证码
    define('HI\\Config\\SMS_PRODUCT_ID', 129);
    define('HI\\Config\\SMS_TEMPLATE_ID', 75);
    define('HI\\Config\\SMS_PRODUCT_KEY', '15iVYeKg377D59d0');
    define('HI\\Config\\SMS_URL', 'http://access.hoolai.com/message_service/messageService/sendSms.hl?');

    //配置
    define('HI\\Config\\UPLOAD_DOMAIN', 'hipubdev-10023356.file.myqcloud.com');    //上传域名
    define('HI\\Config\\DOWNLOAD_DOMAIN', 'hipubdev-10006628.file.myqcloud.com');    //下载图片域名
    define('HI\\Config\\INIT_DOMAIN', 'hi.happyin.com.cn:9970');    //默认域名
    define('HI\\Config\\SPREAD_DOMAIN', '119.29.44.245:7239');    //订阅域名
    define('HI\\Config\\LOG_LEVEL', 4);    //日志级别
    define('HI\\Config\\FLAG', 1);
    define('HI\\Config\\ZK', '10.141.51.6:2181,10.141.51.6:2182,10.141.51.6:2183/devel');    //zk
    define('HI\\Config\\SHARE_LINK_PREFIX', 'http://hi.happyin.com.cn/share/share.html?invitation_code=');
    define('HI\\Config\\LIMIT_SIGN', 128);
    define('HI\\Config\\LIMIT_EVENT', 50);

    //统计路径
    define('HI\\Path\\WRITE_CLIENT_LOG', '/usr/local/nginx/logs/stat/');    //日志写入路径
    define('HI\\Path\\READ_LOG', '/usr/local/nginx/flume/');    //日志读取路径

    // Cache
    define('HI\\Config\\Cache\\CLASSNAME', 'yii\redis\Cache');
    define('HI\\Config\\Cache\\HOSTNAME', '10.141.51.6');
    define('HI\\Config\\Cache\\PORT', 7240);
    define('HI\\Config\\Cache\\PASSWORD', '3c7b9ef0e2ea2fd931373465363551efc66845f079fac0862d6c4bf8b5562c8af7ac02931731e4b3811e998c94c70ff148b8447bfd431d829cd2c996bf1e28d5');

    //脚本内存限制
    define('HI\\Config\\MEMORY_LIMIT', '512M');

    // 数据库
    define('HI\\Config\\Database\\CLASSNAME', 'yii\db\Connection');
    define('HI\\Config\\Database\\DBNAME', 'hi_devel');
    define('HI\\Config\\Database\\HOSTNAME', '10.66.161.251');
    define('HI\\Config\\Database\\PORT', 3306);
    define('HI\\Config\\Database\\DSN', 'mysql:host=' . HI\Config\Database\HOSTNAME . ';port=' . HI\Config\Database\PORT . ';dbname=' . HI\Config\Database\DBNAME);
    define('HI\\Config\\Database\\USERNAME', 'devel_readwrite');
    define('HI\\Config\\Database\\PASSWORD', '2EE6EA13-8D5E-4C34-8F94-CDD65B35E696');
    define('HI\\Config\\Database\\CHARSET', 'utf8');
    
    //表名
    define('HI\\TableName\\ORDER_PICTURE', 'order_picture');//订单用户照片表
    define('HI\\TableName\\ORDER_PICTURE_VERIFY', HI\Config\Database\DBNAME . '.order_picture_verify');//订单用户照片表
    define('HI\\TableName\\CUSTOMER', HI\Config\Database\DBNAME . '.customer');
    define('HI\\TableName\\CUSTOMER_CREDENTIAL', HI\Config\Database\DBNAME . '.customer_credential');
    define('HI\\TableName\\SYSTEM_CODE', HI\Config\Database\DBNAME . '.system_code');
    define('HI\\TableName\\CAPTCHA', HI\Config\Database\DBNAME . '.captcha');
    define('HI\\TableName\\CUSTOMER_DEVICE', HI\Config\Database\DBNAME . '.customer_device');
    define('HI\\TableName\\CUSTOMER_DEVICE_HISTORY', HI\Config\Database\DBNAME . '.customer_device_history');
    define('HI\\TableName\\ADDRESS', HI\Config\Database\DBNAME . '.address');
    define('HI\\TableName\\PRODUCT', HI\Config\Database\DBNAME . '.product');
    define('HI\\TableName\\CART', HI\Config\Database\DBNAME . '.cart');
    define('HI\\TableName\\PRODUCT_DESCRIPTION', HI\Config\Database\DBNAME . '.product_description');
    define('HI\\TableName\\PRODUCT_IMAGE', HI\Config\Database\DBNAME . '.product_image');
    define('HI\\TableName\\PRODUCT_to_CATEGORY', HI\Config\Database\DBNAME . '.product_to_category');
    define('HI\\TableName\\CATEGORY_DESCRIPTION', HI\Config\Database\DBNAME . '.category_description');
    define('HI\\TableName\\COUPON', HI\Config\Database\DBNAME . '.coupon');
    define('HI\\TableName\\COUPON_HISTORY', HI\Config\Database\DBNAME . '.coupon_history');
    define('HI\\TableName\\COUPON_PRODUCT', HI\Config\Database\DBNAME . '.coupon_product');
    define('HI\\TableName\\CUSTOMER_COUPON', HI\Config\Database\DBNAME . '.customer_coupon');
    define('HI\\TableName\\ORDER', HI\Config\Database\DBNAME . '.order');
    define('HI\\TableName\\ORDER_PRODUCT', HI\Config\Database\DBNAME . '.order_product');
    define('HI\\TableName\\ORDER_TOTAL', HI\Config\Database\DBNAME . '.order_total');
    define('HI\\TableName\\ORDER_SPLITTING', HI\Config\Database\DBNAME . '.order_splitting');
    define('HI\\TableName\\ORDER_LOG', HI\Config\Database\DBNAME . '.order_log');
    define('HI\\TableName\\CATEGORY', HI\Config\Database\DBNAME . '.category');
    define('HI\\TableName\\PRODUCT_ATTRIBUTE', HI\Config\Database\DBNAME . '.product_attribute');
    define('HI\\TableName\\PRODUCT_OPTION', HI\Config\Database\DBNAME . '.product_option');
    define('HI\\TableName\\PRODUCT_OPTION_VALUE', HI\Config\Database\DBNAME . '.product_option_value');
    define('HI\\TableName\\PRODUCT_RELATED', HI\Config\Database\DBNAME . '.product_related');
    define('HI\\TableName\\PRODUCT_TO_CATEGORY', HI\Config\Database\DBNAME . '.product_to_category');
    define('HI\\TableName\\PRINT_TYPE', HI\Config\Database\DBNAME . '.print');
    define('HI\\TableName\\PRINT_TO_PRODUCT', HI\Config\Database\DBNAME . '.print_to_product');
    define('HI\\TableName\\ORDER_NUMBERING_SD', HI\Config\Database\DBNAME . '.order_numbering_sd');
    define('HI\\TableName\\ORDER_NUMBERING_ZJ', HI\Config\Database\DBNAME . '.order_numbering_zj');

    // Redis
    define('HI\\Config\\Redis\\HOSTNAME', '10.141.51.6');
    define('HI\\Config\\Redis\\PORT', 7240);
    define('HI\\Config\\Redis\\TIMEOUT', 1);
    define('HI\\Config\\Redis\\RETRY_INTERVAL', 100);
    define('HI\\Config\\Redis\\AUTH', '3c7b9ef0e2ea2fd931373465363551efc66845f079fac0862d6c4bf8b5562c8af7ac02931731e4b3811e998c94c70ff148b8447bfd431d829cd2c996bf1e28d5');

    // Session
    define('HI\\Config\\Session\\CLASSNAME', 'yii\redis\Session');
    define('HI\\Config\\Session\\HOSTNAME', '10.141.51.6');
    define('HI\\Config\\Session\\PORT', 7240);
    define('HI\\Config\\Session\\PASSWORD', '3c7b9ef0e2ea2fd931373465363551efc66845f079fac0862d6c4bf8b5562c8af7ac02931731e4b3811e998c94c70ff148b8447bfd431d829cd2c996bf1e28d5');
    
    //前端URL常量
    define('HI\\APP_URL_PREFIX', 'http://dev.happyin.com.cn:9960');//http://119.29.77.36:9962/
    define('HI\\APP_URL', HI\APP_URL_PREFIX . "/");
   
    //ios下载地址
    define('HI\\Config\\IOS_DOWNLOAD_URL', 'https://itunes.apple.com/cn/app/us/id1041870519');
    
    //后台
    //OSAdmin常量
    define('Console\\ADMIN_URL', 'http://dev.happyin.com.cn:9960/');
    define('Console\\ADMIN_TITLE', '快乐印管理后台');
    define('Console\\COMPANY_NAME', '北京聚说科技有限公司');
    //COOKIE加密密钥
    define('Console\\ADMIN\\ENCRYPT_KEY', 'comeonusyoubest!');
    
    // Tencent COS credentials and configs
    define('HI\\Config\\QCloud\\APP_ID', '10006628');
    define('HI\\Config\\QCloud\\SECRET_ID', 'AKIDwBwXfOISF1LWSoaoqCuCS2cRCwzhCIvk');
    define('HI\\Config\\QCloud\\SECRET_KEY', 'LI5IrAYcMPHMDyrA02tRqSQyckV8yPvB');
    define('HI\\Config\\QCloud\\PUBLIC_BUCKET', 'hipubdev');
    define('HI\\Config\\QCloud\\PRIVATE_BUCKET', 'hiprivdev');
    define('HI\\Config\\QCloud\\COS_USER_AGENT', 'tencent-httputils/1.1');
    define('HI\\Config\\QCloud\\COS_UPLOAD', 'http://web.file.myqcloud.com/files/v1/');
    define('HI\\Config\\QCloud\\COS_SIGN_EXPIRE', 20);
    define('HI\\Config\\QCloud\\IMAGE\\DOMAIN', 'http://hipubdev-10006628.file.myqcloud.com');

    //过期时间
    define('HI\\Config\\SESSION_EXPIRE', 0);    //session_key 过期时间
    define('HI\\Config\\CAPTCHA_ATTEMPTS_EXPIRE', 86400);    //验证码失败次数
    define('HI\\Config\\SIGN_EXPIRE', 1);    //sign_key 过期时间
    define('HI\\Config\\INVITE_EXPIRE', 31536000);    //小组邀请过期时间

    //可解绑数
    define('HI\\User\\UNLINK_NUM', 2);

    //MiPush configuration
    define('HI\\MI_PUSH\\IOS_SECRET', 'NQAEPlJjAw8RasbftVhWpQ==');
    define('HI\\MI_PUSH\\IOS_BUNDLE_ID', 'com.hoolai.us');
    define('HI\\MI_PUSH\\ANDROID_SECRET', '/zQlEWfUzZcahDqxrV2Irg==');
    define('HI\\MI_PUSH\\ANDROID_BUNDLE_ID', 'com.hoolai.us');
    
    //验证码开关
    define('HI\\Captcha\\SEND', 1);                 //发送验证码开关；1-开0-关
    define('HI\\Captcha\\VERIFY', 1);                 //验证验证码开关；1-开0-关

    // Cross Domain Access Control
    define('HI\\Config\\ALLOWED_ORIGIN', 'hi.happyin.com.cn');

    // rabbitmq
    define('HI\\Config\\RabbitMQ\\HOSTNAME', '10.141.51.6');
    define('HI\\Config\\RabbitMQ\\PORT', 5672);
    define('HI\\Config\\RabbitMQ\\USERNAME', 'devel');
    define('HI\\Config\\RabbitMQ\\PASSWORD', '431fb46091d07622b56dba8f3f6f316f5e18526a99b3d4b7438bd9069745246d');
    define('HI\\Config\\RabbitMQ\\VHOST', '/devel');
    
    define('HI\\Config\\CONTENT_GENERATOR_MEMORY_LIMIT', 512);
    
    //RabbitMQ queues
    define('HI\\Config\\Queue\\ORDER_POST_PROCESS', 'order-post-process');
    
    //Products
    define('HI\\Config\\Product\\POSTER_PRODUCT_ID', 58);
    define('HI\\Config\\Product\\Poster\\FRAMESET_SETTING', '[6, 9, 50.71533333333333, 73.67693333333333, 7.45066666666667, 0.2032]');
    define('HI\\Config\\Product\\PRINT_PRODUCTS', '[53 => 0.02521, 54 => 0.02083333333, 55 => 0.02083333333, 56 => 0.02083333333]');
    define('HI\\Config\\Product\\POSTER_QUALITY', 75);
    define('HI\\Config\\Product\\MemoryBox\\SETTING', '[2930, 2930]');
    
    //Products
    define('HI\\Config\\Product\\MEMORY_BOX_PRODUCT_ID', 67);
    define('HI\\Config\\Product\\PHOTO_CARDS_PRODUCT_ID', 61);
    define('HI\\Config\\Product\\LOMO_CARDS_PRODUCT_ID', 55);
    define('HI\\Config\\Product\\FIVE_INCH', 53);
    define('HI\\Config\\Product\\SIX_INCH', 54);
    define('HI\\Config\\Product\\PICTURE_ALBUM', 62);
    define('HI\\Config\\Product\\GRID_ONE_BLACK', 68);
    define('HI\\Config\\Product\\GRID_ONE_WHITE', 63);
    define('HI\\Config\\Product\\GRID_TWO_BLACK', 69);
    define('HI\\Config\\Product\\GRID_TWO_WHITE', 64);
    define('HI\\Config\\Product\\GRID_THREE_BLACK', 70);
    define('HI\\Config\\Product\\GRID_THREE_WHITE', 65);
    define('HI\\Config\\Product\\GRID_FOUR_BLACK', 71);
    define('HI\\Config\\Product\\GRID_FOUR_WHITE', 66);
    define('HI\\Config\\Product\\BIG_POSTER', 60);
    define('HI\\Config\\Product\\PHOTO_GALLERY_THREE', 50);
    define('HI\\Config\\Product\\PHOTO_GALLERY_FIVE', 51);
    define('HI\\Config\\Product\\PHOTO_GALLERY_SIX', 52);
    
    //常用地区
    define('HI\\Config\\COMMON_CITY', '上海市, 浙江省, 江西省');
    define('HI\\Config\\ORDER_COUNT', '100');
    
    //order status
    define('HI\Config\Order\COMPLETE_STATUS_ID', 5);
    
    define('HI\Config\MIPUSH_NODE', "push");
    define('HI\\Config\\DROP_DOWN',[
        'fe36bffc-bac9-4f88-91ac-9258cfb92605',
        'e90f8736-62d2-4e25-bf5c-0e159d7af2c8',
        '5b664d04-7677-4729-b8cd-62bfb30e105b',
        '0564fd13-af69-422d-a4a5-bd2cea53ddfe',
        'ceb97ffe-05ac-45bc-8f9c-ef80e93ed2b9',
        'b314065e-82e3-4ab0-abaf-cbecb5af037f',
        '6295437e-fb6d-4fc3-96b5-bf9f47b3062d',
        '08130efc-30e1-4a03-8ab9-b9026179edc7',
        '55e54a33-3bd9-49f8-aa0e-433020c900b3',
        'a199453f-10a5-4261-964d-7c48b8be8d6d',
        '96157c82-457c-46f9-a3cd-d99560052000',
        'd6686881-1407-43ec-8edf-91f2c16341f0',
    ]);
    
    //PAY
    define('HI\\Config\\Pay\\WX_PARTNER_ID', "1338979901");
    define('HI\\Config\\Pay\\WX_PARTNER_KEY', "happyinzxcvbnm52013105718008zhuX");
    define('HI\\Config\\Pay\\WX_APP_ID', "wxe63a6af33be57b5d");
    define('HI\\Config\\Pay\\WX_APP_SECRET', "bc88d2d1352bff72c3c3e66628c2f89d");
    define('HI\\Config\\Pay\\WX_APP_CALLBACK', "Catalog/Pay/wechatNotify");
    
    define('HI\\Config\\Pay\\ALI_PARTNER_ID', "2088221719922971");
    define('HI\\Config\\Pay\\ALI_PARTNER_SELLER', "zhanghao@happyin.com.cn");
    define('HI\\Config\\Pay\\ALI_PRIVATE_KEY', "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJ+RhsIGGEDcPkotM/Ge+CMmqkwMgBcw4MNDBPOhvJzmkaA6wRy6Arjifacx7P03Rzs/Ct9ed0EimiM1famEcHLj+3keM7Uu0AxaQ+6KJwosjVPCRdLVzIQ4t6B02ZXS6jqiPC4oxpF232/rTWeunAQdsVnjN6QGPR1bAt6DuhbbAgMBAAECgYArp9rgdl5oUFlSXe+aKU3mAAwTZlAdCChWA531MciyfHpXBfwTaeiYwI50EbquxFLeqK7G0cd6sIbPHzFUEYHZ3l2V038Hy7PAkm+GfcK8XC2O5p1gQOU4gAr6MSE4FOwIaI20au4kjCldUAqe0O1taShwt5KX7ImBx6vstt2I8QJBAM3PUXuVkBE72/cHTOApVVR9O1d3SBfUdbUHpcBihnchwohA24uoHH/kF04c/iINL7YAhc+yw3nUozdia5/AWm8CQQDGe11yZ2K6U5BjdsHbNARqRKaQYcL+FWROPS7RM0fPi6B/x8tzl15JkMjYeKU8ir3PLY+DqpPcXyKK6ot3LPBVAkBiaz3gB4m5dBKgcxFEMP/pQ2nZTeVf3+3aACo3ceYzmiVY2wsiIeZfEkhCxoKQ7hVex6k1xZrM6+6RUbN91ZJpAkAsA41cYkrFcORy+hHRDz7vZdvd5p/8AjhlFTI9l/aTsq3byC14q5dwDthLEK3ruASRXdQWaYwycTJSxeI5glClAkEArZvhJwZHVKSYRJoqumLCk4JWRETKQT9sqg2JHtfVjWRTRxvGzuRe8kztf5Z1PmI6O9LxvzfZ7kx+YJgvtXpUew==");
    define('HI\\Config\\Pay\\ALI_APP_CALLBACK', "Catalog/Pay/aliNotify");
    
    //Content Generate settings
    define('HI\\Config\\CONTENT_GENERATOR_RESOLUTION_LIMIT', '[1960, 1200]');
    define('HI\\Config\\CONTENT_GENERATOR_QUALITY', '[50, 75, 95]');
    define('HI\\Config\\CONTENT_GENERATOR_FILE_SIZE_LIMIT', 10485760);
    define('HI\\Config\\CONTENT_GENERATOR_RATE_LIMIT', 16);
    define('HI\\Config\\CONTENT_GENERATOR_RATE_LIMIT_EXPIRE', 600);
    define('HI\\Config\\CONTENT_GENERATOR_RATE_LIMIT_KEY_PREFIX', 'limit.content.');
    define('HI\\Config\\CONTENT_GENERATOR_FORCE_JPEG', false);
    
    //ADMIN EXPRESS INPUT
    define('HI\\Config\\EXPRESS\\ADMIN_ORDER_NUMBER', 0);
    define('HI\\Config\\EXPRESS\\ADMIN_SPLITTING_ID', 2);
    define('HI\\Config\\EXPRESS\\ADMIN_SPLITTING_COMPANY', 1);
?>
