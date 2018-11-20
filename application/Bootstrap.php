<?php
use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;
use Yaf\Application;
use Yaf\Config\Ini;
use Yaf\Route\Rewrite;

function createTempFile()
{
    $tmpRoot = APP_PATH . "/runtime/tmp";
    $retried = false;
retryTmp:
    $tmpPath = tempnam($tmpRoot, "happyin");
    if (substr($tmpPath, 0, strlen($tmpRoot)) !== $tmpRoot) {
        if ($retried) {
            throw new Exception("Could not create tmp directory " . $tmpRoot);
        }
        error_log("Try to create tmp directory " . $tmpRoot);
        mkdir($tmpRoot, 0755, true);
        unlink($tmpPath);
        $retried = true;
        goto retryTmp;
    }
    return $tmpPath;
}

class Bootstrap extends Bootstrap_Abstract
{
    public function _initAutoLoad(Dispatcher $dispatcher)
    {
        require(__DIR__ . '/../vendor/autoload.php');
        require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
    }

    public function _initConstants(Dispatcher $dispatcher)
    {
        require(__DIR__ . '/../conf/constants.php');
    }

    public function _initYii(Dispatcher $dispatcher)
    {
        $config = require(__DIR__ . '/../conf/yii.php');
        new yii\web\Application($config);
    }

    public function _initDisableAutoRender(Dispatcher $dispatcher)
    {
        $dispatcher->autoRender(FALSE);
    }

    public function _initPlugins(Dispatcher $dispatcher)
    {
        $config = Application::app()->getConfig();
        if ($config->instrument->enabled == true) {
            $dispatcher->registerPlugin(new InstrumentationPlugin($config));
        }
        if ($config->trace->enabled == true) {
            $dispatcher->registerPlugin(new TracePlugin($config));
        }
    }

    public function _initVerifySession(Dispatcher $dispatcher)
    {
        if (Predicates::isEmpty(($uri = strtolower($dispatcher->getRequest()->getRequestUri())))) {
            return;
        }
        $console = explode('/', $uri)[1];
        if ($console != HI\Config\CONSOLE && !in_array($console, [HI\Config\S, HI\Config\PS, HI\Config\OS, HI\Config\ASR, HI\Config\BS, HI\Config\P, HI\Config\A])) {
            if (defined('HI\Config\PUBLIC_ACTIONS') && !array_key_exists($uri, HI\Config\PUBLIC_ACTIONS)) {
                if (!Session::checkVerify(Protocol::optional("login_uid", ""), Protocol::optional("device_id", ""))) {
                    Protocol::unauthorized();
                }
            }
        }
    }
    
    public function _initRoute(Dispatcher $dispatcher)
    {
        $config = Application::app()->getConfig();
        if ($config->content->generator->enabled == true) {
            $dispatcher->getRouter()->addRoute("images", new Rewrite(HI\Config\A.'/images/:fileName', ["module" => "Catalog", "controller" => "Content", "action" => "generateImages"]));
            $dispatcher->getRouter()->addRoute("avatar", new Rewrite(HI\Config\P.'/avatar/:fileName', ["module" => "Catalog", "controller" => "Content", "action" => "generateAvatar"]));
            $dispatcher->getRouter()->addRoute("share", new Rewrite(HI\Config\S.'/:code', ["module" => "Catalog", "controller" => "Picture", "action" => "product"]));
            $dispatcher->getRouter()->addRoute("cShare", new Rewrite(HI\Config\PS.'/:code', ["module" => "Catalog", "controller" => "Catalog", "action" => "redirection"]));
            $dispatcher->getRouter()->addRoute("oShare", new Rewrite(HI\Config\OS.'/:code', ["module" => "Catalog", "controller" => "Catalog", "action" => "redirection"]));
            $dispatcher->getRouter()->addRoute("aShare", new Rewrite(HI\Config\ASR.'/:code', ["module" => "Catalog", "controller" => "Catalog", "action" => "redirection"]));
            $dispatcher->getRouter()->addRoute("bShare", new Rewrite(HI\Config\BS.'/:code', ["module" => "Catalog", "controller" => "Catalog", "action" => "redirection"]));
        }
    }
    
    public function _initCrossOriginAccessControl($dispatcher)
    {
        if (Predicates::isNotEmpty($origin = Protocol::origin()) && Predicates::isNotEmpty($url = @parse_url($origin)) && Predicates::isNotEmpty($host = @$url["host"])) {
            if (in_array($host, explode(",", HI\Config\ALLOWED_ORIGIN))) {
                header("Access-Control-Allow-Origin: $origin");
            }
        }
    }
}
?>
