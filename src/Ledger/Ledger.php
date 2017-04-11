<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/4/7
 * Time: 18:42
 */
namespace YeePay\Ledger;
use YeePay\YeePay\Http\ApiRequest;

class Ledger extends ApiRequest
{

    /**
     * 子账号注册配置
     */
    const REGISTER_URL = '/register';
    static $regNeedRequestHmac = ["requestid", "bindmobile", "customertype", "signedname", "linkman", "idcard", "businesslicence", "legalperson", "minsettleamount", "riskreserveday", "bankaccountnumber", "bankname", "accountname", "bankaccounttype", "bankprovince", "bankcity"];
    static $regNeedResponseHmac = ["customernumber", "requestid", "code", "ledgerno"];
    static $regRequest = ["requestid", "bindmobile", "customertype", "signedname", "linkman", "idcard", "businesslicence", "legalperson", "minsettleamount", "riskreserveday", "bankaccountnumber", "bankname", "accountname", "bankaccounttype", "bankprovince", "bankcity", "deposit", "email"];
    /**
     * 子账号修改配置
     */
    const EDIT_URL = '/modifyRequest';
    static $editNeedRequestHmac = ["requestid", "ledgerno", "bankaccountnumber", "bankname", "accountname", "bankaccounttype", "bankprovince", "bankcity", "minsettleamount", "riskreserveday", "manualsettle", "callbackurl"];
    static $editMustFillRequest =["requestid", "bankaccountnumber","bankname", "accountname", "bankaccounttype", "bankprovince","bankcity", "minsettleamount", "riskreserveday","callbackurl", "bindmobile"];
    static $editRequest = ["requestid",  "ledgerno","bankaccountnumber",  "bankname","accountname",  "bankaccounttype","bankprovince","bankcity", "minsettleamount","riskreserveday","callbackurl","bindmobile"];
    static $editNeedResponseHmac = ["customernumber", "requestid","code"];


    /**
     * 分账方资质上传接口配置
     */
    const  UPLOAD_URL = '/uploadLedgerQualifications';
    static $uploadNeedRequestHmac = array(0 => "ledgerno", 1 => "filetype");
    static $uploadMustFillRequest =array(0 => "ledgerno", 1 => "filetype");
    static $uploadRequest = array(0 => "ledgerno", 1 => "filetype");
    static $uploadNeedResponseHmac =array(0 => "customernumber", 1 => "ledgerno", 2 => "code", 3 => "filetype");


    /**
     * 转账接口
     */
    const TRANSFER_URL = '/transfer';
    static $transferNeedRequestHmac =array(0 => "requestid");
    static $transferMustFillRequest = array(0 => "requestid");
    static $transferRequest = array(0 => "requestid");
    static $transferNeedResponseHmac =  array(0 => "customernumber", 1 => "requestid", 2 => "code", 3 => "ledgerno", 4 => "amount", 5 => "status");

    /**
     * 分账接口
     */
    const DIVIDE_URL = '/divide';
    static $divideNeedRequestHmac =array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideMustFillRequest =  array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideRequest = array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideNeedResponseHmac =  array(0 => "customernumber", 1 => "requestid", 2 => "code");




    public function register(array $params)
    {
        $this->setUrl(self::REGISTER_URL);
        $this->setNeedRequest(self::$regRequest);
        $this->setNeedRequestHmac(self::$regNeedRequestHmac);
        $this->setNeedResponseHmac(self::$regNeedResponseHmac);
        $this->setPost($params);

        $response = $this->send();
        return $response;
    }


    public function edit(array $params){
        $this->setUrl(self::EDIT_URL);
        $this->setNeedRequest(self::$editRequest);
        $this->setNeedRequestHmac(self::$editNeedRequestHmac);
        $this->setNeedResponseHmac(self::$editNeedResponseHmac);
        $this->setMustFillRequest(self::$editMustFillRequest);
        $this->setPost($params);
        $response = $this->send();
        return $response;
    }

    /**
     * 分账放上传资质
     * @param $ledgerno
     * @param $fileType
     * @param $file
     */
    public function upload($ledgerno,$fileType,$file){
        $this->setUrl(self::UPLOAD_URL);
        $this->setNeedRequest(self::$uploadRequest);
        $this->setNeedRequestHmac(self::$uploadNeedRequestHmac);
        $this->setNeedResponseHmac(self::$uploadNeedResponseHmac);
        $this->setPost(['ledgerno'=>$ledgerno,'filetype'=>$fileType]);
        $this->setFile($file);
        $response = $this->send();
        return $response;
    }

    /**
     * 转账接口
     * @param $ledgerno
     * @param $amount
     */
    public function transfer($ledgerno,$amount) {
        $this->setUrl(self::EDIT_URL);
        $this->setNeedRequest(self::$transferRequest);
        $this->setNeedRequestHmac(self::$transferNeedRequestHmac);
        $this->setNeedResponseHmac(self::$transferNeedResponseHmac);
        $this->setMustFillRequest(self::$transferMustFillRequest);
        $this->setPost(['requestid'=>date("YmdHis") . rand(0000,9999),'ledgerno'=>$ledgerno,'amount'=>$amount]);
        $response = $this->send();
        return $response;
    }

    /**
     * 分账接口
     * @param $requestid
     * @param $orderrequestid
     * @param $ledgerno
     * @param $amount
     */

    public function divide($requestid,$orderrequestid,$ledgerno,$amount) {
        $this->setUrl(self::EDIT_URL);
        $this->setNeedRequest(self::$divideRequest);
        $this->setNeedRequestHmac(self::$divideNeedRequestHmac);
        $this->setNeedResponseHmac(self::$divideNeedResponseHmac);
        $this->setMustFillRequest(self::$divideMustFillRequest);
        $this->setPost(['requestid'=>$requestid,'orderrequestid'=>$orderrequestid,'divideinfo'=>"$ledgerno:AMOUNT{$amount}"]);
        $response = $this->send();
        return $response;
    }
}