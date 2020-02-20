<?php
/**
 * Created by PhpStorm.
 * User: hiliq
 * Date: 2019/3/25
 * Time: 17:57
 */

namespace app\api\controller;

use app\model\Admin;
use app\model\ChargeCode;
use app\model\RedisHelper;
use app\model\VipCode;
use think\facade\App;
use think\facade\Cache;
use think\Controller;
use app\model\Clicks;

class Common extends Controller
{
    public function clearcache()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }
        Cache::clear('redis');
        $rootPath = App::getRootPath();
        delete_dir_file($rootPath . '/runtime/cache/') && delete_dir_file($rootPath . '/runtime/temp/');
        return json(['success' => 1, 'msg' => '清理成功']);
    }

    public function sycnclicks()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }
        $day = input('date');
        if (empty($day)) {
            $day = date("Y-m-d", strtotime("-1 day"));
        }
        $redis = RedisHelper::GetInstance();
        $hots = $redis->zRevRange('click:' . $day, 0, 10, true);
        foreach ($hots as $k => $v) {
            $clicks = new Clicks();
            $clicks->book_id = $k;
            $clicks->clicks = $v;
            $clicks->cdate = $day;
            $result = $clicks->save();
            if ($result) {
                $redis->zRem('click:'.$day, $k); //同步到数据库之后，删除redis中的这个日期的这本漫画的点击数
            }
        }
        return json(['success' => 1, 'msg' => '同步完成']);
    }

    public function genvipcode()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }
        $num = (int)config('kami.vipcode.num'); //产生多少个
        $day = config('kami.vipcode.day');

        $result = $this->validate(
            [
                'num' => $num,
                'day' => $day,
            ],
            'app\admin\validate\Vipcode');
        if (true !== $result) {
            return json(['success' => 0, 'msg' => '后台配置错误']);
        }

        $salt = config('site.' . config('kami.salt'));//根据配置，获取盐的方式
        for ($i = 1; $i <= $num; $i++) {
            $code = substr(md5($salt . time()), 8, 16);
            VipCode::create([
                'code' => $code,
                'add_day' => $day
            ]);
            sleep(1);
        }
        return json(['success' => 1, 'msg' => '成功生成vip码']);
    }

    public function genchargecode()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }
        $num = (int)config('kami.chargecode.num'); //产生多少个
        $money = config('kami.chargecode.money');

        $result = $this->validate(
            [
                'num' => $num,
                'money' => $money,
            ],
            'app\admin\validate\Chargecode');
        if (true !== $result) {
            return json(['success' => 0, 'msg' => '后台配置错误']);
        }

        $salt = config('site.' . config('kami.salt'));//根据配置，获取盐的方式
        for ($i = 1; $i <= $num; $i++) {
            $code = substr(md5($salt . time()), 8, 16);
            ChargeCode::create([
                'code' => $code,
                'money' => $money
            ]);
            sleep(1);
        }
        return json(['success' => 1, 'msg' => '成功生成充值码']);
    }

    public function resetpwd()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }
        $salt = input('salt');
        if (empty($salt) || is_null($salt)) {
            return json(['success' => 0, 'msg' => '密码盐错误']);
        }
        if ($salt != config('site.salt')) {
            return json(['success' => 0, 'msg' => '密码盐错误']);
        }
        $username = input('username');
        if (empty($username) || is_null($username)) {
            return json(['success' => 0, 'msg' => '用户名不能为空']);
        }
        $pwd = input('password');
        if (empty($pwd) || is_null($pwd)) {
            return json(['success' => 0, 'msg' => '密码不能为空']);
        }
        Admin::create([
            'username' => $username,
            'password' => md5(trim($pwd).config('site.salt'))
        ]);
        return json(['success' => 1, 'msg' => '新管理员创建成功']);
    }


}