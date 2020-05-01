<?php
/**
 * Created by PhpStorm.
 * User: hiliq
 * Date: 2019/3/9
 * Time: 11:47
 */

namespace app\index\controller;


class Rank extends Base
{
    protected $bookService;

    protected function initialize()
    {
        parent::initialize();
        $this->bookService = app('bookService');
    }

    public function index()
    {
        $hot_books = cache('hot_books');
        if (!$hot_books) {
            $hot_books = $this->bookService->getHotBooks(10);
            cache('hot_books', $hot_books, null, 'redis');
        }

        $newest = cache('newest_homepage');
        if (!$newest) {
            $newest = $this->bookService->getBooks('last_time', '1=1', 10);
            cache('newest_homepage', $newest, null, 'redis');
        }
        $ends = cache('ends_homepage');
        if (!$ends) {
            $ends = $this->bookService->getBooks('create_time', [['end', '=', '1']], 10);
            cache('ends_homepage', $ends, null, 'redis');
        }

        $tops = cache('tops_rank');
        if (!$tops) {
            $tops = $this->bookService->getBooks($this->end_point, 'last_time', [['is_top', '=', '1']], 30);
            cache('tops_rank', $tops, null, 'redis');
        }

        $most_charged = cache('most_charged');
        if (!$most_charged) {
            $arr = $this->bookService->getMostChargedBook();
            if (count($arr) > 0) {
                foreach ($arr as $item) {
                    $most_charged[] = $item['book'];
                }
            } else {
                $arr = [];
            }
            cache('most_charged', $most_charged, null, 'redis');
        }

        $this->assign([
            'list' => [
                ['keyword' => $newest, 'title' => '新书榜', 'id' => 'newest'],
                ['keyword' => $hot_books, 'title' => '人气榜', 'id' => 'hot'],
                ['keyword' => $ends, 'title' => '完结榜', 'id' => 'ends'],
                ['keyword' => $tops, 'title' => '推荐榜', 'id' => 'top']
            ],
            'header_title' => '排行榜'
        ]);

        return view($this->tpl);
    }
}