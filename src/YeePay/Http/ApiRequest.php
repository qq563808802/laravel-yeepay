<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/29
 * Time: 21:32
 */

namespace YeePay\YeePay\Http;

use YeePay\Config;
use YeePay\YeePay\Exceptions\Exception;
use YeePay\YeePay\Util\Util;

class ApiRequest {

    public $requestUrl;

    public $postField;

    public $postFile = [];


    public $responseInfo;

    public $responseCode;

    public $curlHandle;

    public $response;

    public $responseData;


    public $needRequestHmac;

    public $needRequest;

    public $needResponseHmac;

    protected $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function setUrl($url) {
        $this->requestUrl = $url;
    }

    public function setNeedRequestHmac($needRequestHmac)
    {
        $this->needRequestHmac = $needRequestHmac;
    }

    public function setNeedRequest($needRequest) {
        $this->needRequest = $needRequest;
    }

    public function setNeedResponseHmac($needResponseHmac){
        $this->needResponseHmac = ($needResponseHmac);
    }




    public function setPost($post,$needRequestHmac,$needRequest) {

        $this->needRequestHmac = $needRequestHmac;
        $this->postField = Util::getPostData($post,$needRequestHmac,$needRequest);
    }

    public function setFile($file) {
        $this->postFile = ['file' => $file ];
    }

    public function send() {
       $this->response =  (new Request($this->requestUrl,$this->postField,$this->postFile))->send();
       return $this->receviceResponse();
    }

    public function receviceResponse() {
        $responseJsonArray = json_decode($this->response, true);
        if ( array_key_exists("code", $responseJsonArray)
            && "1" != $responseJsonArray["code"] ) {

            throw new Exception("response error, errmsg = ["
                . $responseJsonArray["msg"]
                . "], errcode = ["
                . $responseJsonArray["code"]
                . "]
									 . ", $responseJsonArray["code"]);
        }

        $responseData = Util::getDeAes($responseJsonArray["data"],Config::getAesKey());
        $result = json_decode($responseData, true);
        //进行UTF-8->GBK转码
        $resultLocale = array();
        foreach ( $result as $rKey => $rValue ) {
            if (gettype($rValue) != "array")
            {
                $resultLocale[$rKey] = $rValue;
            }else
            {
                $resultLocale[$rKey][0]=cn_json_encode($resultLocale);
            }

        }
        $this->responseData = $resultLocale;



        if (  "1" != $result["code"] ) {

            throw new Exception("response error, errmsg = [" . $resultLocale["msg"] . "], errcode = [" . $resultLocale["code"] . "].", $result["code"]);
        }

        if ( $result["customernumber"] != Config::getAccount() ) {

            throw new Exception("customernumber not equals, request is [" .  Config::getAccount()  . "], response is [" . $result["customernumber"] . "].");
        }

        //验证返回签名
        $hmacGenConfig = $this->needResponseHmac;
        $hmacData = array();
        foreach ( $hmacGenConfig as $hKey => $hValue ) {

            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if ( Util::isViaArray($result, $hValue) && $result[$hValue] ) {

                $v = $result[$hValue];
            }

            //取得对应加密的明文的值
            //$hmacData[$hKey] = $v;
            $hmacData[$hKey] = $v;
        }
        $hmac = Util::getHmac($hmacData, Config::getPrivateKey());

        if ( $hmac != $result["hmac"] ) {

            throw new Exception("hmac not equals, response is [" . $result["hmac"] . "], gen is [" . $hmac . "].");
        }

        if ( array_key_exists("customError", $result)
            && "" != $result["customError"] ) {

            throw new Exception("response.customError error, errmsg = [" . $resultLocale["customError"] . "], errcode = [" . $resultLocale["code"] . "].", $result["code"]);
        }

        return collect($resultLocale);
    }

}