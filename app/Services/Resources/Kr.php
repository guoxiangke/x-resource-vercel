<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;

final class Kr{
    public function _invoke($keyword)
    {
        // 互联网人的资讯早餐（音频版）周1-5
        if($keyword == "8点1氪"){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.kr";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                
                $client = new Client();
                $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36';
                $headers = [
                    'User-Agent'=> $userAgent,
                ];
                $url = 'http://36kr.com/column/491522785281';
                $response = $client->get($url, [
                    'headers'  => $headers,
                    'debug' => false,
                ]);
                $html = (string)$response->getBody();

                    // cURL error 23: Failed reading the chunked-encoded stream
                // $response = Http::get("http://36kr.com/column/491522785281");
                // $html = $response->body();

                $htmlTmp = HtmlDomParser::str_get_html($html);
                $mp3 =  $htmlTmp->getElementByTagName('audio')->getAttribute('src');
                $title =  $htmlTmp->findOne('.audio-title')->text();


                $data =[
                    "url" => $mp3,
                    'title' => "【8点1氪】{$date}",
                    'description' => $title,
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
