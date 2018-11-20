<?php
use Yaf\Dispatcher;
use Yaf\Registry;
use yii\db\Query;
use yii\db\Expression;
class Logger
{
    public static $name = "";
    public static $path = "";
    const ERROR = 400;
    public function __construct($path, $name, array $handlers = array(), array $processors = array())
    {
        self::$name = $name;
        self::$path = $path;
    }
    protected static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );
    public static function error($message, array $context = array())
    {
        return self::addRecord(static::ERROR, $message, $context);
    }
    public static function addRecord($level, $message, array $context = array()) {
        if(!file_exists(self::$path)) {
            mkdir(self::$path, 0755, true);
        };
        return file_put_contents(self::$path . "/" . self::$name . ".log", date("Y-m-d H:i:s") . $message . json_encode($context) . PHP_EOL,FILE_APPEND);
    }
}
class Log
{
    public static $logRoot = APP_PATH . "/runtime/logs";
    public static function commitLogger() {
        static $logger;
        $name = 'commit';
        if (!$logger) {
            $logger = new Logger(self::$logRoot, $name);
        }
        return $logger;
    }
}
?>