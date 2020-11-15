<?php

namespace App\Http\Controllers;

use KubAT\PhpSimple\HtmlDomParser;

class DataController extends Controller
{
    public $URL = "https://promkes.kemkes.go.id/category/topik-kesehatan";

    public function index()
    {
        return response()->json([
            'app' => config('app.name'),
            'version' => "1.0.0"
        ]);
    }

    public function result()
    {
        $hasil = [];
        for($i=1; $i<4; $i++){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->URL."/".$i);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
    
            $dom = HtmlDomParser::str_get_html($response);
    
            $col_sm_8 = $dom->find('div.col-sm-8')[0];
            
            $content = $col_sm_8->find('div.itemcontent-kegiatan');

            foreach($content as $item)
            {
                $arr['title'] = $item->find('a > h4')[0]->innertext;
                $arr['link'] = $item->find('a')[0]->href;
                $arr['image'] = $item->find('img')[0]->src;
                $arr['content'] = $item->find('p')[0]->innertext;
                $arr['date'] = $item->find('div.date > time')[0]->innertext;
                array_push($hasil,$arr);
            }
        }

        return response()->json($hasil);
    }
}
