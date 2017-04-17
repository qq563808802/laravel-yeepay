<?php
/**
 * User: wangzd
 * Email: wangzhoudong@liwejia.com
 * Date: 2017/4/7
 * Time: 18:42
 */
namespace YeePay\Ledger;
use YeePay\YeePay\Exceptions\Exception;
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
     * 分账接口
     */
    const DIVIDE_URL = '/divide';
    static $divideNeedRequestHmac =array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideMustFillRequest =  array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideRequest = array(0 => "requestid", 1 => "orderrequestid", 2 => "divideinfo");
    static $divideNeedResponseHmac =  array(0 => "customernumber", 1 => "requestid", 2 => "code");

    /**
     * 余额查询
     * @param array $params
     * @return \Illuminate\Support\Collection
     *
     */
    const BALANCE_URL = '/queryBalance';
    static $balanceNeedRequestHmac =  array(0 => "ledgerno");
    static $balanceMustFillRequest = array();
    static $balanceRequest =  array(0 => "ledgerno");
    static $balanceNeedResponseHmac =  array(0 => "customernumber", 1 => "code", 2 => "balance", 3 => "ledgerbalance");


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
     * 分账接口
     * @param $requestid
     * @param $orderrequestid
     * @param $ledgerno
     * @param $amount
     */

    public function divide($requestid,$orderrequestid,$ledgerno,$amount) {
        $this->setUrl(self::DIVIDE_URL);
        $this->setNeedRequest(self::$divideRequest);
        $this->setNeedRequestHmac(self::$divideNeedRequestHmac);
        $this->setNeedResponseHmac(self::$divideNeedResponseHmac);
        $this->setMustFillRequest(self::$divideMustFillRequest);
        $this->setPost(['requestid'=>$requestid,'orderrequestid'=>$orderrequestid,'divideinfo'=>"$ledgerno:AMOUNT{$amount}"]);
        $response = $this->send();
        return $response;
    }

    private function balanceSource($ledgerno) {
            $this->setUrl(self::BALANCE_URL);
            $this->setNeedRequest(self::$balanceRequest);
            $this->setNeedRequestHmac(self::$balanceNeedRequestHmac);
            $this->setNeedResponseHmac(self::$balanceNeedResponseHmac);
            $this->setMustFillRequest(self::$balanceMustFillRequest);
            $this->setPost(['ledgerno'=>$ledgerno]);
            return $response = $this->send();


    }

    public function balanceMaster() {
        $data = $this->balanceSource('');
        return isset($data['balance']) ? $data['balance'] : "";
    }

    /**
     * 查询余额
     * @param $ledgerno
     * @return \Illuminate\Support\Collection
     * 查询账号余额
     */
    public function balance($ledgerno) {
        $data = $this->balanceSource($ledgerno);
        if(!isset($data['ledgerbalance'])) {
            return '';
        }
        $amount = explode(':',$data['ledgerbalance']);
        return $amount[1];
    }
}