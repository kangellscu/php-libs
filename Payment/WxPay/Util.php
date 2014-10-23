<?php
// Payment/WxPay/Util.php
namespace Kangell\Libs\Payment;


class Util
{

    /**
     * gen random string
     *
     * @param string $length The length of random string
     *
     * @return string $str The random string
     */
    public static function genRandomStr($length=32) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        return $str;
    }


    /**
     * 生成支付签名
     *
     * @param array $signData 需要参数如下:
     *      appid, appkey, noncestr, package, timestamp, traceid
     */
    public static function genSignStr(array $signData) {
        ksort($signData);
        $signString = self::buildQueryString($signData);
        return sha1($signString);
    }


    /**
     * 创建package
     *
     * @param array $packageParms
     * @param string $partnerKey 财付通partnerKey
     *
     * @return string $package
     */
    public static function genPackage(array $packageParms, $partnerKey) {
        self::checkPackageParams($packageParms);    

        ksort($packageParms);
        $queryString = self::buildQueryString($packageParms, true);
        $signQueryString = self::buildQueryString($packageParms, false);
        $md5SignString = self::md5Sign($signQueryString, $partnerKey);

        return $queryString . "&sign=" . $md5SignString;
    }


    public static function checkPackageParams(array $packageParms) {
        $requireKeys = array(
            "bank_type", "body", "attach", "partner", "out_trade_no", "total_fee",
            "fee_type", "notify_url", "spbill_create_ip", "input_charset"
        );
        foreach ($requireKeys as $key) {
            if ( ! isset($packageParms[$key]) || ! $packageParms[$key]) {
                throw \Exception("fatal error: gen package error, $key must be supply");
            }
        }
    }


    public static function buildQueryString($params, $urlEncode=false) {
        $queryStringData = array();
        foreach ($params as $key => $value) {
            if ($key == "sign" || empty($value) || $value == "null") {
                continue;
            }
            $queryStringData[$key] = $urlEncode ? urlencode($value) : $value;
        }

        return implode("&", $queryStringData);
    }


    public static function md5Sign($str, $key) {
        if (empty($str) || empty($key)) {
            throw \Exception("财付通签名/内容不能为空");
        }

        $signStr = $str . "&key=" . $key;

        return strtoupper(md5($signStr));
    }

}
