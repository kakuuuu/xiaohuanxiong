<?php


namespace app\api\controller;


use app\model\Book;
use think\Controller;

class Sitemap extends Controller
{
    public function gen() {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return json(['success' => 0, 'msg' => 'api密钥不能为空']);
        }
        if ($key != config('site.api_key')) {
            return json(['success' => 0, 'msg' => 'api密钥错误']);
        }

        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $data_array = $this->create_array();
        foreach ($data_array as $data) {
            $content .= $this->create_item($data);
        }
        $content .= '</urlset>';
        $fp = fopen($_SERVER['DOCUMENT_ROOT'] .'/sitemap.xml', 'w+');
        fwrite($fp, $content);
        fclose($fp);
        return json(['success' => 1, 'msg' => '生成网站地图成功']);
    }

    private function create_array(){
        $site_name = config('site.url');
        $data = array();
        $main = array(
            'loc' => $site_name,
            'priority' => '1.0'
        );
        $booklist= array(
            'loc' => $site_name.'/booklist',
            'priority' => '0.5',
            'lastmod' => date("Y-m-d"),
            'changefreq' => 'yearly'
        );

        $books = Book::all();
        foreach ($books as &$book){ //这里构建所有的内容页数组
            if ($this->end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
            $temp = array(
                'loc' => $site_name.'/'.BOOKCTRL.'/'.$book->id,
                'priority' => '0.9',
            );
            array_push( $data,$temp);
        }

        array_push($data,$main);
        array_push($data,$booklist);
        return $data;
    }

    private function create_item($data)
    {
        $item = "<url>\n";
        $item .= "<loc>" . $data['loc'] . "</loc>\n";
        $item .= "<priority>" . $data['priority'] . "</priority>\n";
        $item .= "</url>\n";
        return $item;
    }
}