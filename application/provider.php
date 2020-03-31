<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 容器Provider定义文件
return [
    'adminService' => \app\service\AdminService::class,
    'authorService' => \app\service\AuthorService::class,
    'bookService' => \app\service\BookService::class,
    'chapterService' => \app\service\ChapterService::class,
    'financeService' => \app\service\FinanceService::class,
    'photoService' => \app\service\PhotoService::class,
    'promotionService' => \app\service\PromotionService::class,
    'tagsService' => \app\service\TagsService::class,
    'userService' => \app\service\UserService::class
];
