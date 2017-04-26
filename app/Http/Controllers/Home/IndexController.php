<?php
namespace App\Http\Controllers\Home;

use App\Models\Admin\Book;
use App\Models\Admin\Links;

class IndexController extends BaseController
{
    public function getIndex(Book $book)
    {
        $categorys = config('book.categorys');

        //最近更新
        $newLists = $book->lists([],'updated_at desc',50,false);
        if(count($newLists)){
            $newLists = $this->setCatname($newLists->toArray());
        }

        //最新入库
        $newInserts = $book->lists([],'id desc',50,false);
        if(count($newInserts)){
            $newInserts = $this->setCatname($newInserts->toArray());
        }

        //各分类推荐
        $tjLists = \Cache::remember('tjLists' , 600 ,function() use ($categorys,$book) {
            $tjLists = [];
            $i = 1;
            foreach($categorys as $k => $v){
                if($k == 9) break;
                $tjLists[$i]['catname'] = $v['name'];
                $tjLists[$i]['id'] = $k;
                $tjLists[$i]['data'] = $book->lists(['catid' => $k],'',7,false)->toArray();
                $i++;
            }
        });

        //封面推荐
        $ftLists = \Cache::remember('ftLists' , 600 ,function() use ($book) {
            return $book->ftlists([],'hits DESC',6)->toArray();
        });

        //firendLinks
        $firendLinks = \Cache::remember('firendLinks', 600, function(){
            $links = new Links();
            return $links->lists();
        });

        $data = [
            'newLists'   => $newLists,
            'newInserts' => $newInserts,
            'tjLists'    => $tjLists,
            'ftLists'    => $ftLists,
            'firendLinks'=> $firendLinks,
        ];
        return home_view('index.index',$data);
    }

    public function getTest(Book $book)
    {

    }

    /**
     *
     * @param $data
     * @return array
     */
    protected function setCatname($data)
    {
        global $categorys;
        $new_data = [];
        foreach($data as $v){
            $v['catname'] = $categorys[$v['catid']]['name'];
            $new_data[] = $v;
        }
        return $new_data;
    }
}