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
	public function __invoke($keyword)
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
                    $meta[$e->find('td',0)->plaintext . $e->find('td',1)->plaintext] = $e->find('td',2)->plaintext;
                    // 每天一句文本发群里！
                    $meta[$e->find('td',0)->plaintext . $e->find('td',1)->plaintext . '.text'] = $e->find('td',3)->plaintext;
                }
                $descA = $meta[date('n-j-Y') . 'a']??'';
                $descB = $meta[date('n-j-Y') . 'b']??'';
                $descC = $meta[date('n-j-Y') . 'c']??'';
                $additionc = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3c,
                        'title' => '分享-'.$date,
                        'description' => $descC,
                        'image' => $image,
                    ],
                    // 'addition'=>$additionD,
                ];

                $textDescD = $meta[date('n-j-Y') . 'c.text']??'';
                // $textDescD = "这是一段测试哦！\n换行3\r\n4";
                if($textDescD){
                    $additionD = [
                        'type' => 'text',
                        "data"=> [
                            'content' => $textDescD,
                        ],
                    ];
                    $additionc['addition'] = $additionD;
                } 
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
                $data = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3a,
                        'title' => '讀經-'.$date,
                        'description' => $descA,
                        'image' => $image,
                    ],
                    'addition'=>$additionb,
                ];
                //      0 (for Sunday) through 6 (for Saturday)
                if(date('w')==0 || date('w')==6){
                    $data = $additionc;
                }
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }

            return $data;
        }
        return null;
	}
}
