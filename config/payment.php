<?php
return [
    'pay' => [ //幻兮支付，官网地址:https://www.zhapay.com/
        'appid' => '',
        'appkey' => '',
        'channel' => [
            ['type' => 1, 'code' => 2, 'img' => 'alipay', 'title' => '支付宝'], //type是小浣熊默认的，1支付宝2QQ钱包3微信支付
            ['type' => 3, 'code' => 1, 'img' => 'weixin', 'title' => '微信支付'] //code是幻兮定义的pay_type_id，具体看幻兮文档
        ]
    ],
    'paypal' => [
        'clientId' => '',
        'clientSecret' => '',
        'channel' => [
            ['type' => 'paypal', 'code' => 5, 'img' => 'paypal', 'title' => '贝宝支付'],
        ]
    ],
    'kami' => [
        'url' => '' //卡密地址
    ],
    'vip' => [  //设置vip天数及相应的价格
        ['month' => 1, 'price' => 5],
        ['month' => 6, 'price' => 100],
        ['month' => 12, 'price' => 400]
    ],
    'money' => [1, 5, 10, 30, 50], //设置支付金额
    'promotional_rewards_rate' => 0.1, //设置充值提成比例，必须是小数
    'reg_rewards' => 1, //注册奖励金额，单位是元
    'mobile_bind_rewards' => 0 //绑定手机奖励金额，单位是元
];