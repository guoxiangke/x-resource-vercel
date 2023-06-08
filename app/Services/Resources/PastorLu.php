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
	public function _invoke($keyword)
	{
        if($keyword == "PastorLu"){
            return $this->_getData();
        }
        if($keyword == 801){
            $data = $this->_getData();
            $vid = $data['data']['vid'];
            $data['data']['url'] = "https://r2share.simai.life/@pastorpaulqiankunlu618/".$vid.".mp4";

            // Add audio
            $m4a = "https://r2share.simai.life/@pastorpaulqiankunlu618/".$vid.".m4a";
            $addition = $data;
            $addition['type'] = 'music';
            $addition['data']['url']= $m4a;
            $addition['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];

            $data['addition'] = $addition;
            unset($data['addition']['addition']);
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'video',
            ];
            return $data;
        }
        // 周日的
        if($keyword == 802){
            // if($day = now()->dayOfWeek()==0){
                $data = $this->_getLastSundayData();

                $vid = $data['data']['vid'];
                $data['data']['url'] = "https://r2share.simai.life/@pastorpaulqiankunlu618/".$vid.".mp4";

                // Add audio
                $m4a = "https://r2share.simai.life/@pastorpaulqiankunlu618/".$vid.".m4a";
                $addition = $data;
                $addition['type'] = 'music';
                $addition['data']['url']= $m4a;
                $addition['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $keyword,
                    "type" => 'audio',
                ];
                $data['addition'] = $addition;
                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $keyword,
                    "type" => 'video',
                ];
                return $data;
            // }
        }
	}


    private function _getData(){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.PastorLu";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                // http://chinesetodays.org/sites/default/files/devotion_audio/2017c/220127.mp3
                $response = Http::get("https://www.youtube.com/@pastorpaulqiankunlu618/videos");
                $html =$response->body();

                
                $re = '/vi\/([^\/]+).*?"text":"(.*?)"/';
                preg_match_all($re, $html, $matches);
                

                $day = now()->format('md');
                
                $lastSundayTitle = null;
                $yesterdayTitle = null;
                $yesterdayIndex = null;
                $lastSundayIndex = null;
                foreach ($matches[2] as $key => $value) {
                    // "text":"0518-每日
                    if(Str::startsWith($value, $day)){
                        $yesterdayTitle = $value;
                        $yesterdayIndex = $key;
                    }
                    if(Str::containsAll($value, ['主日信息', $day])){
                        $lastSundayTitle = $value;
                        $lastSundayIndex = $key;
                    }
                }

                $vid = $matches[1][$yesterdayIndex];
                $image = 'https://share.simai.life/uPic/2023/Amn09V.jpg';

                $yesterdayTitle = str_replace('-- 卢乾坤牧师 Pastor Paul Qiankun Lu', '-- 訂閱、點讚，轉發即是宣教', $yesterdayTitle);
                $yesterdayTitle = str_replace($day.'-每日与主同行 –', '', $yesterdayTitle);

                $data = [
                    'type' => 'link',
                    'data' => [
                        "url" => "https://www.youtube.com/embed/{$vid}",
                        'title' => "每日与主同行-{$day}" ,
                        'description' => $yesterdayTitle,
                        'image' => $image,
                        'vid' => $vid,
                    ]
                ];

                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $vid,
                    "type" => 'everyday',
                ];

                // dd($lastSundayIndex, $lastSundayTitle,$yesterdayIndex,$yesterdayTitle);
                if($lastSundayTitle){
                    $vid = $matches[1][$lastSundayIndex];
                    $descs = explode('：',$lastSundayTitle);
                    $data['addition'] = [
                        'type' => 'link',
                        'data' => [
                            "url" => "https://www.youtube.com/embed/{$vid}",
                            'title' => $descs[0],//"主日信息-{$day}" ,
                            'description' => $descs[1],//$lastSundayTitle,
                            'image' => $image,
                            'vid' => $vid,
                        ]
                    ];
                    $data['addition']['statistics'] = [
                        'metric' => class_basename(__CLASS__),
                        "keyword" => $vid,
                        "type" => 'sunday',
                    ];
                }
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return $data;

        }



    private function _getLastSundayData(){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.PastorLu.lastSunday";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                // http://chinesetodays.org/sites/default/files/devotion_audio/2017c/220127.mp3
                $response = Http::get("https://www.youtube.com/@pastorpaulqiankunlu618/videos");
                $html =$response->body();

                
                $re = '/vi\/([^\/]+).*?"text":"(.*?)"/';
                preg_match_all($re, $html, $matches);

                $lastSundayTitle = null;
                $lastSundayIndex = null;
                foreach ($matches[2] as $key => $value) {
                    if(Str::containsAll($value, ['主日信息'])){
                        $lastSundayTitle = $value;
                        $lastSundayIndex = $key;
                        break;
                    }
                }

                $image = 'https://share.simai.life/uPic/2023/Amn09V.jpg';
                $vid = $matches[1][$lastSundayIndex];
                $descs = explode('：',$lastSundayTitle);
                $data = [
                    'type' => 'link',
                    'data' => [
                        "url" => "https://www.youtube.com/embed/{$vid}",
                        'title' => $descs[0],//"主日信息-{$day}" ,
                        'description' => $descs[1],//$lastSundayTitle,
                        'image' => $image,
                        'vid' => $vid,
                    ]
                ];

                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $vid,
                    "type" => 'sunday',
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return $data;

        }
}
