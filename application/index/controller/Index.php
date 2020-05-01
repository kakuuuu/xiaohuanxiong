<?php

namespace app\index\controller;

use app\model\Author;
use app\model\Banner;
use app\model\RedisHelper;

class Index extends Base
{
    protected $bookService;

    protected function initialize()
    {
        parent::initialize();
        $this->bookService = app('bookService');
    }

    public function index()
    {
        $pid = input('pid');
        if ($pid) { //如果有推广pid
            cookie('xwx_promotion', $pid); //将pid写入cookie
        }
        $banners = cache('bannersHomepage');
        if (!$banners) {
            $banners = Banner::with('book')->where('banner_order','>', 0)->order('banner_order','desc')->select();
            cache('bannersHomepage',$banners, null, 'redis');
        }
        $hot_books = cache('hotBooks');
        if (!$hot_books) {
            $hot_books = $this->bookService->getHotBooks();
            cache('hotBooks', $hot_books, null, 'redis');
        }

        $newest = cache('newestHomepage');
        if (!$newest) {
            $newest = $this->bookService->getBooks('last_time', '1=1', 14);
            cache('newestHomepage', $newest, null, 'redis');
        }

        $ends = cache('endsHomepage');
        if (!$ends) {
            $ends = $this->bookService->getBooks('create_time', [['end', '=', '1']], 14);
            cache('endsHomepage', $ends, null, 'redis');
        }

        $tops = cache('topsHomepage');
        if (!$tops) {
            $tops = $this->bookService->getBooks('last_time', [['is_top', '=', '1']], 30);
            cache('topsHomepage', $tops, null, 'redis');
        }

//        $most_charged = cache('mostCharged');
//        if (!$most_charged) {
//            $arr = $this->bookService->getMostChargedBook();
//            if (count($arr) > 0) {
//                foreach ($arr as $item) {
//                    $most_charged[] = $item['book'];
//                }
//            } else {
//                $arr = [];
//            }
//            cache('mostCharged', $most_charged, null, 'redis');
//        }

        $tags = cache('tags');
        if (!$tags) {
            $tags = \app\model\Tags::all();
            cache('tags', $tags, null, 'redis');
        }

        $catelist = array(); //分类漫画数组
        $cateItem = array();
        foreach ($tags as $tag) {
            $books = cache('booksFilterByTag:'.$tag);
            if (!$books) {
                $books = $this->bookService->getByTag($tag->tag_name);
                cache('booksFilterByTag:'.$tag, $books, null, 'redis');
            }
            $cateItem['books'] = $books->toArray();
            $cateItem['tag'] = ['id' => $tag->id, 'tag_name' => $tag->tag_name];
            $catelist[] = $cateItem;
        }

        $this->assign([
            'banners' => $banners,
            'banners_count' => count($banners),
            'newest' => $newest,
            'hot' => $hot_books,
            'ends' => $ends,
            'tops' => $tops,
            'tags' => $tags,
            'catelist' => $catelist
        ]);

        return view($this->tpl);
    }
 
    public function search()
    {
        $keyword = input('keyword');
        $redis = RedisHelper::GetInstance();
        $redis->zIncrBy($this->redis_prefix . 'hot_search', 1, $keyword); //搜索词写入redis热搜
        $hot_search_json = $redis->zRevRange($this->redis_prefix . 'hot_search', 0, 4, true);
        $hot_search = array();
        foreach ($hot_search_json as $k => $v) {
            $hot_search[] = $k;
        }
        $books = cache('searchresult:' . $keyword);
        if (!$books) {
            $num = config('page.search_result_pc');
            if ($this->request->isMobile()) {
                $num = config('page.search_result_mobile');
            }
            $books = $this->bookService->search($keyword, $num);
            cache('searchresult:' . $keyword, $books, null, 'redis');
        }
        foreach ($books as &$book) {
            $author = Author::get($book['author_id']);
            $book['author'] = $author;
            if (empty($book['cover_url'])) {
                $book['cover_url'] = $this->img_site.'/static/upload/book/'.$book['id'].'/cover.jpg';
            }
            if ($this->end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
        }
        $this->assign([
            'books' => $books,
            'count' => count($books),
            'hot_search' => $hot_search,
            'keyword' => $keyword
        ]);
        return view($this->tpl);
    }

    public function bookshelf()
    {
        $this->assign('header_title', '书架');
        return view($this->tpl);
    }
}

