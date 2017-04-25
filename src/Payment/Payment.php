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


    /**
     * 创建支付
     */
    const PAY_URL = '/pay';
    static $payNeedRequestHmac = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo");
    static $payNeedResponseHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "externalid", 4 => "amount", 5 => "payurl");
    static $payRequest = array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "bankid", 10 => "period", 11 => "memo", 12 => "payproducttype", 13 => "userno", 14 => "ip", 15 => "cardname", 16 => "idcard", 17 => "bankcardnum",18=> "mobilephone",19 => "orderexpdate");
    static $payMustFillRequest = ["requestid","amount","callbackurl"];
    static $payNeedCallbackHmac = array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "notifytype", 4 => "externalid", 5 => "amount", 6 => "cardno");
    /**
     * 查询支付
     */
    const QUERY_URL = '/queryOrder';
    static $queryNeedRequestHmac =  array(0 => "requestid");
    static $queryNeedResponseHmac =  array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "externalid", 4 => "amount", 5 => "productname", 6 => "productcat", 7 => "productdesc", 8 => "status", 9 => "ordertype", 10 => "busitype", 11 => "orderdate", 12 => "createdate", 13 => "bankid");
    static $queryRequest = array(0 => "requestid");

    /**
     * 转账接口
     */
    const TRANSFER_URL = '/transfer';
    static $transferNeedRequestHmac = array(0 => "requestid", 1 => "ledgerno", 2 => "amount");
    static $transferMustFillRequest = array(0 => "requestid", 1 => "amount");
    static $transferRequest = array(0 => "requestid", 1 => "ledgerno", 2 => "amount");
    static $transferNeedResponseHmac =  array(0 => "customernumber", 1 => "requestid", 2 => "code");


    public function add($params = null)
    {
        $this->setUrl(self::PAY_URL);
        $this->setNeedRequest(self::$payRequest);
        $this->setNeedRequestHmac(self::$payNeedRequestHmac);
        $this->setNeedResponseHmac(self::$payNeedResponseHmac);
        $this->setPost($params);
        $response = $this->send();
        return $response;
    }

    /**
     * 查询支付状态
     */
    public function query($orderNo) {
        $this->setUrl(self::QUERY_URL);
        $this->setNeedRequest(self::$queryRequest);
        $this->setNeedRequestHmac(self::$queryNeedRequestHmac);
        $this->setNeedResponseHmac(self::$queryNeedResponseHmac);
        $this->setPost(['requestid'=>$orderNo]);
        $response = $this->send();
        return $response;
    }


    /**
     * 转账接口
     * @param $ledgerno
     * @param $amount
     */
    public function transfer($ledgerno,$amount,$requestid = null) {
        $requestid = $requestid ? $requestid : date("YmdHis") . rand(0000,9999);
        $this->setUrl(self::TRANSFER_URL);
        $this->setNeedRequest(self::$transferRequest);
        $this->setNeedRequestHmac(self::$transferNeedRequestHmac);
        $this->setNeedResponseHmac(self::$transferNeedResponseHmac);
        $this->setMustFillRequest(self::$transferMustFillRequest);
        $this->setPost(['requestid'=>$requestid,'ledgerno'=>$ledgerno,'amount'=>$amount]);
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
        foreach ( self::$payNeedCallbackHmac as $hKey => $hValue ) {
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