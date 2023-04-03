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
final class Zhen{
	public function __invoke($keyword)
	{
        if($keyword == '789'){
            $cacheKey = "xbot.keyword.".$keyword;

            $data = Cache::store('redis')->get($cacheKey, false);

            if(!$data){
                $url = now()->format('/Y/m/');
                $year = date('Y');
                $date = date('ymd');

                // 
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
                foreach ($htmlTmp->find('tbody tr') as $e) {
                    $meta[$e->find('td',0)->plaintext . $e->find('td',1)->plaintext] = $e->find('td',2)->plaintext;
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
        if($keyword == '799'){

            // $date = date('Ymd');
            // ->subDays(1)
            $url = now()->format('/Y/m/');

            $url = 'https://www.tpehoc.org.tw'.$url;
            $cacheKey = "xbot.keyword.".$keyword;
            $data = Cache::store('redis')->get($cacheKey, false);

            if(!$data){
                $client = new Client();
                $response = $client->get($url);//,['proxy' => 'socks5://54.176.71.221:8011']
                $html = (string)$response->getBody();
                $htmlTmp = HtmlDomParser::str_get_html($html);
                $mp3 =  $htmlTmp->findOne('.wp-audio-shortcode source')->getAttribute('src');
                $title =  $htmlTmp->findOne('.post-content-outer h3')->text();

                $description =  $htmlTmp->findOne('.post-content-outer .post-content p')->text();
                $description = Str::remove($title, $description);
                $title = Str::remove('&#8230;', $title);
                $description = Str::remove('&#8230;', $description);
                

                $image = 'https://lytx2021.s3-ap-southeast-1.amazonaws.com/share/799/blSf8E7UOiXCZwL.png';
                $url =  $htmlTmp->findOne('.post-content-outer h3 a')->getAttribute('href');
                $addition =[
                    'type' => 'link',
                    'data' => compact(['image','url','title','description']),
                ];

                $data =[
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3,
                        'title' => $title,
                        'description' => $description,
                        'image' => $image,
                    ],
                    'addition'=>$addition,
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return $data;
        }

        if(Str::contains($keyword, '@AI助理')){
            // https://laravel-news.com/openai-for-laravel
            // https://github.com/openai-php/laravel
            // https://github.com/openai-php/client
            $keyword = trim(Str::remove('@AI助理', $keyword));
            $client = new Client();
            $url = 'https://gpt3.51chat.net/api/' . $keyword;
            $response = Http::get($url);
            $data = $response->json();
            return [
                "type" => "text",
                "data" => ['content'=>$data['choices'][0]['text']],
            ];
        }

        // https://youtu.be/Y8X8JXNbBbI
        // https://www.youtube.com/watch?v=Y8X8JXNbBbI&list=RDwwpK3p4heEM&index=2
        if(Str::startsWith($keyword, ['https://youtu.be/','https://www.youtube.com/watch?v='])){
            $command = "/var/www/html/youtube-dl --no-playlist -J $keyword";
            // $command = "/usr/local/bin/youtube-dl --no-playlist -J $keyword";
            $output = json_decode(shell_exec($command));

            $title = $output->fulltitle;
            foreach ($output->formats as $key => $format) {
                if(in_array($format->format_id,[139,140,141])){
                    $mp3 = $format->url;
                }
                if($format->format_id == 22){
                    $mp4 = $format->url;
                }
            }
            
            // $mp4 = $output->formats[11]->url; //22 720p 18 360p 
            $thumbnail = $output->thumbnail;
            //https://i.ytimg.com/vi/T7SrzqlV9NY/maxresdefault.jpg

            $addition = [
                'type' => 'link',
                "data"=> [
                    "url" => $mp4,
                    'title' => $title,
                    'description' => '720P',
                    'image' => $thumbnail,
                ]
            ];
            $data = [
                'type' => 'music',
                "data"=> [
                    "url" => $mp3,
                    'title' => $title,
                    'description' => '解析音频',
                    // 'image' => $thumbnail,
                ],
                'addition'=>$addition,
            ];
            Log::error(__CLASS__,$data);
            return $data;
        }
        return null;
	}
}
