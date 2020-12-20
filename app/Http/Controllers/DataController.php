<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use KubAT\PhpSimple\HtmlDomParser;

class DataController extends Controller
{
    public $URL = "https://promkes.kemkes.go.id/category/topik-kesehatan";

	public $URL_DETIK = "https://www.detik.com/tag/kemenkes/?sortby=time&page=";
	
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
		$error = "";
        $status_url_detik = Http::get($this->URL_DETIK);
		
		try {
			$status_url = Http::get($this->URL);
		} 
		catch (\Exception $e)
		{
			$error = $e->getMessage();
		}
		
		if($error === "") {
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
		else {
			for($i=1; $i<3; $i++) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->URL_DETIK."".$i);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($ch);
				curl_close($ch);
				
				$dom = HtmlDomParser::str_get_html($response);
				
				$div = $dom->find('div.list.media_rows.list-berita')[0];
				
				$articles = $div->find('article');
				
				$j=0;
				foreach($articles as $article)
				{
					if($i===2)
					{
						if($j===5)
						{
							break;
						}
						else
						{
							$arr['title'] = $article->find('h2')[0]->innertext;
							$arr['link'] = $article->find('a')[0]->href;
							$arr['image'] = $article->find('img')[0]->src;		
							$arr['content'] = $article->find('p')[0]->innertext;
							$str = substr($article->find('span.date')[0]->innertext,strpos($article->find('span.date')[0]->innertext, "</span>"));
							$arr['date'] = substr($str,7);
						}
					}
					else {
						$arr['title'] = $article->find('h2')[0]->innertext;
						$arr['link'] = $article->find('a')[0]->href;
						$arr['image'] = $article->find('img')[0]->src;
						$arr['content'] = $article->find('p')[0]->innertext;
						$str = substr($article->find('span.date')[0]->innertext,strpos($article->find('span.date')[0]->innertext, "</span>"));
						$arr['date'] = substr($str,7);
					}
					
					$j++;
					array_push($hasil, $arr);
				}
			}
			
			return response()->json($hasil);
		}

    }
}
