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
use Madcoda\Youtube\Facades\Youtube;
use Illuminate\Support\Facades\Redis;
final class Tpehoc{
	public function _invoke($keyword)
	{
        if($keyword == '799'){
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
            $image = 'https://zgtai.com/wp-content/uploads/Luo/luo-36.jpg';
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
                        'image' => $image,
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
            $image = 'https://zgtai.com/wp-content/uploads/Luo/luo-36.jpg';
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
                        'image' => $image,
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
        // 796 CCAC 袁惠鈞牧師
        // 7910 - 7912
        if(Str::startsWith($keyword,'791') && strlen($keyword) >= 3){
            $playLists = [
                ["id"=>"PLtrnB_vqQmqQObaA8ZCYc2Lig0NtuS5hH",'title'=>'聖經真的可信嗎'],
                ["id"=>"PLtrnB_vqQmqRBSltXAKvONWuSJp23hlye",'title'=>'箴言系列 - 智慧的話語'],
                ["id"=>"PLtrnB_vqQmqSsBabU-DuUUNnbhqCc4qiF",'title'=>'天地揭秘系列'],
            ];
            $oriKeyword = substr($keyword,0,3);
            $index = (int)substr($keyword, 3)%count($playLists);

            $playList = $playLists[$index];
            $playListId = $playList['id'];
            $playListTitle = $playList['title'];
            $cacheKey = "resources." . $keyword;
            $items = Cache::store('redis')->get($cacheKey, false);
            if(!$items){
                $url = "https://pub-3813a5d14cba4eaeb297a0dba302143c.r2.dev/playlist/{$playListId}/{$playListId}.txt";
                $response = Http::get($url);
                $ids = explode(PHP_EOL, $response->body());
                $items = [];
                
                foreach ($ids as $key => $yid) {
                    if(!$yid) continue;
                    $url = "https://pub-3813a5d14cba4eaeb297a0dba302143c.r2.dev/playlist/{$playListId}/{$yid}.info.json";
                    $json = Http::get($url)->json();
                    $tempItem['title'] = $json['title'];
                    $tempItem['thumbnail'] = $json['thumbnail'];
                    $tempItem['url'] = "{$yid}.mp4";
                    // "https://i.ytimg.com/vi/$vid/maxresdefault.jpg"
                    $tempItem['id'] = $json['id'];
                    $items[] = $tempItem;
                }
                Cache::store('redis')->put($cacheKey, $items);
            }
            $total = count($items);
            $index =  now()->format('z') % ($total);
            $item = $items[$index++];
            $data = [
                'type' => 'link',
                "data"=> [
                    "url" => env('R2_SHARE')."/playlist/{$playListId}/{$item['url']}",
                    'title' => "($index/$total)".$playListTitle,
                    'description' => "{$item['title']}",
                    'image' => $item['thumbnail'],
                    'vid' => $item['id'],
                ],
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'video',
            ];

            // Add audio
            $addition = $data;
            $addition['type'] = 'music';
            $addition['data']['url'] = str_replace('.mp4', '.m4a', $addition['data']['url']);
            $addition['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
            $data['addition'] = $addition;
            return $data;
        }

        // https://www.youtube.com/playlist?list=PL_sOpTJkyWnAbZRPaSktjlsv0_nH1K6aV
        // 有声书《西游记》精讲1-156
        // 79500-79501-79525
        if(Str::startsWith($keyword,'795') && strlen($keyword) >= 3){
            $playLists = [
                ["id"=>"PL_sOpTJkyWnAbZRPaSktjlsv0_nH1K6aV",'title'=>'西游记精讲'],
                ["id"=>"PL_sOpTJkyWnAeaM_DZvgXyHqJgt2xX7fV",'title'=>'汤姆叔叔的小屋'],
                ["id"=>"PL_sOpTJkyWnAH95cdSnLg6DNrylLsu4MA",'title'=>'简爱'],
                ["id"=>"PL_sOpTJkyWnB7WT3ukZVq92j47q3qDxdd",'title'=>'乌合之众'],
                ["id"=>"PL_sOpTJkyWnDWW_dE67EMwlNaCRB4SfFx",'title'=>'战争与和平'],
                ["id"=>"PL_sOpTJkyWnD-U9p6ykLtsO6M0ghTmeCW",'title'=>'昆虫记'],
                ["id"=>"PL_sOpTJkyWnB_abdRUng2s-SoOrw0xqB9",'title'=>'神曲'],
                ["id"=>"PL_sOpTJkyWnCDfIzb43w_ObbtjApRxAMR",'title'=>'日瓦戈医生'],
                ["id"=>"PL_sOpTJkyWnBQZXF3Dw_QqkILQEUeQIMh",'title'=>'围城'],
                ["id"=>"PL_sOpTJkyWnBvkuJzr8qIwoBT3w7OK_ul",'title'=>'骆驼祥子'],
                ["id"=>"PL_sOpTJkyWnBY_MpWusnEtwailn546EYV",'title'=>'呼啸山庄'],
                ["id"=>"PL_sOpTJkyWnCIa3R2IVUZJljWFfNLK8gf",'title'=>'双城计'],
                ["id"=>"PL_sOpTJkyWnDyMyzNd8apjrxvKNkEvgRx",'title'=>'雾都孤儿'],
                ["id"=>"PL_sOpTJkyWnBmIGMm6o0_zJ5bdReIeHR0",'title'=>'鲁滨逊漂流记'],
                ["id"=>"PL_sOpTJkyWnCAUkq4iDr1aMcvNZ2YR37w",'title'=>'巴黎圣母院'],
                ["id"=>"PL_sOpTJkyWnDZEt7aQo4LNHrz3eOcTqrd",'title'=>'包法力夫人'],
                ["id"=>"PL_sOpTJkyWnAtP1Nr4wUfCpKZwkcsRHxv",'title'=>'红与黑'],
                ["id"=>"PL_sOpTJkyWnDGEt9OfloRcP_ER8zPrrQE",'title'=>'灿烂千阳'],
                ["id"=>"PL_sOpTJkyWnDoy6jPjJBtXFT48zDW8eus",'title'=>'悲惨世界'],
                ["id"=>"PL_sOpTJkyWnBOGAy9suw_w3lT4da2tF_u",'title'=>'傲慢与偏见'],
                ["id"=>"PL_sOpTJkyWnAPhHAnA7E9fQBafVH4U4Vw",'title'=>'白夜行'],
                ["id"=>"PL_sOpTJkyWnByw3T9x59knNfOcaI8Ozmx",'title'=>'苔丝'],
                ["id"=>"PL_sOpTJkyWnCcp7hW1-Fwfm4rNFsr4GAF",'title'=>'复活'],
                ["id"=>"PL_sOpTJkyWnB9URu4zW8fJuKvNCDsrzqK",'title'=>'霍乱时期的爱情'],
                ["id"=>"PL_sOpTJkyWnCZohPj2g2jUozF8lswZ5so",'title'=>'百年孤独'],
                ["id"=>"PL_sOpTJkyWnCT7dwuRgQNdgkYAMzWXHdP",'title'=>'欧亨利'],

            ];
            // playlist='PL_sOpTJkyWnAeaM_DZvgXyHqJgt2xX7fV'
            // mkdir /tmp/r2/playlist/$playlist
            // /usr/local/bin/ydl -f 139 $playlist --write-info-json -o '/tmp/r2/playlist/%(playlist_id)s/%(id)s.%(ext)s' --split-chapters -o "chapter:/tmp/r2/playlist/%(playlist_id)s/%(id)s/%(section_number)s.%(ext)s"

            $oriKeyword = substr($keyword,1,3);
            $index = (int)substr($keyword, 4)%count($playLists);;

            $playList = $playLists[$index];
            $playListId = $playList['id'];
            $playListTitle = $playList['title'];

            $cacheKey = "resources." . $keyword;

            $items = Cache::store('redis')->get($cacheKey, false);
            if(!$items){
                $url = "https://pub-3813a5d14cba4eaeb297a0dba302143c.r2.dev/playlist/{$playListId}/{$playListId}.txt";
                $response = Http::get($url);
                $ids = explode(PHP_EOL, $response->body());
                $items = [];
                foreach ($ids as $key => $yid) {
                    if(!$yid) continue;
                    $url = "https://pub-3813a5d14cba4eaeb297a0dba302143c.r2.dev/playlist/{$playListId}/{$yid}.info.json";

                    $json = Http::get($url)->json();
                    $key = null;
                    foreach ($json['chapters'] as $key => $chapter) {
                        $index = $key+1;
                        $tempItem['title'] = $chapter['title'];
                        $tempItem['url'] = "{$yid}/{$index}.m4a";
                        $items[] = $tempItem;
                    }
                }
                Cache::store('redis')->put($cacheKey, $items);
            }
            $thumbnail = $json['thumbnail'];
            $total = count($items);
            $index =  now()->format('z') % ($total+1);
            $item = $items[$index];
            $data = [
                'type' => 'music',
                "data"=> [
                    "url" => env('R2_SHARE')."/playlist/{$playListId}/{$item['url']}",
                    'title' => "($index/$total)".$playListTitle,
                    'description' => "{$item['title']} By @LucyFM1999",
                    'image' => $thumbnail,
                ],
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
            return $data;
        }

        // 794 信心是一把梯子 救恩之聲
        if($keyword == '794'){
            $title = "信心是一把梯子";
            $desc = "救恩之聲 有聲書";
            $prefix = "https://www.vos.org.tw/Datafile/UploadFile/Voice/52/";
            $items = [    
                [
                    'title' => '幸好上帝沒答應',
                    'file' => '20211018145604.mp3'
                ],
                [
                    'title' => '量恩而為',
                    'file' => '20211018145727.mp3'
                ],
                [
                    'title' => '哦上帝不是故意的',
                    'file' => '20211018150104.mp3'
                ],
                [
                    'title' => '37度C的恩典',
                    'file' => '20180313114159.mp3'
                ],
                [
                    'title' => '貧心競氣',
                    'file' => '20180313114233.mp3'
                ],
                [
                    'title' => '傑出的歐巴桑',
                    'file' => '20180313114300.mp3'
                ],
                [
                    'title' => '印壞的郵票',
                    'file' => '20180313114323.mp3'
                ],
                [
                    'title' => '不要限定上帝賜福你的方式',
                    'file' => '20180313143659.mp3'
                ],
                [
                    'title' => '慢半拍的賜福',
                    'file' => '20180313143726.mp3'
                ],
                [
                    'title' => '我心靈得安寧',
                    'file' => '20180313143802.mp3'
                ],
                [
                    'title' => '怒火中消',
                    'file' => '20180313143824.mp3'
                ],
                [
                    'title' => '大智若娛',
                    'file' => '20180313143846.mp3'
                ],
                [
                    'title' => '簡單生活生活減擔',
                    'file' => '20180320102340.mp3'
                ],
                [
                    'title' => '清心寡鬱',
                    'file' => '20200921115539.mp3'
                ],
                [
                    'title' => '勞者多能',
                    'file' => '20200616153315.mp3'
                ],
                [
                    'title' => '沒有名次的考試',
                    'file' => '20200616153533.mp3'
                ],
                [
                    'title' => '你怎樣對待你的夢',
                    'file' => '20200616153657.mp3'
                ],
                [
                    'title' => '後天才子',
                    'file' => '20200616153817.mp3'
                ],
                [
                    'title' => '許一個雙B的人生',
                    'file' => '20200616153911.mp3'
                ],
                [
                    'title' => '用烏龜的精神作兔子',
                    'file' => '20200616154023.mp3'
                ],
                [
                    'title' => '優質的大男人主義',
                    'file' => '20200616154512.mp3'
                ],
                [
                    'title' => '不可叫人小看你年輕',
                    'file' => '20200616154708.mp3'
                ],
                [
                    'title' => '善良成大器',
                    'file' => '20200616154759.mp3'
                ],
                [
                    'title' => '讓愛你的人以你為榮',
                    'file' => '20200616154909.mp3'
                ],
                [
                    'title' => '下一盤人生的好棋',
                    'file' => '20200616155013.mp3'
                ],
                [
                    'title' => '小提琴物語',
                    'file' => '20200616155109.mp3'
                ],
                [
                    'title' => '另一種宣教',
                    'file' => '20200616155158.mp3'
                ],
                [
                    'title' => '品格是一種魅力',
                    'file' => '20200616155257.mp3'
                ],
                [
                    'title' => '上帝的馬賽克',
                    'file' => '20200616155359.mp3'
                ],
                [
                    'title' => '架子與價值',
                    'file' => '20200616155506.mp3'
                ],
                [
                    'title' => '熱情是金',
                    'file' => '20200616155558.mp3'
                ],
                [
                    'title' => '窮爸爸富遺產',
                    'file' => '20200616155658.mp3'
                ],
                [
                    'title' => '生氣時智商只有五歲',
                    'file' => '20200616155759.mp3'
                ],
                [
                    'title' => '一句話的重量',
                    'file' => '20200616155858.mp3'
                ],
                [
                    'title' => '另一種雙聲帶',
                    'file' => '20200616160821.mp3'
                ],
                [
                    'title' => '百善笑為先',
                    'file' => '20200616160921.mp3'
                ],
                [
                    'title' => '為批評繫上蝴蝶結',
                    'file' => '20200616161021.mp3'
                ],
                [
                    'title' => '英雄所見不同',
                    'file' => '20200616161114.mp3'
                ],
                [
                    'title' => '情緒的適放',
                    'file' => '20200616161210.mp3'
                ],
                [
                    'title' => '原來他也是人',
                    'file' => '20200731150734.mp3'
                ],
                [
                    'title' => '斜視與偏見',
                    'file' => '20200731151005.mp3'
                ],
                [
                    'title' => '是誰該死',
                    'file' => '20200731151208.mp3'
                ],
                [
                    'title' => '吵一場優質的架',
                    'file' => '20200731151327.mp3'
                ],
                [
                    'title' => '愛人太甚',
                    'file' => '20200731151428.mp3'
                ],
                [
                    'title' => '錦上不添炭雪中不送花',
                    'file' => '20200731151551.mp3'
                ],
                [
                    'title' => '強人所難',
                    'file' => '20200731151726.mp3'
                ],
                [
                    'title' => '最難復健的動作',
                    'file' => '20200731151822.mp3'
                ],
                [
                    'title' => '心是方向盤',
                    'file' => '20200731151923.mp3'
                ],
                [
                    'title' => '地瓜型人格',
                    'file' => '20200731152034.mp3'
                ],
                [
                    'title' => '如果少了您',
                    'file' => '20200731152212.mp3'
                ],
                [
                    'title' => '浪漫讓慢',
                    'file' => '20200731152732.mp3'
                ],
                [
                    'title' => '樂透了嗎',
                    'file' => '20200731152841.mp3'
                ],
                [
                    'title' => '理了髮的草坪',
                    'file' => '20200731152948.mp3'
                ],
                [
                    'title' => '候補第一的救主',
                    'file' => '20200731153717.mp3'
                ],
                [
                    'title' => '今日怒今日畢',
                    'file' => '20200731153901.mp3'
                ],
                [
                    'title' => '如果聖經是武林秘笈',
                    'file' => '20200731154040.mp3'
                ],
                [
                    'title' => '一二三木頭人',
                    'file' => '20200731154146.mp3'
                ],
                [
                    'title' => '天國的外交官',
                    'file' => '20200731154249.mp3'
                ],
                [
                    'title' => '恆行爸道',
                    'file' => '20200731154405.mp3'
                ],
                [
                    'title' => '當你以為沒人看見的時候',
                    'file' => '20200828135445.mp3'
                ],
                [
                    'title' => '天堂裡的委員會',
                    'file' => '20200828135706.mp3'
                ],
                [
                    'title' => '日劇白色巨塔片尾曲的由來',
                    'file' => '20200828135820.mp3'
                ],
                [
                    'title' => '心靈營養學',
                    'file' => '20200828140008.mp3'
                ],
                [
                    'title' => '月領三份薪',
                    'file' => '20200828140138.mp3'
                ],
                [
                    'title' => '十減一大於十',
                    'file' => '20200828140347.mp3'
                ],
                [
                    'title' => '天父必看顧你',
                    'file' => '20200921121750.mp3'
                ],
                [
                    'title' => '耶穌選總統',
                    'file' => '20200828140532.mp3'
                ],
                [
                    'title' => '惡人有惡福',
                    'file' => '20200828140853.mp3'
                ],
                [
                    'title' => '333生活處方',
                    'file' => '20200828140952.mp3'
                ],
                [
                    'title' => '祝福滿滿的人生',
                    'file' => '20200828141056.mp3'
                ],
                [
                    'title' => '情能補拙',
                    'file' => '20200828141425.mp3'
                ],
                [
                    'title' => '論家世背景',
                    'file' => '20200828141517.mp3'
                ],
                [
                    'title' => '中風的滑鼠',
                    'file' => '20180313113950.mp3'
                ],
            ];
            $image = 'https://www.vos.org.tw/Datafile/Icon/20180320152534135.png';

            $index = now()->format('z') % 72;
            $data =[
                'type' => 'music',
                "data"=> [
                    "url" => $prefix . $items[$index]['file'],
                    'title' => "(" . $index+1 . "/73)" . $title,
                    'description' => $items[$index]['title'] . $desc,
                    'image' => $image,
                ],
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
            return $data;
        }
        // 793 為兒女禱告40天 救恩之聲
        if($keyword == '793'){
            $title = "為兒女禱告40天";
            $desc = "救恩之聲 靈修禱告";
            $prefix = "https://www.vos.org.tw/Datafile/UploadFile/Voice/70/";
            $image = 'https://www.vos.org.tw/Datafile/Icon/20180424141532565.png';
            $items = [
                '20190122153048.mp3',
                '20190122153311.mp3',
                '20190122153321.mp3',
                '20190122153330.mp3',
                '20190122153401.mp3',
                '20190122153421.mp3',
                '20190122153447.mp3',
                '20190122153516.mp3',
                '20190122153531.mp3',
                '20190122153554.mp3',
                '20190122153604.mp3',
                '20190122153620.mp3',
                '20190122153630.mp3',
                '20190122153641.mp3',
                '20190122153658.mp3',
                '20190122153740.mp3',
                '20190122153753.mp3',
                '20190122153824.mp3',
                '20190122153845.mp3',
                '20190122153858.mp3',
                '20190122153921.mp3',
                '20190122153947.mp3',
                '20190122154037.mp3',
                '20190122154112.mp3',
                '20190122154123.mp3',
                '20190122154140.mp3',
                '20190122154151.mp3',
                '20190122154221.mp3',
                '20190122154250.mp3',
                '20190122154308.mp3',
                '20190122154334.mp3',
                '20190122154355.mp3',
                '20190122154427.mp3',
                '20190122154513.mp3',
                '20190122154528.mp3',
                '20190122154607.mp3',
                '20190122154626.mp3',
                '20190122154650.mp3',
                '20190122154706.mp3',
                '20190122154728.mp3'
            ];
            $index = now()->format('z') % 39;
            $data =[
                'type' => 'music',
                "data"=> [
                    "url" => $prefix . $items[$index++],
                    'title' => "($index/40)" . $title,
                    'description' => $desc,
                    'image' => $image,
                ],
                // 'addition'=>$addition,
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
            return $data;
        }
        // 792 加尔文-基督教教义研读
        // http://m.srsjdjt.com/nr.jsp?mid=27&groupId=215&_reqArgs=%7B%22args%22%3A%7B%22groupId%22%3A%22215%22%2C%22mid%22%3A%2227%22%7D%2C%22type%22%3A64%7D
        if($keyword == '792'){
            $title = "林慈信 加尔文《基督教教义》研读 ";
            $desc = "--";
            $prefix = env('R2_SHARE')."/resources/$keyword/";
            $image = env('R2_SHARE')."/resources/$keyword/$keyword.jpg";
            // 001=>110.mp3
            $count = 110;
            $items = ['卷101导论','卷102解答','卷103认识上帝与认识我们自己之间的关系','卷104对上帝的认识之性质及趋势','卷105对上帝的认识之性质及趋势','卷106—元论monism','卷107拜偶像就是背叛真神','卷108圣经论','卷109圣经论','卷110圣经论','卷111三一真神','卷112三一真神','卷113三一真神','卷114三一真神','卷115三一真神','卷116三一真神','卷117早期教会错误的教导','卷118早期教会错误的教导','卷201亚当的堕落，原罪论','卷202亚当的堕落，原罪论','卷203人现在被剥夺了意志自由，并处于悲惨的奴役下','卷204人现在被剥夺了意志自由，并处于悲惨的奴役下','卷205凡出于败坏的人性的，都得定罪','卷206上帝在人心中的运行','卷207旧约的约，新约的约同与不同；道德律','卷208道德律；律法的赐与，律法的功用','卷209律法的赐与，律法的功用','卷210a旧约，新约论，律法与福音，类似与差异','卷210b旧约，新约论，律法与福音','卷211旧约，新约论，律法与福音，类似与差异','卷212旧约，新约论，类似与差异','卷213基督论；为完成中保的任务，不得不降世为人，论基督的神性和人性','卷214基督论；为完成中保的任务，不得不降世为人，论基督的神性和人性','卷215基督论；神性人性的联合何以能组成中保的位格基督的三种任务，先知、君王和祭司。','卷216道德律；律法与福音','卷217道德律；律法与福音','卷218道德律','卷219道德律','卷220道德律；十戒的意义，接著律法认识神，接著律法认识自己','卷221道德律：十戒的意义，接著律法认识神，接着律法认识自己','卷222道德律；十戒','卷223道德律；十戒','卷224道德律；十戒','卷225道德律；十戒','卷226道德律；十戒','卷227上帝的吩咐，人的无能','卷228解答','卷229解答','卷301大纲：圣灵暗中的运行使有关基督的一切都成为我们的益惠','卷302信心的定义及其特性','卷303信心的定义及其特性','卷304信心的定义及其特性','卷305信心的定义及其特性','卷306信心的定义及其特性','卷307信心的定义及其特性','卷308因信重生，悔改','卷309因信重生，悔改','卷310因信重生，悔改','卷311因信重生，悔改','卷312因信重生，悔改','卷313论基督徒的生活，兼论圣经所提示的劝勉','卷314论基督徒的生活，兼论圣经所提示的劝勉','卷315基督徒的生活——克己','卷316背负十架乃是克己的一部分','卷317背负十架乃是克己的一部分：默念来生','卷318默念来生','卷319.因信称义之名与实的界说','卷320因信称义之名与实的界说','卷321因信称义之名与实的界说','卷322因信称义之名与实的界说','卷323因信称义之名与实的界说','卷324因信称义之名与实的界说','卷325驳斥罗马教徒反对因信称义说之谬论','卷326律法的应许与福音的应许之间的一致性','卷327论基督徒的自由','卷328论基督徒的自由','卷329论基督徒的自由','卷330预定与拣选，加尔文思想的背景','卷331预定与拣选，加尔文思想的背景','卷332预定','卷333预定与拣选','卷334预定与拣选','卷335斥诽谤预定论之谬说','卷336斥诽谤预定论之谬说','卷337拣选由神的呼召而证实。被弃绝者的灭亡是自己所招致的。','卷401大纲','卷402论真教会为众信徒之母，因而我们必须与之联合','卷403论真教会为众信徒之母，因而我们必须与之联合','卷404论真教会为众信徒之母，因而我们必须与之联合','卷405论真教会为众信徒之母，因而我们必须与之联合','卷406论教会的训戒及其对制裁和革除的主要用处','卷407论真教会为众信徒之母，因而我们必须与之联合','卷408教会，职务和职分','卷409教会，职务和职分','卷410教会，职务和职分','卷411教会，职务和职分','卷412教会，职务和职分','卷413教会，职务和职分','卷414救赎，称义，成圣','卷415圣礼','卷416圣礼','卷417圣礼','卷418圣礼','卷419洗礼','卷420洗礼','卷421洗礼','卷422圣餐及其所赐恩惠','卷423圣餐及其所飓恩惠','卷424圣餐及其所赐恩惠','卷425圣餐及其所赐恩惠'];
            $index = now()->format('z') % $count;//0-110 => 1->110
            $title = $items[$index++];
            $url = $prefix . str_pad($index, 3, '0', STR_PAD_LEFT)   . '.mp3';//61
            $data =[
                'type' => 'music',
                "data"=> [
                    "url" => $url,
                    'title' => '基督教要义-导读',
                    'description' => "($index/$count)" . $title,
                    'image' => $image,
                ],
                // 'addition'=>$addition,
            ];
            $data['statistics'] = [
                'metric' => class_basename(__CLASS__),
                "keyword" => $keyword,
                "type" => 'audio',
            ];
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
        // dd($keyword,urlencode('?'));// ? => %3F
        // $keyword = str_replace('?', "%3F", $keyword);
        Log::error(__CLASS__,[$keyword]);
        if(preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w\-]{11})/', $keyword, $matches)){
            $vid = $matches[1];
            $videoInfo = Youtube::getVideoInfo($vid);
            $title = $videoInfo->snippet->title;
            $mp4 = env('R2_SHARE')."/tmpshare/{$vid}.mp4";
            $mp3 = env('R2_SHARE')."/tmpshare/{$vid}.m4a";
            $image = "https://i.ytimg.com/vi/{$vid}/sddefault.jpg";
            $addition = [
                'type' => 'link',
                "data"=> [
                    "url" => $mp4,
                    'title' => $title,
                    'description' => '您请求的内容正在处理，5分钟后方可播放！',
                    'image' => $image,
                ],
                'statistics' => [
                    'metric' => 'youtube',
                    "keyword" => $vid,
                    "type" => 'audio',
                ],
            ];
            $data = [
                'type' => 'music',
                "data"=> [
                    "url" => $mp3,
                    'title' => $title,
                    'description' => '解析音频',
                ],
                'statistics' => [
                    'metric' => 'youtube',
                    "keyword" => $vid,
                    "type" => 'audio',
                ],
                'addition'=>$addition,
            ];
            
            return $data;
        }
        return null;
	}
}
