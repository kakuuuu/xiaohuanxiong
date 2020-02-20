<?php


namespace app\api\controller;


use app\pay\iPay;

class Huiju implements iPay
{
    public function submit($order_id, $money, $pay_type, $pay_code)
    {
        $mch_id = trim(config('payment.huiju.appid'));//这里改成支付ID
        $mch_key = trim(config('payment.huiju.appkey')); //这是您的通讯密钥

        $url="http://pay.m7b.cn/Pay_Index.html";

        $arr['pay_memberid']=$mch_id;
        $arr['pay_orderid']=$order_id;
        $arr['pay_applydate']=date('Y-m-d H:i:s');
        $arr['pay_bankcode']= $pay_code;
        $arr['pay_notifyurl']= config('site.url')  . '/Huijunotify';
        $arr['pay_callbackurl']= config('site.url')  . '/feedback';
        $arr['pay_amount']=$money;
        $arr['pay_md5sign']=strtoupper($this->md5Sign($arr, $mch_key, '&key='));
        $arr['pay_productname']='prod';

        $html=$this->createForm($url,$arr);
        echo $html;
    }

    private function md5Sign($data, $key, $connect='',$is_md5 = true)
    {
        ksort($data);
        $string = '';
        foreach( $data as $k => $vo ){
            if($vo !== '')
                $string .=  $k . '=' . $vo . '&' ;
        }
        $string = rtrim($string, '&');
        $result = $string . $connect . $key;

        return $is_md5 ? md5($result) : $result;

    }


    private function createForm($url, $data, $enctype = false, $method = 'post')
    {
        $str = '<!doctype html>
            <html>
                <head>
                    <meta charset="utf8">
                    <title>正在跳转付款页</title>
                </head>
                <body onLoad="document.pay.submit()">
                <form method="'.$method.'" action="' . $url . '" name="pay"';

        if ($enctype) {
            $str .= '  enctype="'.$enctype.'">';
        }  else {
            $str .= '>';
        }

        foreach ($data as $k => $vo) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $vo . '">';
        }

        $str .= '</form>
                </body>
            </html>';
        return $str;
    }
}