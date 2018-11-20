<?php

class ProtocolException extends Exception
{
    private $hpCode;
    private $hpNotice;
    private $hpMessage;
    private $hpPayload;
    public function __construct($code, $notice = NULL, $message = NULL, $payload = NULL, $hint = NULL)
    {
        $this->hpCode = $code;
        $this->hpNotice = $notice;
        $this->hpMessage = $message;
        $this->hpPayload = $payload;
        $this->hpHint = $hint;
    }

    public function send()
    {
        Protocol::jsonReturn($this->hpCode, $this->hpPayload, $this->hpNotice, $this->hpMessage, $this->hpHint);
    }
}

class HttpException extends Exception
{
    private $httpCode;
    private $httpMessage;

    public function __construct($code, $exception = NULL, $message = "")
    {
        parent::__construct($message, $code, $exception);
        $this->httpMessage = $message;
        $this->httpCode = $code;
    }

    public function send()
    {
        http_response_code($this->httpCode);
        echo $this->httpMessage;
    }
}

try {
    define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
    $app = new Yaf\Application(APP_PATH . "/conf/application.ini");
    $app->bootstrap()->run();
} catch (ProtocolException $e) {
    $e->send();
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
} catch (HttpException $e) {
    $e->send();
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
} catch (InvalidArgumentException $e) {
    $app = Yaf\Application::app();
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    if (Predicates::isNull($app) || !$app->getConfig()->development) {
        Protocol::jsonReturn(Protocol::STATUS_BAD_REQUEST, NULL, $e->getMessage());
    } else {
        echo $e->getTraceAsString();
    }
} catch (Exception $e) {
    $app = Yaf\Application::app();
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    if (Predicates::isNull($app) || !$app->getConfig()->development) {
        Protocol::jsonReturn(Protocol::STATUS_INTERNAL_ERROR, NULL, Notice::get()->internalError());
    } else {
        echo $e->getTraceAsString();
    }
}

?>
