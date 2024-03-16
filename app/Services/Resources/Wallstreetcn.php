<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;

final class Wallstreetcn{
	public function _invoke($keyword) {
        if($keyword == "华尔街见闻早餐"){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.Wallstreetcn";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
            // if(1){
                $response = Http::get("https://api-one-wscn.awtmt.com/apiv1/search/article?query=华尔街见闻早餐&cursor=&limit=1&vip_type=");
                $json =$response->json();
                $id = $json['data']['items'][0]['id'];

                // $mp3 =  $json['data']['audio_uri']; 机器读， 不要，要从html里找到

                $response = Http::get("https://api-one-wscn.awtmt.com/apiv1/content/articles/{$id}?extract=0");
                $json =$response->json();
                $html = $json['data']['content'];

                $htmlTmp = HtmlDomParser::str_get_html($html);
                $mp3 =  $htmlTmp->findOne('img.editor-placeholder')->getAttribute('data-uri');

                $title = $json['data']['audio']['title'];;
				$desc = "华尔街见闻早餐:{$date}"." 市场有风险，投资需谨慎。本文不构成个人投资建议";
                $data =[
                    "url" => $mp3,
                    'title' => $title,
                    'description' => $desc,
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return [
                'type' => 'music',
                "data"=> $data,
            ];
        }
	}
}
