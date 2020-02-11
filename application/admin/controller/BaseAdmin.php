<?php
/**
 * Created by PhpStorm.
 * User: zhangxiang
 * Date: 2018/10/19
 * Time: 下午6:04
 */

namespace app\admin\controller;


use think\App;
use think\Controller;
use think\facade\Session;
use think\facade\View;

class BaseAdmin extends Controller
{
    protected $prefix;
    protected $end_point;
    protected function initialize()
    {
        $this->checkAuth();

    }

    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->prefix = config('database.prefix');
        $img_site = config('site.img_site');
        $this->end_point = config('seo.book_end_point');
        View::share([
            'img_site' => $img_site,
        ]);
    }

    protected function checkAuth(){
        if (!Session::has('xwx_admin')) {
            $this->redirect('admin/login/index');
        }
    }
}