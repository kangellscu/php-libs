<?php
// Payment/WxPay/WxPay.php
namespace Kangell\Libs\Payment\WxPay;

use Kangell\Libs\WrapCurl;

class WxPay
{
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
            "appId" => Config::$appId,
            "timestamp" => time(),
            "noncestr" => Util::genRandomStr(),
            "productid" => $productId,
            "appkey" => Config::$appKey,
        );

        $sign = Util::genSignStr($params);
        $params['sign'] = $sign;

        $paymentUrl = Config::$url['nativePayUrl'] . "?" . http_build_query($params);

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
    public function getAccessToken() {

    }


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
