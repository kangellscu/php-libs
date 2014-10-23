<?php
// Payment/WxPay/WxPay.php
namespace Kangell\Libs\Payment\WxPay;

use Kangell\Libs\WrapCurl;

class WxPay
{
    private $appId;
    private $appKey;
    private $appSecret;

    private $packageParams;


    public function __construct(
        $appId=NULL, $appKey=NULL, $appSecret=NULL, $partnerId=NULL, $notifyUrl=NULL
    ) {
        $this->appId = $appId ?: Config::$APP_ID;
        $this->appKey = $appKey ?: Config::$APP_KEY;
        $this->appSecret = $appSecret ?: Config::$APP_SECRET;
        $this->partnerId = $partnerId ?: Config::$PARTNER_ID;
        $this->notifyUrl = $notifyUrl ?: Config::$NOTIFY_URL;

        $this->packageParams = array();
    }


    /*********** Native pay begin ***********/

    /**
     * @Method("GET")
     *
     * 由商户发起
     * 
     * @param string $productId 商户定义并维护商品id, 该id与一张订单等价
     *              微信后台根据该id调用package回调接口获取支付所需信息
     *
     * @return string $nativePayUrl
     */
    public function nativePayUrl($productId) {
        $params = array(
            "appId" => $this->appId,
            "timestamp" => time(),
            "noncestr" => Util::genRandomStr(),
            "productid" => $productId,
            "appkey" => $this->appKey,
        );

        $sign = Util::genSignStr($params);
        $params['sign'] = $sign;

        $paymentUrl = Config::$URLS['nativePayUrl'] . "?" . http_build_query($params);

        return $paymentUrl;
    }


    /**
     * 请求nativePayUrl时，WX支付服务器会请求公众号注册时填写的回调URL, 
     * 获取用于支付的Package
     *
     * 由微信发起
     *
     * @Method("POST")
     */
    public function nativePackageCallback() {

    }

    /*********** Native pay end *************/

    public function setPackageParams(array $params) {
        $this->packageParams = array();
        foreach ($params as $key => $value) {
            $this->packageParams[$key] = $value ?: NULL;
        }
    }


    /*********** App pay begin **************/

    /**
     * app获取支付参数用于app内支付
     *
     * 由app发起
     *
     */
    public function getPayParm($accessToken) {
        $prePayId = $this->genPrePay($accessToken);

        // TODO resign prepayId
        $reSignData = array();
        $payInfo = "";

        return $payInfo;
    }

    /**
     * 生成预支付订单
     * 商户使用access_token，生成预付费订单package，提交到WX，获取prepayId
     * 签名后返回给客户端支付参数
     *
     * 由商户发起
     *
     * @Method("POST")
     *
     * 
     * @return string $payInfo
     */
    public function genPrePay($accessToken) {
        $package = Util::genPackage($this->packageParams, $this->partnerKey);
        $payParam = array(
            "appid" => $this->appId,
            "appkey" => $this->appKey,
            "noncestr" => Util::genRandomStr(),
            "package" => $package,
            "timestamp" => (string) time(),
            "traceid" => $Config::$APP_PARTNER . "_" . $this->packageParams['out_trade_no'],
        );
        $paySign = Util::genSignStr($payParam);
        $payParam["app_signature"] = $paySign;
        $payParam["sign_method"] = "sha1";

        // Post to prePayUrl and get prepayId
        $prePayUrl = Config::$url["appPrePayUrl"];
        $res = WrapCurl::post($prePayUrl, $paySign);

        $resData = json_decode($res, true);
        if ( ! $resData) {
            throw \Exception("fatal error: call prepay failed");
        }
        if ($resData['errcode'] != "0") {
            throw \Exception("fatal error: prepay failed; errmsg: " . $resData["errmsg"]);
        }

        return array($resData["prepayid"], NULL);
    }


    /**
     * 调用微信API需要提供access_token，由微信验证请求的合法性
     * 每天获取次数有限制，正常情况下access_token有效期为7200s，
     * 重复获取将导致上次获取的access_token失效
     * 
     * 由商户发起
     * 
     * @Method("GET")
     * 
     * @return array $accessToken Format as below:
     *      array(
     *          "code" => 0,        // 0 stand for success
     *          "accessToken" => "abcdse",  // 最大长度为512 bytes
     *          "expiresIn" => 7200,        // 过期时间
     *      )
     *      or 
     *      array(
     *          "code" => 40013,
     *          "errmsg" => "invalid appid",
     *      )
     */
    public function getAccessToken() {
        $querys = array(
            "grant_type" => "client_credential",
            "appid" => $this->appId,
            "secret" => $this->appSecret,
        );

        $res = WrapCurl::get(Config::$url["apiTokenUrl"], $querys);
        $resData = json_decode($res, true);

        if ( ! $resData) {
            return array(
                "code" => WrapCurl::ERROR_REQUEST,
                "errmsg" => "系统繁忙，请稍候重试",
            );
        } else if ($resData["errcode"]) {
            return array(
                "code" => $resData["errcode"],
                "errmsg" => $resData["errmsg"],
            );
        } else {
            return array(
                "code" => WrapCurl::ERROR_OK,
                "accessToken" => $resData["access_token"],
                "expiresIn" => $resData["expires_in"],
            );
        }
    }

    /*********** App pay end ****************/

    
    /**
     * 用户成功完成支付后，WX后台(POST)商户服务器(notify_url), 告知支付结果
     * 补单机制:
     *      商户返回的response不是success或者timeout, 微信认为通知失败，会
     *      通过一定策略(如30分钟共8次)定期重新发起通知，提高成功率. 但微信
     *      不保证通知最终能成功.
     * 当前补单机制的时间间隔:
     *      8s, 10s, 10s, 30s, 30s, 60s, 120s, 360s, 1000s
     *
     * 由微信发起
     *
     * @Method("POST")
     */
    public function paymentNotify() {

    }

    /*********** Api begin ******************/
    
    /**
     * 调用API需要使用access_token
     *
     * 由商户发起
     *
     * @Method("GET")
     */
    /*
    public function getAccessToken() {

    }
     */


    /**
     * 发货通知
     * 
     * 商户在收到最终的支付通知后，调用发货通知API告知微信后台该订单的发货状态
     * 发货时间限制：虚拟、服务类24小时内，实物类72小时内
     * 需要在收到支付通知后，按时发货，并使用发货通知接口将相关信息同步到微信
     * 后台。如果微信后台在规定时间内没有收到，将视为发货超时处理.
     *
     * 由商户发起
     */
    public function deliverNotify() {

    }


    /**
     * 查询订单的详细支付状态
     * 由于微信后台通知商户支付结果(paymentNotify)由于某种原因可能失效，通过
     * 订单查询可以获取详细支付状态
     *
     * 由商户发起
     */
    public function orderQuery() {

    }


    /**
     * 告警通知
     *
     * 告警类型：延迟发货、调用失败、通知失败等
     * 告警地址需要在公众号申请支付时填写
     *
     * 由微信发起
     */
    public function alarmNOtify() {

    }

    /*********** Api end ********************/
}


/* End of file */
