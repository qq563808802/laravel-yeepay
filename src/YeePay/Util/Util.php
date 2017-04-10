<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/3/30
 * Time: 10:30
 */
namespace Yeepay\YeePay\Util;
use YeePay\YeePay\Util\CryptAES;

class Util {

    public static function getPostData($post,$needRequestHmac,$needRequest) {
        //生成签名
        $hmacData["customernumber"] = config('yeepay.account');
        foreach ( $needRequestHmac as $hKey => $hValue ) {
            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if ( isset($post[$hValue])) {
                $v = $post[$hValue];
            }
            $hmacData[$hValue] = $v;
        }
        $hmac = self::getHmac($hmacData,config('yeepay.merchantPrivateKey'));
        $dataMap["customernumber"] = config('yeepay.account');//商户号
        foreach ( $needRequest as $rKey => $rValue ) {

            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if (isset($post[$rValue])) {

                $v = $post[$rValue];
            }
            //取得对应加密的明文的值
            $dataMap[$rValue] = $v;
        }
        $dataMap["hmac"] = $hmac;

        //转换成json格式
        $dataJsonString = self::cn_json_encode($dataMap);
        $data = self::getAes($dataJsonString, substr(config('yeepay.merchantPrivateKey'), 0, 16));
        $postfields = array("customernumber" => config('yeepay.account'), "data" => $data);
        return $postfields;
    }

    public static function getHmac(array $dataArray,$key) {
        if ( is_array($dataArray) ) {

            $data = implode("", $dataArray);
        } else {

            $data = strval($dataArray);
        }
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }


    /**
     * @使用特定function对数组中所有元素做处理
     * @&$array 要处理的字符串
     * @$function 要执行的函数
     * @$apply_to_keys_also 是否也应用到key上
     *
     */
    public static function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
    }

    /**
     *
     * @将数组转换为JSON字符串（兼容中文）
     * @$array 要转换的数组
     * @return string 转换得到的json字符串
     *
     */
    public static function cn_json_encode($array) {
        $array = self::cn_url_encode($array);
        $json = json_encode($array);
        return urldecode($json);
    }

    /**
     *
     * @将数组统一进行urlencode（兼容中文）
     * @$array 要转换的数组
     * @return array 转换后的数组
     *
     */
    public static function cn_url_encode($array) {
        self::arrayRecursive($array, "urlencode", true);
        return $array;
    }


    /**
     * @取得aes加密
     * @$dataArray 明文字符串
     * @$key 密钥
     * @return string
     *
     */
    public static function getAes($data, $aesKey) {

        //print_r(mcrypt_list_algorithms());
        //print_r(mcrypt_list_modes());
        $aes = new CryptAES();

        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $encrypted = strtoupper($aes->encrypt($data));

        return $encrypted;

    }

    /**
     * @取得aes解密
     * @$dataArray 密文字符串
     * @$key 密钥
     * @return string
     *
     */
    public static function getDeAes($data, $aesKey) {

        $aes = new CryptAES();
        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $text = $aes->decrypt($data);
        return $text;
    }

    /**
     * @检查一个数组是否是有效的
     * @$checkArray 数组
     * @$arrayKey 数组索引
     * @return boolean
     * 如果$arrayKey传参，则不止检查数组，
     * 而且检查索引是否存在于数组中。
     *
     */
    public static function isViaArray($checkArray, $arrayKey = null) {

        if ( !$checkArray || empty($checkArray) ) {

            return false;
        }

        if ( !$arrayKey ) {

            return true;
        }

        return array_key_exists($arrayKey, $checkArray);
    }
}