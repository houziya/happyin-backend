<?php
/**
 * 快递鸟物流信息查询
 * @author yantao
 *
 */
class Express
{
    //快递鸟秘钥
    public $appkey;
    //商户id
    public $businessId;
    //订阅地址
    public $subscribeUrl;
    
    public function __construct()
    {
        $this->businessId = 1256461;
        $this->appkey = '4b657b5f-d1f7-4db1-bc88-fc1b6f53bb03';
        $this->subscribeUrl = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
    }
    
    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    public function encrypt($data)
    {
        return urlencode(base64_encode(md5($data.$this->appkey)));
    }
    
    /**
     * 物流信息订阅
     */
    public function subscribe ($channel, $orders)
    {
        $orderInfo = [];
        foreach ($orders as $order) {
            $orderInfo[] = ['No' => $order];
        }
        $requestData = json_encode([
            'Code' => $channel,
            'Item' => $orderInfo,
        ]);
        $datas = [
                    'EBusinessID' => $this->businessId,
                    'RequestType' => '1005',
                    'RequestData' => urlencode($requestData) ,
                    'DataType' => '2',
                ];
        $datas['DataSign'] = self::encrypt($requestData);
        $result = self::sendPost($this->subscribeUrl, $datas);
        return $result;
    }
    
    /**
     * 物流信息获取
     */
    public function getInfo ($shipperCode, $logisticCode)
    {
        $requestData = json_encode([
            'ShipperCode' => $shipperCode,
            'LogisticCode' => $logisticCode,
        ]);
        $datas = [
            'EBusinessID' => $this->businessId,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        ];
        $datas['DataSign'] = self::encrypt($requestData);
        $result = self::sendPost($this->subscribeUrl, $datas);
        return $result;
    } 
    
    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], 80);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);
        return $gets;
    }
}