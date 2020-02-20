<?php


namespace app\pay;


interface iPay
{
    public function submit($order_id, $money, $pay_type,$pay_code);
}