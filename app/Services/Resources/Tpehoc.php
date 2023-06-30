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
            $url = now()->subDays(1)->format('/Y/m/');

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
                    "门徒训练与圣灵的建造工作（二）",
                    "门徒训练与圣灵的建造工作（一）",
                    "领袖的拣选与榜样",
                    "门徒祷告生活的再思",
                    "门徒的进修生活与成长",
                    "门徒的时间管理与灵修生活",
                    "门徒的职业与工作观",
                    "门徒的圣洁与成圣生活",
                    "基督徒团契生活的操练",
                    "攻克己身的挑战",
                    "如何训练门徒信心的功课",
                    "学习倾听神的声音",
                    "作领袖的代价与陷阱",
                    "寻找合神心意的领袖",
                    "门徒在苦难中的操练",
                    "如何听讲道？",
                    "门徒与十字架的道理",
                    "作门徒必须终身学习",
                    "门徒与讲道操练（二）",
                    "门徒与讲道操练（一）",
                    "如何明白神的旨意",
                    "过敬虔的门徒生活",
                    "展开门徒训练者的服事",
                    "如何带领一个小组",
                    "如何带领归纳式研经法(查经班)",
                    "门徒家庭崇拜(家庭祭坛)的建立",
                    "门徒敬拜的操练（二）",
                    "门徒敬拜的操练（一）",
                    "作门徒与钱财的好管家",
                    "成为热心事奉的门徒",
                    "门徒的情绪管理（二）",
                    "门徒的情绪管理（一）",
                    "迈向灵性的成熟（二）",
                    "迈向灵性的成熟（一）",
                    "门徒训练的栽培计划",
                    "门徒的品格操练－话语和舌头的控制",
                    "门徒进阶训练的栽培计划",
                    "初信者的栽培计划",
                    "门徒训练与恩赐操练",
                    "门徒训练与配搭事奉",
                    "门徒训练与教会增长",
                    "门徒的纪律生活",
                    "门徒训练者的操练",
                    "从信徒到门徒",
                    "门徒训练的目标(四)：团契生活的操练",
                    "门徒训练的目标(三)：作见证的操练",
                    "门徒训练的目标(二)：祷告的操练",
                    "门徒训练的目标(一)：有关读经",
                    "寻找人作门徒（二）",
                    "寻找人作门徒（一）",
                    "耶稣与门徒",
                    "作主门徒的挑战",
                ];
                $items = array_reverse($items);
                $index = now()->addDay(1)->format('z') % 51;
                $item = $items[$index-1];
                $index = str_pad($index, 2, "0", STR_PAD_LEFT);
                $data =[
                    'type' => 'music',
                    "data"=> [
                        "url" => "https://r2share.simai.life/zgtai.com/mds/" . $index . ".mp3",
                        'title' => "($index/52)" . $item,
                        'description' => "罗门,门徒训练",
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

        if($keyword == '797'){
            // https://zgtai.com/教会事工/门徒训练
            // $cacheKey = "xbot.keyword.".$keyword;
            // $data = Cache::store('redis')->get($cacheKey, false);
            // if(!$data){
                $items=[
                    "教牧人员与十字架的道路 (2)",
                    "教牧人员与十字架的道路 (1)",
                    "教牧人员的受伤与医治",
                    "教牧人员(教会)与社会责任",
                    "传道人的生命与事奉",
                    "教牧人员与辅导",
                    "再思教牧人员与冲突处理",
                    "再思教牧人员的家庭生活",
                    "教牧人员与信徒皆祭司 (2)",
                    "教牧人员与信徒皆祭司 (1)",
                    "教牧人员的压力与能力",
                    "教牧人员与教会增长",
                    "教牧人员与宣教异象 (2)",
                    "教牧人员与宣教异象 (1)",
                    "教牧人员与事奉工场的转换",
                    "弟兄姊妹转换教会的危机与转机",
                    "教会长老执事的选择 (2)",
                    "教会长老执事的选择 (1)",
                    "教牧人员与浸礼的举行",
                    "教牧人员与圣餐的举行",
                    "祷告聚会的计划与进行",
                    "主日崇拜的计划与进行",
                    "教牧人员与门徒训练 (2)",
                    "教牧人员与门徒训练 (1)",
                    "师母的角色扮演",
                    "传道人的家庭",
                    "教牧人员与讲道 (2)",
                    "教牧人员与讲道 (1)",
                    "教牧人员的属灵危机-耗尽 (2)",
                    "教牧人员的属灵危机-耗尽 (1)",
                    "教牧人员的牧养工作 (2)",
                    "教牧人员的牧养工作 (1)",
                    "教牧人员与教会纪律 (2)",
                    "教牧人员与教会纪律 (1)",
                    "教牧人员特有的危险",
                    "教牧人员冲突的处理",
                    "教牧人员的待遇问题 (2)",
                    "教牧人员的待遇问题 (1)",
                    "教牧人员的感情陷阱 (2)",
                    "教牧人员的感情陷阱 (1)",
                    "传道人事奉的危机 (2)",
                    "传道人事奉的危机 (1)",
                    "教牧的同工关系 (2)",
                    "教牧的同工关系 (1)",
                    "传道人的角色与职份 (3)",
                    "传道人的角色与职份 (2)",
                    "传道人的角色与职份 (1)",
                    "教牧人员的装备-有关读书",
                    "传道人的品格塑造 (2)",
                    "传道人的品格塑造 (1)",
                    "传道人的神圣呼召 (2)",
                    "传道人的神圣呼召 (1)"
                ];
                $items = array_reverse($items);
                $index = now()->addDay(1)->format('z') % 51;
                $item = $items[$index-1];
                $index = str_pad($index, 2, "0", STR_PAD_LEFT);
                $data =[
                    'type' => 'music',
                    "data"=> [
                        "url" => "https://r2share.simai.life/zgtai.com/mgs/" . str_pad($index, 2, "0", STR_PAD_LEFT) . ".mp3",
                        'title' => "($index/52)" . $item,
                        'description' => "罗门,我是好牧人",
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
