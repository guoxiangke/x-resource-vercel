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
final class CCAC{
	public function _invoke($keyword)
	{
        //3位数关键字xxx
        $offset = substr($keyword, 3) ?: 0;
        $keyword = substr($keyword, 0, 3);
        if($keyword == '796'){
            $response = Http::get("https://www.youtube.com/@cantoneseccac1966/streams");
            $html =$response->body();

            $re = '/"text":"psyuanccac([^"]+).*?"videoId":"([^"]+)"/';
            preg_match_all($re, $html, $matches);

            $vid = $matches[2][$offset];
            $channelDomain = env('R2_DOMAIN')."/@cantoneseccac1966/";
            $url = $channelDomain.$vid.".mp4";
            $image = 'https://share.simai.life/uPic/2023/IeDDmx.jpg';

            $data = [
                'type' => 'link',
                'data' => [
                    "url" => $url,
                    'title' => "【CCAC】聖谷華人宣道會粤語堂",
                    'description' => $matches[1][$offset]."粵語堂主日直播回放",
                    'image' => $image,
                    'vid' => $vid,
                ]
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'video',
            ];

            // Add audio
            $m4a = $channelDomain.$vid.".m4a";
            $addition = $data;
            $addition['type'] = 'music';
            $addition['data']['url']= $m4a;
            $addition['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
            $data['addition'] = $addition;
            return $data;
        }
        return null;
	}
}
