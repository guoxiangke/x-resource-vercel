<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;

// https://www.youtube.com/@pastorpaulqiankunlu618/videos

final class PastorLu{
	public function __invoke($keyword)
	{
        if($keyword == "PastorLu"){
            return $this->_getData();
        }
        if($keyword == 801){
            $data = $this->_getData();
            $data['data']['url'] = "https://r2share.simai.life/@pastorpaulqiankunlu618/{$data['data']['vid']}.mp4";
            return $data;
        }
	}


    public function _getData(){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.PastorLu";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                // http://chinesetodays.org/sites/default/files/devotion_audio/2017c/220127.mp3
                $response = Http::get("https://www.youtube.com/@pastorpaulqiankunlu618/videos");
                $html =$response->body();

                // "text":"0518-每日
                $re = '/"text":"(.*?)"/';

                // "/"text\":\"(.*?)\"/gm";
                preg_match_all($re, $html, $matches);

                // dd(now()->subDay()->format('md'));
                $yesterdayTitle = '';
                $yesterday = now()->format('md');
                foreach ($matches[1] as $key => $value) {
                    if($key%4==0  &&  Str::startsWith($value, $yesterday)){
                        $yesterdayTitle = $value;
                    }
                }
                $yesterdayTitle = str_replace('-- 卢乾坤牧师 Pastor Paul Qiankun Lu', '', $yesterdayTitle);
                $yesterdayTitle = str_replace($yesterday.'-每日与主同行 –', '', $yesterdayTitle);

                // "videoId":"og2SkjrNyt0"
                $re = '/"videoId":"(.*?)"/';
                preg_match_all($re, $html, $matches);
                $ids = [];
                foreach ($matches[1] as $key => $value) {
                    if($key%4==0) $ids[] = $value;
                }
                $id = $ids[0];
                $image = 'https://share.simai.life/uPic/2023/Amn09V.jpg';
                $data =[
                    "url" => "https://www.youtube.com/watch?v={$id}",
                    'title' => "每日与主同行-{$yesterday}" ,
                    'description' => $yesterdayTitle,
                    'image' => $image,
                    'vid' => $id,
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return [
                'type' => 'link',
                "data"=> $data,
            ];
        }
}
