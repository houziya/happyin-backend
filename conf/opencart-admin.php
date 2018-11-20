<?php
require_once("constants.php");

//HTTP
define('HTTP_SERVER', HI\APP_URL . 'admin/');
define('HTTP_CATALOG', HI\APP_URL . '');

// HTTPS
define('HTTPS_SERVER', HI\APP_URL . 'admin/');
define('HTTPS_CATALOG', HI\APP_URL . '');

// DIR
define('DIR_APPLICATION', '/usr/local/nginx/happyin/public/admin/');
define('DIR_SYSTEM', '/usr/local/nginx/happyin/public/system/');
define('DIR_LANGUAGE', '/usr/local/nginx/happyin/public/admin/language/');
define('DIR_TEMPLATE', '/usr/local/nginx/happyin/public/admin/view/template/');
define('DIR_CONFIG', '/usr/local/nginx/happyin/public/system/config/');
define('DIR_IMAGE', '/usr/local/nginx/happyin/public/image/');
define('DIR_CACHE', '/usr/local/nginx/happyin/public/system/storage/cache/');
define('DIR_DOWNLOAD', '/usr/local/nginx/happyin/public/system/storage/download/');
define('DIR_LOGS', '/usr/local/nginx/happyin/public/system/storage/logs/');
define('DIR_MODIFICATION', '/usr/local/nginx/happyin/public/system/storage/modification/');
define('DIR_UPLOAD', '/usr/local/nginx/happyin/public/system/storage/upload/');
define('DIR_CATALOG', '/usr/local/nginx/happyin/public/catalog/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', HI\Config\Database\HOSTNAME);
define('DB_USERNAME', HI\Config\Database\USERNAME);
define('DB_PASSWORD', HI\Config\Database\PASSWORD);
define('DB_DATABASE', HI\Config\Database\DBNAME);
define('DB_PORT', HI\Config\Database\PORT);
define('DB_PREFIX', '');

?>
