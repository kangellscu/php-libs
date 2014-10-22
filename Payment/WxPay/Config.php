<?php
// Payment/WxPay/Config.php
namespace Kangell\Libs\Payment\WxPay;


class Config
{
    // 支付币种，暂时只支持人民币
    const FEE_TYPE_RMB = "1";


    /****************** 微信支付所需要的各类 Id 和 Key ******************/
    // 开放平台查看，标识申请的应用
    public static $appId = "";

    /* 支付请求中用于加密的密钥Key，可验证商户唯一身份，在微信发送的邮件
     * 中查看 PaySignKey 的值
     */
    public static $appKey = "";

    // 第三方用户唯一凭证密钥，用其换取access_token. 在开放平台中查看
    public static $appSecret = "";

    // 注册时分配的财付通商户号。在财付通发送的邮件中查看
    public static $partnerId = "";

    // 财付通商户权限密钥Key。在财付通发送的邮件中查看
    public static $partnerKey = "";


    // 支付成功后，微信服务器通知支付结果的URL
    public static $notifyUrl = "";

    public static $charset = "UTF-8";


    public static $url = array(
        // native pay
        "nativePayUrl" => "weixin://wxpay/bizpayurl",

        // app
        "appPrePayUrl" => "https://api.weixin.qq.com/pay/genprepay",

        // Common API
        "apiTokenUrl" => "",
    );
}


/* End of file */
