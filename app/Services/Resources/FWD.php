<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;

use Symfony\Component\HttpClient\Psr18Client;
use Tectalic\OpenAi\Authentication;
// use Tectalic\OpenAi\Client;
use Tectalic\OpenAi\Manager;
use Tectalic\OpenAi\Models\Completions\CreateRequest;
final class FWD{
	public function _invoke($keyword)
	{
        if($keyword == '789'){
            $cacheKey = "xbot.keyword.".$keyword;
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                $url = now()->format('/Y/m/');
                $year = date('Y');
                $date = date('ymd');

                // 正常每天有3个音频，周六日只有c音频
                $domain = "share.simai.life/fwd";
                $mp3a = "https://{$domain}/".date('Y')."/fwd{$date}_a.mp3";
                $mp3b = "https://{$domain}/".date('Y')."/fwd{$date}_b.mp3";
                $mp3c = "https://{$domain}/".date('Y')."/fwd{$date}_c.mp3";
                $image = 'https://share.simai.life/uPic/2022/Of6qHa.jpg';


                $client = new Client();
                $url = 'https://docs.google.com/spreadsheets/d/1xIdXT4mTKHRulwJeHkzL_1dUuSsirnriGNHMvlOfdCc/htmlview';
                $response = $client->get($url);
                $html = (string)$response->getBody();
                $htmlTmp = HtmlDomParser::str_get_html($html);
                $meta = [];
                foreach ($htmlTmp->find('tbody tr') as $e) {
                    $cloumn1 = $e->find('td',0)->plaintext; //date
                    $cloumn2 = $e->find('td',1)->plaintext; //abc
                    $cloumn3 = $e->find('td',2)->plaintext; //desc
                    // $cloumn4 = $e->find('td',3)->plaintext; //c-text
                    $meta[$cloumn1 . $cloumn2] = $cloumn3;
                    // 每天一句文本发群里！
                    if($cloumn2=='c')
                        $meta[$cloumn1 . $cloumn2 . '.text'] = $e->find('td',3)->plaintext;
                }
                $descA = $meta[date('n-j-Y') . 'a']??'';
                $descB = $meta[date('n-j-Y') . 'b']??'';
                $descC = $meta[date('n-j-Y') . 'c']??'';
                // $textDescD = "这是一段测试哦！\n换行3\r\n4";
                $textDescD = $meta[date('n-j-Y') . 'c.text']??"=灵修分享=\n今天的天英讨论问题是：";
               
                $additionc = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3c,
                        'title' => '分享-'.$date,
                        'description' => $descC,
                        'image' => $image,
                    ]
                ];
                $additionb = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3b,
                        'title' => '讀經-'.$date,
                        'description' => $descB,
                        'image' => $image,
                    ],
                    'addition'=>$additionc,
                ];
                $additiona = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3a,
                        'title' => '讀經-'.$date,
                        'description' => $descA,
                        'image' => $image,
                    ],
                    'addition'=>$additionb,
                ];

                // 周六日只发c
                $data = [
                    'type' => 'text',
                    "data"=> [
                        'content' => $textDescD,
                    ],
                    'addition'=>now()->isWeekend()?$additionc:$additiona,
                ];
                
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }

            return $data;
        }

        if($keyword == '803'){
            $response = Http::get("https://www.youtube.com/@fwdforwardchurch7991/streams");
            $html =$response->body();

            $re = '/"text":"FWDFC ([^"]+).*?"videoId":"([^"]+)"/';
            preg_match_all($re, $html, $matches);

            $day = now()->format('md');

            foreach($matches[1] as $key => $value){
                if(Str::containsAll($value, ['主日崇拜'])){
                    $lastSundayTitle = $value;
                    $lastSundayIndex = $key;
                    break;
                }
            }
            
            $vid = $matches[2][$lastSundayIndex];
            $channelDomain = "https://r2share.simai.life/@fwdforwardchurch7991/";
            $url = $channelDomain.$vid.".mp4";
            $image = 'https://share.simai.life/uPic/2023/IeDDmx.jpg';

            $descs = explode('【',$lastSundayTitle);
            $data = [
                'type' => 'link',
                'data' => [
                    "url" => $url,
                    'title' => "【日出神話】主日崇拜線上直播",
                    'description' => $descs[0],
                    'image' => $image,
                    'vid' => $vid,
                ]
            ];

            // Add audio
            $m4a = $channelDomain.$vid.".m4a";
            $addition = $data;
            $addition['type'] = 'music';
            $addition['data']['url']= $m4a;
            $data['addition'] = $addition;

            return $data;
        }
        if($keyword == '804'){
            $response = Http::get("https://www.youtube.com/@fwdforwardchurch7991/streams");
            $html =$response->body();

            $re = '/"text":"FWDFC ([^"]+).*?"videoId":"([^"]+)"/';
            preg_match_all($re, $html, $matches);

            $day = now()->format('md');

            foreach($matches[1] as $key => $value){
                if(Str::containsAll($value, ['禱告會'])){
                    $lastSundayTitle = $value;
                    $lastSundayIndex = $key;
                    break;
                }
            }
            
            $vid = $matches[2][$lastSundayIndex];
            $channelDomain = "https://r2share.simai.life/@fwdforwardchurch7991/";
            $url = $channelDomain.$vid.".mp4";
            $image = 'https://share.simai.life/uPic/2023/IeDDmx.jpg';

            $descs = explode('【',$lastSundayTitle);
            $data = [
                'type' => 'link',
                'data' => [
                    "url" => $url,
                    'title' => "前進教會週三禱告會",
                    'description' => $lastSundayTitle,
                    'image' => $image,
                    'vid' => $vid,
                ]
            ];
            
            // Add audio
            $m4a = $channelDomain.$vid.".m4a";
            $addition = $data;
            $addition['type'] = 'music';
            $addition['data']['url']= $m4a;
            $data['addition'] = $addition;
            return $data;
        }
        return null;
	}
}
