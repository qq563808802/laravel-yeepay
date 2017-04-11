<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/4/7
 * Time: 18:36
 */
namespace YeePay\Payment;
use YeePay\YeePay\Exceptions\Exception;
use YeePay\YeePay\Http\ApiRequest;
use YeePay\YeePay\Util\Util;

class Payment extends ApiRequest{



    const PAY_URL = '/pay';
    static $payNeedRequestHmac = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo");
    static $payNeedResponseHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "externalid", 4 => "amount", 5 => "payurl");
    static $payRequest = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo", 12 => "payproducttype", 13 => "userno", 14 => "ip", 15 => "cardname", 16 => "idcard", 17 => "bankcardnum",18=> "mobilephone",19 => "orderexpdate");
    static $payMustFillRequest = ["requestid","amount","callbackurl"];
    static $needCallbackHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "notifytype", 4 => "externalid", 5 => "amount", 6 => "cardno");



    public function add($params = null)
    {
        $this->setUrl($this->config['baseUrl'] . self::PAY_URL);
        $this->setPost($params,self::$payNeedRequestHmac,self::$payRequest);
        $this->setNeedRequest(self::$payRequest);
        $this->setNeedRequestHmac(self::$payNeedRequestHmac);
        $this->setNeedResponseHmac(self::$payNeedResponseHmac);

        $response = $this->send();
        return $response;
    }

    public function callback() {
        $data = request('data');
        if(!$data) {
            throw new Exception('没有数据');
        }
        $responseData = Util::getDeAes($data, $this->config['aesKey']);

        $result = json_decode($responseData, true);
        if ( "1" != $result["code"] ) {

            throw new Exception("response error, errmsg = [" . $result["msg"] . "], errcode = [" . $result["code"] . "].", $result["code"]);
        }

        if ( array_key_exists("customError", $result)
            && "" != $result["customError"] ) {

            throw new Exception("response.customError error, errmsg = [" . $result["customError"] . "], errcode = [" . $result["code"] . "].", $result["code"]);
        }

        if ( $result["customernumber"] != $this->config['account'] ) {
            throw new Exception("customernumber not equals, request is [" . $this->config['account'] . "], response is [" . $result["customernumber"] . "].");
        }
        $hmacData = [];
        foreach ( self::$needCallbackHmac as $hKey => $hValue ) {
            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if ( Util::isViaArray($result, $hValue) && $result[$hValue] ) {

                $v = $result[$hValue];
            }

            //取得对应加密的明文的值
            $hmacData[$hKey] = $v;
        }
        $hmac = Util::getHmac($hmacData,$this->config['merchantPrivateKey']);
        if($hmac !== $result['hmac']) {
            throw new \Exception('hmac not equals');
        }
        return $result;
    }

}