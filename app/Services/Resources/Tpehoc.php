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
final class Tpehoc{
	public function _invoke($keyword)
	{
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
                $mp3 = str_replace('?_=1', '', $mp3);
                $title =  $htmlTmp->findOne('.post-content-outer h3')->text();

                $description =  $htmlTmp->findOne('.post-content-outer .post-content p')->text();
                $description = Str::remove($title, $description);
                $title = Str::remove('&#8230;', $title);
                $description = Str::remove('&#8230;', $description);
                

                $image = 'https://lytx2021.s3-ap-southeast-1.amazonaws.com/share/799/blSf8E7UOiXCZwL.png';
                $url = $htmlTmp->findOne('.post-content-outer h3 a')->getAttribute('href');
                $addition =[
                    'type' => 'link',
                    'data' => compact(['image','url','title','description']),
                ];
                $addition['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $keyword,
                    "type" => 'link',
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
                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $keyword,
                    "type" => 'audio',
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return $data;
        }
        if($keyword == '798'){
            // https://zgtai.com/教会事工/门徒训练
            // $cacheKey = "xbot.keyword.".$keyword;
            // $data = Cache::store('redis')->get($cacheKey, false);
            // if(!$data){
                $items = [
                    ["title"=>"52. 门徒训练与圣灵的建造工作（二）","url"=> "/zgtai/52.mp3"],
                    ["title"=>"51. 门徒训练与圣灵的建造工作（一）","url"=> "/zgtai/51.mp3"],
                    ["title"=>"50. 领袖的拣选与榜样","url"=> "/zgtai/50.mp3"],
                    ["title"=>"49. 门徒祷告生活的再思","url"=> "/zgtai/49.mp3"],
                    ["title"=>"48. 门徒的进修生活与成长","url"=> "/zgtai/48.mp3"],
                    ["title"=>"47. 门徒的时间管理与灵修生活","url"=> "/zgtai/47.mp3"],
                    ["title"=>"46. 门徒的职业与工作观","url"=> "/zgtai/46.mp3"],
                    ["title"=>"45. 门徒的圣洁与成圣生活","url"=> "/zgtai/45.mp3"],
                    ["title"=>"44. 基督徒团契生活的操练","url"=> "/zgtai/44.mp3"],
                    ["title"=>"43. 攻克己身的挑战","url"=> "/zgtai/43.mp3"],
                    ["title"=>"42. 如何训练门徒信心的功课","url"=> "/zgtai/42.mp3"],
                    ["title"=>"41. 学习倾听神的声音","url"=> "/zgtai/41.mp3"],
                    ["title"=>"40. 作领袖的代价与陷阱","url"=> "/zgtai/40.mp3"],
                    ["title"=>"39. 寻找合神心意的领袖","url"=> "/zgtai/39.mp3"],
                    ["title"=>"38. 门徒在苦难中的操练","url"=> "/zgtai/38.mp3"],
                    ["title"=>"37. 如何听讲道？","url"=> "/zgtai/37.mp3"],
                    ["title"=>"36. 门徒与十字架的道理","url"=> "/zgtai/36.mp3"],
                    ["title"=>"35. 作门徒必须终身学习","url"=> "/zgtai/35.mp3"],
                    ["title"=>"34. 门徒与讲道操练（二）","url"=> "/zgtai/34.mp3"],
                    ["title"=>"33. 门徒与讲道操练（一）","url"=> "/zgtai/33.mp3"],
                    ["title"=>"32. 如何明白神的旨意","url"=> "/zgtai/32.mp3"],
                    ["title"=>"31. 过敬虔的门徒生活","url"=> "/zgtai/31.mp3"],
                    ["title"=>"30. 展开门徒训练者的服事","url"=> "/zgtai/30.mp3"],
                    ["title"=>"29. 如何带领一个小组","url"=> "/zgtai/29.mp3"],
                    ["title"=>"28. 如何带领归纳式研经法(查经班)","url"=> "/zgtai/28.mp3"],
                    ["title"=>"27. 门徒家庭崇拜(家庭祭坛)的建立","url"=> "/zgtai/27.mp3"],
                    ["title"=>"26. 门徒敬拜的操练（二）","url"=> "/zgtai/26.mp3"],
                    ["title"=>"25. 门徒敬拜的操练（一）","url"=> "/zgtai/25.mp3"],
                    ["title"=>"24. 作门徒与钱财的好管家","url"=> "/zgtai/24.mp3"],
                    ["title"=>"23. 成为热心事奉的门徒","url"=> "/zgtai/23.mp3"],
                    ["title"=>"22. 门徒的情绪管理（二）","url"=> "/zgtai/22.mp3"],
                    ["title"=>"21. 门徒的情绪管理（一）","url"=> "/zgtai/21.mp3"],
                    ["title"=>"20. 迈向灵性的成熟（二）","url"=> "/zgtai/20.mp3"],
                    ["title"=>"19. 迈向灵性的成熟（一）","url"=> "/zgtai/19.mp3"],
                    ["title"=>"18. 门徒训练的栽培计划","url"=> "/zgtai/18.mp3"],
                    ["title"=>"17. 门徒的品格操练－话语和舌头的控制","url"=> "/zgtai/17.mp3"],
                    ["title"=>"16. 门徒进阶训练的栽培计划","url"=> "/zgtai/16.mp3"],
                    ["title"=>"15. 初信者的栽培计划","url"=> "/zgtai/15.mp3"],
                    ["title"=>"14. 门徒训练与恩赐操练","url"=> "/zgtai/14.mp3"],
                    ["title"=>"13. 门徒训练与配搭事奉","url"=> "/zgtai/13.mp3"],
                    ["title"=>"12. 门徒训练与教会增长","url"=> "/zgtai/12.mp3"],
                    ["title"=>"11. 门徒的纪律生活","url"=> "/zgtai/11.mp3"],
                    ["title"=>"10. 门徒训练者的操练","url"=> "/zgtai/10.mp3"],
                    ["title"=>"09. 从信徒到门徒","url"=> "/zgtai/09.mp3"],
                    ["title"=>"08. 门徒训练的目标(四)：团契生活的操练","url"=> "/zgtai/08.mp3"],
                    ["title"=>"07. 门徒训练的目标(三)：作见证的操练","url"=> "/zgtai/07.mp3"],
                    ["title"=>"06. 门徒训练的目标(二)：祷告的操练","url"=> "/zgtai/06.mp3"],
                    ["title"=>"05. 门徒训练的目标(一)：有关读经","url"=> "/zgtai/05.mp3"],
                    ["title"=>"04. 寻找人作门徒（二）","url"=> "/zgtai/04.mp3"],
                    ["title"=>"03. 寻找人作门徒（一）","url"=> "/zgtai/03.mp3"],
                    ["title"=>"02. 耶稣与门徒","url"=> "/zgtai/02.mp3"],
                    ["title"=>"01. 作主门徒的挑战","url"=> "/zgtai/01.mp3"],
                ];
                $index = now()->format('z');
                $item = $items[$index%51];
                $data =[
                    'type' => 'music',
                    "data"=> [
                        "url" => "https://r2share.simai.life" . $item['url'],
                        'title' => $item['title'],
                        'description' => "罗门",
                    ],
                    // 'addition'=>$addition,
                ];
                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $keyword,
                    "type" => 'audio',
                ];
                // Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            // }
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
                "data" => ['content'=>$data['choices'][0]['message']['content']],
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
