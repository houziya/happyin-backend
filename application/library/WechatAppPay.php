<?php
class WechatAppPay extends WechatPayBase
{
    //package参数
    public $package = [];
    //异步通知参数
    public $notify = [];
    //推送预支付订单参数
    protected $config = [];
    //access token
    protected $accessToken;
    //取access token的url
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
    //生成预支付订单提交地址
    //const POST_ORDER_URL = 'https://api.weixin.qq.com/pay/genprepay?access_token=%s';
    const POST_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    /**
     * 创建APP支付最终返回参数
     * @throws \Exception
     * @return multitype:string NULL
     */
    public function createAppPayData()
    {
        $this->generateConfig();
        $prepayid = $this->getPrepayid();
        return ['prepay_id' =>$prepayid, 'nonce_str' => $this->config['nonce_str'], 'sign' => $this->config['sign']];
    }
    
    /**
     * 验证支付成功后的通知参数
     * 
     * @throws \Exception
     * @return boolean
     */
    public function verifyNotify()
    {
        try{
            $staySignStr = $this->notify;
            unset($staySignStr['sign']);
            $sign = $this->signData($staySignStr);
            return $this->notify['sign'] === $sign;
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 魔术方法，给添加支付参数进来
     * 
     * @param string $name  参数名
     * @param string $value  参数值
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    /**
     * 设置access token
     * @param string $token
     * @throws \Exception
     * @return boolean
     */
    public function setAccessToken()
    {
        $content = Yii::$app->redis->get('hi_wx_token');
        if(!empty($content)) {
            $info = json_decode($content, true);
            if( time() - $info['getTime'] < 7150 ) {
                $this->accessToken = $info['accessToken'];
                return true;
            }
        }
        $this->outputAccessToken();
        return true;
    }
    /**
     * 写入access token 到文件
     * @throws \Exception
     * @return boolean
     */
    protected function outputAccessToken()
    {
        $token = [
            'accessToken' => $this->getAccessToken(),
            'getTime' => time(),
        ];
        $this->accessToken = $token['accessToken'];
        return Yii::$app->redis->set('hi_wx_token', json_encode($token));
    }
    /**
     * 取access token
     * 
     * @throws \Exception
     * @return string
     */
    protected function getAccessToken()
    {
        $url = sprintf(self::ACCESS_TOKEN_URL, $this->appid, $this->appSecret);
        $result = json_decode( $this->getUrl($url), true );
        if(isset($result['errcode'])) {
            throw new \Exception("get access token failed:{$result['errmsg']}");
        }
        return $result['access_token'];
    }
    /**
     * 取预支付会话标识
     * 
     * @throws \Exception
     * @return string
     */
    protected function getPrepayid()
    {
        $data = $this->arrayToXml($this->config);
        $result = $this->xmlToArray($this->postUrl(self::POST_ORDER_URL, $data));
        if( !isset($result['prepay_id']) ) {
            throw new \Exception('get prepayid failed, url request error.');
        }
        return $result['prepay_id'];
    }
    /**
     * 组装预支付参数
     * 
     * @throws \Exception
     */
    protected function generateConfig()
    {
        try{
            $this->config = [
                'appid' => $this->appid,
                'mch_id' => $this->partnerId,
                'device_info' => 'WEB',
                'nonce_str' => $this->getRandomStr(),
                'body' => $this->body,
                'out_trade_no' => $this->out_trade_no,
                'total_fee' => $this->total_fee,
                'spbill_create_ip' => $this->clent_ip,
                'notify_url' => $this->notify_url,
                'trade_type' => "APP",
            ];
            $this->config['sign'] = $this->generateSign();
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
     * 调起支付参数
     *
     * @throws \Exception
     */
    public function generatePayConfig()
    {
        $nonce_str = $this->getRandomStr();
            $this->configPay = [
                'appid' => $this->appid,
                'partnerid' => $this->partnerId,
                'prepayid' => $this->prepayId,
                'package' => 'Sign=WXPay',
                'noncestr' => $this->config['nonce_str'],
                'timestamp' => time(),
            ];
            return ['sign' =>$this->generatePaySign(), 'nonce_str' => $this->config['nonce_str']];
        
    }
    /**
     * 生成package字段
     * 
     * 生成规则:
     * 1、生成sign的值signValue
     * 2、对package参数再次拼接成查询字符串，值需要进行urlencode
     * 3、将sign=signValue拼接到2生成的字符串后面得到最终的package字符串
     * 
     * 第2步urlencode空格需要编码成%20而不是+
     * 
     * RFC 1738会把 空格编码成+
     * RFC 3986会把空格编码成%20
     * 
     * @return string
     */
    protected function generatePackage()
    {
        $this->package['sign'] = $this->signData($this->package);
        return http_build_query($this->package, '', '&', PHP_QUERY_RFC3986);
    }
    /**
     * 生成签名
     * 
     * @return string
     */
    protected function generateSign()
    {
        return $this->signData($this->config);
    }
    
    /**
     * 生成签名
     *
     * @return string
     */
    protected function generatePaySign()
    {
        return $this->signData($this->configPay);
    }
    
    /**
     * 签名数据
     * 
     * 生成规则:
     * 1、字典排序，拼接成查询字符串格式，不需要urlencode
     * 2、上一步得到的字符串最后拼接上key=paternerKey
     * 3、MD5哈希字符串并转换成大写得到sign的值signValue
     * 
     * @param array $data 待签名数据
     * @return string 最终签名结果
     */
    protected function signData($data)
    {
        ksort($data);
        $str = $this->arrayToString($data);
        $str .= "&key={$this->partnerKey}";
        return strtoupper( $this->signMd5($str) );
    }
    /**
     * sha1签名
     * 签名规则
     * 1、字典排序
     * 2、拼接查询字符串
     * 3、sha1运算
     * 
     * @param array $arr
     * @return string
     */
    protected function sha1Sign($arr)
    {
        ksort($arr);
        return sha1( $this->arrayToString($arr) );
    }
}