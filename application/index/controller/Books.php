<?php

namespace app\index\controller;

use app\model\Book;
use app\model\Comments;
use app\model\RedisHelper;
use app\model\User;
use app\model\UserBook;
use think\Db;
use think\Request;

class Books extends Base
{
    protected $bookService;

    public function initialize()
    {
        parent::initialize();
        cookie('nav_switch', 'booklist'); //设置导航菜单active
        $this->bookService = app('bookService');
    }

    public function index()
    {
        $id = input('id');
        $book = cache('book:' . $id);
        $tags = cache('tags:book:' . $id);
        if ($book == false) {
            $book_end_point = config('seo.book_end_point');
            if ($book_end_point == 'id') {
                $book = Book::with(['chapters' => function ($query) {
                    $query->order('chapter_order');
                }])->find($id);
            } else {
                $book = Book::with(['chapters' => function ($query) {
                    $query->order('chapter_order');
                }])->where('unique_id','=', $id)->find();
            }
            if (empty($book['cover_url'])) {
                $book['cover_url'] = $this->img_site.'/static/upload/book/'.$book['id'].'/cover.jpg';
            }
            $tags = [];
            if (!empty($book->tags) || is_null($book->tags)) {
                $tags = explode('|', $book->tags);
            }
            cache('book:' . $id, $book, null, 'redis');
            cache('tags:book:' . $id, $tags, null, 'redis');
        }
        $redis = RedisHelper::GetInstance();
        $day = date("Y-m-d", time());
        //以当前日期为键，增加点击数
        $redis->zIncrBy('click:' . $day, 1, $book->id);

        $hot_books = cache('hotBooks'); //总点击
        if (!$hot_books) {
            $hot_books = $this->bookService->getHotBooks();
            cache('hotBooks', $hot_books, null, 'redis');
        }

        $hot_books_month = cache('hotBooksMonth'); //月点击
        if (!$hot_books_month) {
            $date = date('Y-m-d', strtotime('-1 mouth'));
            $hot_books_month = $this->bookService->getHotBooks($date);
            cache('hotBooksMonth', $hot_books, null, 'redis');
        }

        $hot_books_day = cache('hotBooksDay'); //日点击
        if (!$hot_books_day) {
            $date = date('Y-m-d', strtotime('-1 day'));
            $hot_books_day = $this->bookService->getHotBooks($date);
            cache('hotBooksDay', $hot_books, null, 'redis');
        }

        $recommand = cache('randBooks:'.$book->tags);
        if (!$recommand) {
            $recommand = $this->bookService->getRecommand($book->tags);
            cache('randBooks:'.$book->tags, $recommand, null, 'redis');
        }

        $updates = cache('updateBooks');
        if (!$updates) {
            $updates = $this->bookService->getBooks('last_time', [], 10);
            cache('updateBooks', $updates, null, 'redis');
        }

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT id FROM ' . $this->prefix . 'chapter WHERE book_id = ' . $book->id . ' ORDER BY id LIMIT 1');
            $start = $db ? $db[0]['id'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        $comments = $this->getComments($book->id);

        $isfavor = 0;
        if (!is_null($this->uid)) {
            $where[] = ['user_id', '=', $this->uid];
            $where[] = ['book_id', '=', $book->id];
            $userfavor = UserBook::where($where)->find();
            if (!is_null($userfavor)) { //未收藏本漫画
                $isfavor = 1;
            }
        }

        $start_pay = cache('maxChapterOrder:' . $book->id);
        if (!$start_pay) {
            if ($book->start_pay >= 0) {
                $start_pay = $book->start_pay; //如果是正序，则开始付费章节就是设置的
            } else { //如果是倒序付费设置
                $abs = abs($book->start_pay) - 1; //取得倒序的绝对值，比如-2，则是倒数第2章开始付费
                $max_chapter_order = Db::query("SELECT MAX(chapter_order) as max FROM " . $this->prefix . "chapter WHERE book_id=:id",
                    ['id' => $book->id])[0]['max'];
                cache('maxChapterOrder:' . $id, $max_chapter_order);
                $start_pay = (float)$max_chapter_order - $abs; //计算出起始付费章节
            }
        }

        $clicks = cache('bookClicks:' . $book->id);
        if (!$clicks) {
            $clicks = $this->bookService->getClicks($book->id);
            cache('bookClicks:' . $book->id, $clicks);
        }

        $this->assign([
            'book' => $book,
            'tags' => $tags,
            'start' => $start,
            'updates' => $updates,
            'hot' => $hot_books,
            'day_hot' => $hot_books_day,
            'month_hot' => $hot_books_month,
            'recommand' => $recommand,
            'header_title' => $book->book_name,
            'isfavor' => $isfavor,
            'comments' => $comments,
            'start_pay' => $start_pay,
            'clicks' => $clicks
        ]);
        return view($this->tpl);

    }

    public function booklist(Request $request)
    {
        $cate_selector = '全部';
        $area_selector = '全部';
        $end_selector = '全部';
        $tags = cache('tags');
        if (!$tags) {
            $tags = \app\model\Tags::all();
            cache('tags', $tags, null, 'redis');
        }
        $areas = cache('areas');
        if (!$areas) {
            $areas = \app\model\Area::all();
            cache('areas', $areas, null, 'redis');
        }

        $map = array();
        $area = $request->param('area');
        if (is_null($area) || $area == '-1') {

        } else {
            $area_selector = $area;
            $map[] = ['area_id', '=', $area];
        }
        $tag = $request->param('tag');
        if (is_null($tag) || $tag == '全部') {

        } else {
            $cate_selector = $tag;
            $map[] = ['tags', 'like', '%' . $tag . '%'];
        }
        $end = $request->param('end');
        if (is_null($end) || $end == -1) {

        } else {
            $end_selector = $end;
            $map[] = ['end', '=', $end];
        }
        $pc_page = config('page.booklist_pc_page');
        $mobile_page = config('page.booklist_mobile_page');
        $data = $this->bookService->getPagedBooks('create_time', $map, $pc_page, $mobile_page);

        $hot_books = cache('hotBooks'); //总点击
        if (!$hot_books) {
            $hot_books = $this->bookService->getHotBooks();
            cache('hotBooks', $hot_books, null, 'redis');
        }

        $hot_books_month = cache('hotBooksMonth'); //月点击
        if (!$hot_books_month) {
            $date = date('Y-m-d', strtotime('-1 mouth'));
            $hot_books_month = $this->bookService->getHotBooks($date);
            cache('hotBooksMonth', $hot_books, null, 'redis');
        }

        $hot_books_day = cache('hotBooksDay'); //日点击
        if (!$hot_books_day) {
            $date = date('Y-m-d', strtotime('-1 day'));
            $hot_books_day = $this->bookService->getHotBooks($date);
            cache('hotBooksDay', $hot_books, null, 'redis');
        }
        unset($data['page']['query']['page']);
        $param = '';
        foreach ($data['page']['query'] as $k => $v) {
            $param .= '&' . $k . '=' . $v;
        }
        $this->assign([
            'books' => $data['books'],
            'tags' => $tags,
            'areas' => $areas,
            'cate_selector' => $cate_selector,
            'area_selector' => $area_selector,
            'end_selector' => $end_selector,
            'header_title' => $cate_selector,
            'hot' => $hot_books,
            'day_hot' => $hot_books_day,
            'month_hot' => $hot_books_month,
            'page' => $data['page'],
            'param' => $param
        ]);
        return view($this->tpl);
    }

    public function addfavor()
    {
        if ($this->request->isPost()) {
            if (is_null($this->uid)) {
                return ['err' => 1, 'msg' => '用户未登录'];
            }
            $redis = RedisHelper::GetInstance();
            if ($redis->exists('favor_lock:' . $this->uid)) { //如果存在锁
                return ['err' => 1, 'msg' => '操作太频繁'];
            } else {
                $redis->set('favor_lock:' . $this->uid, 1, 3); //写入锁

                $val = input('val');
                $book_id = input('book_id');

                if ($val == 0) { //未收藏
                    $user = User::get($this->uid);
                    $books = $user->books;
                    if (count($books) >= 20) {
                        return json(['err' => 1, 'msg' => '您已经收藏太多了']); //收藏上限为20本
                    }
                    $book = Book::get($book_id);
                    $user->books()->save($book);
                    return ['err' => 0, 'isfavor' => 1]; //isfavor表示已收藏
                } else {
                    $user = User::get($this->uid);
                    $user->books()->detach(['book_id' => $book_id]);
                    return ['err' => 0, 'isfavor' => 0]; //isfavor为0表示未收藏
                }
            }
        }
        return ['err' => 1, 'msg' => '不是post请求'];
    }

    public function update()
    {
        $update_pc_page = config('page.update_pc_page');
        $update_mobile_page = config('update_mobile_page');
        $date = input('date');
        $day = input('day');
        if (empty($date)) {
            $time = 0;
        } else {
            $time = strtotime($date);
        }
        $where[] = ['last_time' , '>=', $time];
        $data = $this->bookService->getPagedBooks('last_time', $where, $update_pc_page, $update_mobile_page);
        unset($data['page']['query']['page']);
        $param = '';
        foreach ($data['page']['query'] as $k => $v) {
            $param .= '&' . $k . '=' . $v;
        }
        $this->assign([
            'books' => $data['books'],
            'page' => $data['page'],
            'param' => $param,
            'day' => $day == null ? -1 : $day,
            'header_title' => '更新',
        ]);
        return view($this->tpl);
    }

    private function getComments($book_id)
    {
        $comments = cache('comments:' . $book_id);
        if (!$comments) {
            $comments = Comments::with('user')->where('book_id', '=', $book_id)
                ->order('create_time', 'desc')->limit(0, 5)->select();
            cache('comments:' . $book_id, $comments);
        }
//        $dir = App::getRootPath() . 'public/static/upload/comments/' . $book_id;
//        foreach ($comments as &$comment) {
//            $comment['content'] = file_get_contents($dir . '/' . $comment->id . '.txt');
//        }
        return $comments;
    }
}
