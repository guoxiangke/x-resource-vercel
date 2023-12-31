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
            return $this->getByDate();
            return $this->_getData();
        }
        if($keyword == 801){
            return $this->getByDate();
            $data = $this->_getData();
            $vid = $data['data']['vid'];
            $data['data']['url'] = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".mp4";

            // Add audio
            $m4a = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".m4a";
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
            $newData = $data['addition'];
            unset($data['addition']);
            $newData['addition'] = $data;
            return $newData;
        }
        // 周日的
        if($keyword == 802){
            // if($day = now()->dayOfWeek()==0){
                $data = $this->_getLastSundayData();

                $vid = $data['data']['vid'];
                $data['data']['url'] = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".mp4";

                // Add audio
                $m4a = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".m4a";
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
                $newData = $data['addition'];
                unset($data['addition']);
                $newData['addition'] = $data;
                return $newData;
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

                // 主日信息模版
                // 20230528主日信息：信靠神！祂必供应 -- 卢乾坤牧师
                // dd($lastSundayIndex, $lastSundayTitle,$yesterdayIndex,$yesterdayTitle);
                if($lastSundayTitle){
                    $vid = $matches[1][$lastSundayIndex];
                    $descs = explode('：',$lastSundayTitle);
                    $data['addition'] = [
                        'type' => 'link',
                        'data' => [
                            "url" => "https://www.youtube.com/embed/{$vid}",
                            'title' => $descs[0]??'',//"主日信息-{$day}" ,
                            'description' => $descs[1]??'',//$lastSundayTitle,
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
                        'title' => $descs[0]??"",//"主日信息-{$day}" ,
                        'description' => $descs[1]??"",//$lastSundayTitle,
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

    private function getByDate()
    {
        $items = [
            "0101"=>["vid"=>"FdYvOwM9spQ","title"=>"有晚上，有早晨"],
            "0102"=>["vid"=>"csR5A3RpCGc","title"=>"与神同行"],
            "0103"=>["vid"=>"vFMpSfaxeh0","title"=>"神以彩虹为约"],
            "0104"=>["vid"=>"E2SEk4g15uw","title"=>"对神的话深信不疑"],
            "0105"=>["vid"=>"0n4g3fnoSw0","title"=>"心灵的割礼"],
            "0106"=>["vid"=>"Wuz3qeztTu0","title"=>"看顾人的上帝"],
            "0107"=>["vid"=>"LEHPu3wHJJ4","title"=>"耶和华以勒"],
            "0108"=>["vid"=>"_PEEF6DRJ5A","title"=>"耶和华使我们宽阔"],
            "0109"=>["vid"=>"iYc_DT-LsuQ","title"=>"耶和华真在这里"],
            "0110"=>["vid"=>"OpOLWrXncm8","title"=>"面对面遇见神"],
            "0111"=>["vid"=>"fjKHHxQ4r6c","title"=>"起来！上伯特利去"],
            "0112"=>["vid"=>"h0bAe9AaR6k","title"=>"耶和华试验人"],
            "0113"=>["vid"=>"GqO4nrHnVyA","title"=>"没有义人，连一个都没有"],
            "0114"=>["vid"=>"hkYR9Psrv08","title"=>"从玛拿西到以法莲"],
            "0115"=>["vid"=>"4llFhesVMfw","title"=>"敬畏主，远离恶"],
            "0116"=>["vid"=>"wSCxe7ACQHY","title"=>"上帝保全我们"],
            "0117"=>["vid"=>"sZRfUx4jLOc","title"=>"神的意思是好的"],
            "0118"=>["vid"=>"T8-lfxf3Z50","title"=>"出埃及记01-03章—萧旭"],
            "0119"=>["vid"=>"dfD8V-Rn5N0","title"=>"出埃及记04-06章—萧旭"],
            "0120"=>["vid"=>"xUtDLl9o8P8","title"=>"出埃及记07-09章—萧旭"],
            "0121"=>["vid"=>"KKhAO2ZUJB4","title"=>"出埃及记10-12章—萧旭"],
            "0122"=>["vid"=>"MymIoOMplZM","title"=>"出埃及记13-15章—萧旭"],
            "0123"=>["vid"=>"zFghQjUz7HQ","title"=>"出埃及记16-18章—萧旭"],
            "0124"=>["vid"=>"nIPCV-VnvdM","title"=>"出埃及记19-21章—萧旭"],
            "0125"=>["vid"=>"XBwV11F63aA","title"=>"出埃及记22-24章—萧旭"],
            "0126"=>["vid"=>"I9NFdsqz718","title"=>"出埃及记25-27章—萧旭"],
            "0127"=>["vid"=>"fq0a0FVoHOs","title"=>"出埃及记28-29章—萧旭"],
            "0128"=>["vid"=>"sc1jA9URiOY","title"=>"出埃及记30-32章—萧旭"],
            "0129"=>["vid"=>"I0-GaHqf0rg","title"=>"出埃及记33-35章—萧旭"],
            "0130"=>["vid"=>"zjDZQNjutnc","title"=>"出埃及记36-38章—萧旭"],
            "0131"=>["vid"=>"kMC6xgZuUPU","title"=>"出埃及记39-40章—萧旭"],
            "0201"=>["vid"=>"HbubIZzblvw","title"=>"利未记01-03章—金雪峰"],
            "0202"=>["vid"=>"T5mwRjjsd84","title"=>"利未记04-05章—金雪峰"],
            "0203"=>["vid"=>"UUEl7M-7EUE","title"=>"利未记06-07章—金雪峰"],
            "0204"=>["vid"=>"a1xhCTnHxCg","title"=>"利未记08-10章—金雪峰"],
            "0205"=>["vid"=>"cBD1Bdd7aDs","title"=>"利未记11-15章—金雪峰"],
            "0206"=>["vid"=>"mzIr8An-E6k","title"=>"刻苦己心，全民赎罪"],
            "0207"=>["vid"=>"61rovoJMPHE","title"=>"行在圣洁中"],
            "0208"=>["vid"=>"TENdKf4UGo4","title"=>"认识自己的本相"],
            "0209"=>["vid"=>"nnej_IpqKEg","title"=>"燃灯发光，共饮灵粮"],
            "0210"=>["vid"=>"j7VI23RtmZc","title"=>"甘心还愿，乐意奉献"],
            "0211"=>["vid"=>"2TE0dYeN3VQ","title"=>"数点自己的生命—孙力"],
            "0212"=>["vid"=>"grBJtrsohSo","title"=>"照神的规矩行—孙力"],
            "0213"=>["vid"=>"rnu7z38Pnpo","title"=>"奉献的意义—孙力"],
            "0214"=>["vid"=>"cqq9JcsecSE","title"=>"你的云柱火柱是什么—孙力"],
            "0215"=>["vid"=>"zKPOXsSVrgk","title"=>"知足与抱怨—孙力"],
            "0216"=>["vid"=>"oCusLLF3fjE","title"=>"用神的视角看问题—孙力"],
            "0217"=>["vid"=>"ZCezmpGbTX8","title"=>"耶和华必指示谁是属祂的"],
            "0218"=>["vid"=>"e1LM0Xm_mOw","title"=>"仰望铜蛇得医治"],
            "0219"=>["vid"=>"KBFpxvWeByQ","title"=>"巴兰错在哪里"],
            "0220"=>["vid"=>"6zOqjQq4nUE","title"=>"他们必要死在旷野"],
            "0221"=>["vid"=>"iQiQZu-eQeA","title"=>"心中有圣灵"],
            "0222"=>["vid"=>"1ODyv0MQt6E","title"=>"你们的罪必追上你们"],
            "0223"=>["vid"=>"w4bwVguy_-w","title"=>"牢记与遗忘"],
            "0224"=>["vid"=>"jK0AgxqVecw","title"=>"各守各的产业"],
            "0225"=>["vid"=>"61RqY6CDZ5c","title"=>"重新认识恩典"],
            "0226"=>["vid"=>"enI5yWqzEjU","title"=>"重申十条诫"],
            "0227"=>["vid"=>"pj73w47tXyY","title"=>"以实际生活来表明"],
            "0228"=>["vid"=>"7rQkbRK70xM","title"=>"守住蒙恩的地位"],
            "0301"=>["vid"=>"HnEqTSHx5ZI","title"=>"施行豁免"],
            "0302"=>["vid"=>"J1ESt2NWTHA","title"=>"不可献上残缺的"],
            "0303"=>["vid"=>"zGMdOMGM2qY","title"=>"预备道路，设立逃城"],
            "0304"=>["vid"=>"GWFfjd4D88E","title"=>"神顾念我们"],
            "0305"=>["vid"=>"V_tgv_VP-Z0","title"=>"祝福与咒诅"],
            "0306"=>["vid"=>"JgAfVGOGl78","title"=>"变咒诅为祝福"],
            "0307"=>["vid"=>"ulyatuIFgOQ","title"=>"神疼爱我们"],
            "0308"=>["vid"=>"GEEPJtbbnis","title"=>"现在你要起来"],
            "0309"=>["vid"=>"myAFazJsHH4","title"=>"信心的呼喊"],
            "0310"=>["vid"=>"iD4EXINdsic","title"=>"亚割谷的教训"],
            "0311"=>["vid"=>"y1HBa8NSig4","title"=>"没有求问耶和华"],
            "0312"=>["vid"=>"Mhvve4cyCLs","title"=>"还有许多未得之地"],
            "0313"=>["vid"=>"dHxm_teq7Fc","title"=>"南地与水泉"],
            "0314"=>["vid"=>"lgk6YnhlToI","title"=>"进入逃城"],
            "0315"=>["vid"=>"0dl1XaVI5m0","title"=>"立坛为证"],
            "0316"=>["vid"=>"TPJmBIWDYXE","title"=>"约书亚的领袖特质"],
            "0317"=>["vid"=>"KveU4ymJmyE","title"=>"从吉甲到波金"],
            "0318"=>["vid"=>"uhUGgahgShI","title"=>"女先知底波拉"],
            "0319"=>["vid"=>"2lbVwTNdFc4","title"=>"我们的兵器"],
            "0320"=>["vid"=>"KfcRqGBACEc","title"=>"荆棘作王"],
            "0321"=>["vid"=>"jWVbM8wbviw","title"=>"神无条件的拣选"],
            "0322"=>["vid"=>"oHVJZsE1BeY","title"=>"无知的失败"],
            "0323"=>["vid"=>"5qphHugfp58","title"=>"各人任意而行"],
            "0324"=>["vid"=>"_uuZRN7Bzxw","title"=>"缺了一个支派"],
            "0325"=>["vid"=>"1djM6z8gZbs","title"=>"路得的赏赐"],
            "0326"=>["vid"=>"QZbg-Mc7s7k","title"=>"撒母耳记上01-03章—萧旭"],
            "0327"=>["vid"=>"Y94lXk8wruQ","title"=>"撒母耳记上04-06章—萧旭"],
            "0328"=>["vid"=>"qCDaGFpZD6U","title"=>"撒母耳记上07-09章—萧旭"],
            "0329"=>["vid"=>"4Hmoli8SXj4","title"=>"撒母耳记上10-12章—萧旭"],
            "0330"=>["vid"=>"6ycsrcCPMhw","title"=>"撒母耳记上13-15章—萧旭"],
            "0331"=>["vid"=>"hP8aNBBUPTw","title"=>"撒母耳记上16-18章—萧旭"],
            "0401"=>["vid"=>"mNRaqSpidY4","title"=>"撒母耳记上19-21章—萧旭"],
            "0402"=>["vid"=>"auFXES7KF-k","title"=>"撒母耳记上22-24章—萧旭"],
            "0403"=>["vid"=>"-XeHaYepTK4","title"=>"撒母耳记上25-27章—萧旭"],
            "0404"=>["vid"=>"Gq595YO7YSA","title"=>"撒母耳记上28-31章—萧旭"],
            "0405"=>["vid"=>"XUb5Ad1YRM4","title"=>"撒母耳记下01-03章—萧旭"],
            "0406"=>["vid"=>"a3hSZBfiS74","title"=>"撒母耳记下04-06章—萧旭"],
            "0407"=>["vid"=>"BH32ej3DNl4","title"=>"撒母耳记下07-11章（上）—萧旭"],
            "0408"=>["vid"=>"M9KCo1TFjYc","title"=>"撒母耳记下07-11章（下）—萧旭"],
            "0409"=>["vid"=>"nB9LYFYULHs","title"=>"撒母耳记下12-13章—萧旭"],
            "0410"=>["vid"=>"gp5jCCavPFc","title"=>"撒母耳记下14-15章—萧旭"],
            "0411"=>["vid"=>"hFX3rfE0A1k","title"=>"撒母耳记下16-18章—萧旭"],
            "0412"=>["vid"=>"7jsNrXMlE08","title"=>"撒母耳记下19-20章—萧旭"],
            "0413"=>["vid"=>"LApzN-MB1Mg","title"=>"撒母耳记下21-22章—萧旭"],
            "0414"=>["vid"=>"z7MLPpGOfjU","title"=>"撒母耳记下23-24章—萧旭"],
            "0415"=>["vid"=>"_1SaaeActas","title"=>"语言的杀伤力–经文：列王纪上第1章"],
            "0416"=>["vid"=>"40_baqoPgjE","title"=>"让基督作王–经文：列王纪上第2章"],
            "0417"=>["vid"=>"ySSYEinoVU0","title"=>"草根的服侍–经文：列王纪上第3章"],
            "0418"=>["vid"=>"Eu6ct6RS9L8","title"=>"荣耀背后的危机–经文：列王纪上第4章"],
            "0419"=>["vid"=>"Znbwr6–uUjs","title"=>"信仰的无力感经文:列王纪上第5章"],
            "0420"=>["vid"=>"0e11ZINTQgE","title"=>"伪装的实力–经文：列王纪上第14–15章"],
            "0421"=>["vid"=>"NLrE4zyqAa8","title"=>"不同的服侍–经文：列王纪上第16–18章"],
            "0422"=>["vid"=>"CphNAOykQ7c","title"=>"投靠神才是出路–经文：列王纪上第19–20章"],
            "0423"=>["vid"=>"OAjCQFxndSo","title"=>"真先知和假先知–经文：列王纪上第21–22章"],
            "0424"=>["vid"=>"X6ey54u2QYc","title"=>"这谷必满了水–经文：列王纪下第1–3章"],
            "0425"=>["vid"=>"xh7G9aO8dYk","title"=>"向空器皿倒油–经文：列王纪下第4–5章"],
            "0426"=>["vid"=>"31Dz8mW0dj8","title"=>"信是得着就必得着–经文：列王纪下第6–8章"],
            "0427"=>["vid"=>"ViHcSPiMJjw","title"=>"你平安吗？–经文：列王纪下第9–10章"],
            "0428"=>["vid"=>"SrFs8zbnbdY","title"=>"信心的考验–经文：列王纪下第11–13章"],
            "0429"=>["vid"=>"Qj6ql4UZ1W0","title"=>"晚节不保的亚玛谢–经文：列王纪下第14–15章"],
            "0430"=>["vid"=>"zsXHtAWCd2c","title"=>"倚靠神的希西家–经文：列王纪下第16–18章"],
            "0501"=>["vid"=>"IY6_kiNZc9c","title"=>"因祈祷脱困–经文：列王纪下第19–21章"],
            "0502"=>["vid"=>"6MXMOIXzYaA","title"=>"不偏左右–经文：列王纪下第22–23章"],
            "0503"=>["vid"=>"gM73OIvfX9o","title"=>"君王与国运–经文：列王纪下第24–25章"],
            "0504"=>["vid"=>"YhzwGEeDHFA","title"=>"透过家谱重整信仰–经文：历代志上第1–3章"],
            "0505"=>["vid"=>"Seusph3eQkw","title"=>"在主殿中侍奉–经文：历代志上第4–6章"],
            "0506"=>["vid"=>"88UpsbNrshg","title"=>"紧要的职任–经文：历代志上第7–9章"],
            "0507"=>["vid"=>"s_BE9mUT0gs","title"=>"大衮庙中–经文：历代志上第10–12章"],
            "0508"=>["vid"=>"1dT3dvBmE2A","title"=>"迎请约柜–经文：历代志上第13–16章"],
            "0509"=>["vid"=>"AfdMpkp–AYg","title"=>"听命胜于建殿–经文：历代志上第17–19章"],
            "0510"=>["vid"=>"IUOI7kSx0Ks","title"=>"防不胜防–经文：历代志上第20–23章"],
            "0511"=>["vid"=>"d1g7–Dcm8Dc","title"=>"称颂耶和华–经文：历代志上第24–26章"],
            "0512"=>["vid"=>"NothgJe_rM0","title"=>"殷殷嘱托–经文：历代志上第27–29章"],
            "0513"=>["vid"=>"PXnAMw4xo9U","title"=>"得神赐福的属灵原则–经文：历代志下第1–4章"],
            "0514"=>["vid"=>"zAp1YXUwKsc","title"=>"抬约柜入圣殿–经文：历代志下第5–7章"],
            "0515"=>["vid"=>"WMEdZDgmBis","title"=>"荣耀背后的原因–经文：历代志下第8–10章"],
            "0516"=>["vid"=>"Pcpa5s1kqfQ","title"=>"金盾牌变铜盾牌–经文：历代志下第11–14章"],
            "0517"=>["vid"=>"Te8JwUx–7CU","title"=>"站队很重要–经文：历代志下第15–18章"],
            "0518"=>["vid"=>"og2SkjrNyt0","title"=>"赞美蒙福、祈祷胜敌–经文：历代志下第19–21章"],
            "0519"=>["vid"=>"Q_–aiEsDkk4","title"=>"我们与谁为伍？–经文：历代志下第22–24章"],
            "0520"=>["vid"=>"MuqGs2VWQkw","title"=>"心高气傲，以致行事邪僻–经文：历代志下第25–27章"],
            "0521"=>["vid"=>"BCD6bLvTPZQ","title"=>"正道与恶道–经文：历代志下第28–29章"],
            "0522"=>["vid"=>"R9D5L8Qg5EU","title"=>"专一仰望蒙拯救–经文：历代志下第30–32章"],
            "0523"=>["vid"=>"QE2V1ZseISE","title"=>"约西亚的榜样–经文：历代志下第33–36章"],
            "0524"=>["vid"=>"E–Ynl428lGc","title"=>"经文：以斯拉记第1–3章—金雪峰传道"],
            "0525"=>["vid"=>"TPa_EqfLBuE","title"=>"经文：以斯拉记第4–6章—金雪峰传道"],
            "0526"=>["vid"=>"eWCPXNl89Oo","title"=>"经文：以斯拉记第7–8章—金雪峰传道"],
            "0527"=>["vid"=>"PkxYPISYw30","title"=>"经文：以斯拉记第9–10章—金雪峰传道"],
            "0528"=>["vid"=>"liaJqjtHEOc","title"=>"经文：尼希米记第1–4章—金雪峰传道"],
            "0529"=>["vid"=>"kYmmFXKkzsA","title"=>"经文：尼希米记第5–7章—金雪峰传道"],
            "0530"=>["vid"=>"6GE8qpFPjOE","title"=>"经文：尼希米记第8–10章—金雪峰传道"],
            "0531"=>["vid"=>"eZLw8pA3_6E","title"=>"经文：尼希米记第10–13章—金雪峰传道"],
            "0601"=>["vid"=>"A7wAzRpBL2s","title"=>"经文：以斯贴记第1–4章—金雪峰传道"],
            "0602"=>["vid"=>"tFtoYhyo3tw","title"=>"经文：以斯帖记第5–7章—金雪峰传道"],
            "0603"=>["vid"=>"uiqhZ_rFZLA","title"=>"经文：以斯帖记第8–10章—金雪峰传道"],
            "0604"=>["vid"=>"Ek6zv5GyHvk","title"=>"经文：约伯记第1–6章—萧旭老师"],
            "0605"=>["vid"=>"S31PHkbgn94","title"=>"经文：约伯记第7–11章—萧旭老师"],
            "0606"=>["vid"=>"WyCY3Yx42aI","title"=>"经文：约伯记第12–15章—萧旭老师"],
            "0607"=>["vid"=>"Dyagv9yjJ1o","title"=>"经文：约伯记第16–20章—萧旭老师"],
            "0608"=>["vid"=>"C0k4–1nQCFU","title"=>"经文：约伯记第21–25章—萧旭老师"],
            "0609"=>["vid"=>"–F6WM6oUSyQ","title"=>"经文：约伯记第26–30章—萧旭老师"],
            "0610"=>["vid"=>"CUkrXDgDPNQ","title"=>"经文：约伯记第31–34章—萧旭老师"],
            "0611"=>["vid"=>"cs6oRHMRT10","title"=>"经文：约伯记第35–38章—萧旭老师"],
            "0612"=>["vid"=>"k5C8CeYf_Vo","title"=>"经文：约伯记第39–42章—萧旭老师"],
            "0613"=>["vid"=>"YqjvM2wE3rE","title"=>"拥戴耶稣为王–经文：诗篇第1–9篇"],
            "0614"=>["vid"=>"m6yR––jJ–S0","title"=>"从叹息到歌唱–经文：诗篇第10–18篇"],
            "0615"=>["vid"=>"Zjpxx–eVtXY","title"=>"奇妙之道–经文：诗篇第19–25篇"],
            "0616"=>["vid"=>"WL_TqvcZ2JE","title"=>"向来行事纯全–经文：诗篇第26–31篇"],
            "0617"=>["vid"=>"FN7d4oDsFYA","title"=>"赦罪之福与喜乐之道–经文：诗篇第32–37篇"],
            "0618"=>["vid"=>"rzAH2_1nvso","title"=>"追求良善–经文：诗篇第38–44篇"],
            "0619"=>["vid"=>"f–1aGHADLu8","title"=>"大卫的悔罪诗–经文：诗篇第45–51篇"],
            "0620"=>["vid"=>"IYtJvQ3lDf8","title"=>"除去诡诈的舌头–经文：诗篇第52–59篇"],
            "0621"=>["vid"=>"eFxeWk8m_Xw","title"=>"靠神反败为胜–经文：诗篇第60–68篇"],
            "0622"=>["vid"=>"RbmDv1zMoyc","title"=>"清心之人的祷告–经文：诗篇第69–73篇"],
            "0623"=>["vid"=>"elGaw–hIE_E","title"=>"走出抑郁–经文：诗篇第74–78篇"],
            "0624"=>["vid"=>"2EvK3znwc_s","title"=>"有福的人–经文：诗篇第79–85篇"],
            "0625"=>["vid"=>"X1F1ZywhEkw","title"=>"智慧的人生–经文：诗篇第86–90篇"],
            "0626"=>["vid"=>"mlA0qHo4u9M","title"=>"住在至高者的隐密处–经文：诗篇第91–98篇"],
            "0627"=>["vid"=>"uL_kwMtgzKE","title"=>"敬拜与侍奉–经文：诗篇第99–104篇"],
            "0628"=>["vid"=>"X_pGoc63h40","title"=>"健康的灵命–经文：诗篇第105–107篇"],
            "0628"=>["vid"=>"GIx558nxGos","title"=>"健康的灵命–经文：诗篇第105–107篇"],
            "0629"=>["vid"=>"dYh5_wVCWoc","title"=>"倚靠神施展大能–经文：诗篇第108–115篇"],
            "0630"=>["vid"=>"GYOLj2EsdkU","title"=>"行活人之路–经文：诗篇第116–119篇"],
            "0701"=>["vid"=>"iSWkK–TKJAc","title"=>"我们的帮助从神而来–经文：诗篇第120–134篇"],
            "0702"=>["vid"=>"6APhC6EB2_0","title"=>"祂的慈爱永远长存–经文：诗篇第136–142篇"],
            "0703"=>["vid"=>"rLBVs44Hkt8","title"=>"赞美耶和华–经文：诗篇第143–150篇"],
            "0704"=>["vid"=>"jOJXAJ_VDOg","title"=>"经文：箴言1–4章—宁政牧师"],
            "0705"=>["vid"=>"6Iw3HS5w1Bk","title"=>"经文：箴言5–8章—宁政牧师"],
            "0706"=>["vid"=>"4tvRr3k5cLk","title"=>"经文：箴言9–13章—宁政牧师"],
            "0707"=>["vid"=>"GWm_W6hK93I","title"=>"经文：箴言14–18章—宁政牧师"],
            "0708"=>["vid"=>"4L9xMOKtMsg","title"=>"经文：箴言19–23章—宁政牧师"],
            "0709"=>["vid"=>"Ql_zM319zUk","title"=>"经文：箴言24–27章—宁政牧师"],
            "0710"=>["vid"=>"8PuZO3hDY3M","title"=>"经文：箴言28–31章—宁政牧师"],
            "0711"=>["vid"=>"Dc3–jcZrfFo","title"=>"经文：传道书1–3章—宁政牧师"],
            "0712"=>["vid"=>"zPyFdCNBf1k","title"=>"经文：传道书4–7章—宁政牧师"],
            "0713"=>["vid"=>"fYwL2lN7iS4","title"=>"经文：传道书8–12章—宁政牧师"],
            "0714"=>["vid"=>"4TClIPHGv9g","title"=>"与神独处的生活–经文：雅歌第1–4章"],
            "0715"=>["vid"=>"vgHbKI3sYPM","title"=>"一切为良人–经文：雅歌第5–8章"],
            "0716"=>["vid"=>"3SAFnwrp_y0","title"=>"《以赛亚书》导论–经文：以赛亚书第1–5章"],
            "0717"=>["vid"=>"EzEkfxE9bXc","title"=>"再思以马内利–经文：以赛亚书第6–9章"],
            "0718"=>["vid"=>"jlJCTUdTR0w","title"=>"弥赛亚的国度–经文：以赛亚书第10–13章"],
            "0719"=>["vid"=>"MuM8NbAnN5I","title"=>"骄傲变魔鬼–经文：以赛亚书第14–17章"],
            "0720"=>["vid"=>"5SGGNjUNKSY","title"=>"为自己安身–经文：以赛亚书第18–22章"],
            "0721"=>["vid"=>"1m2HLKBN4_E","title"=>"谁赐下冠冕？–经文：以赛亚书第23–26章"],
            "0722"=>["vid"=>"9CNkYtriYew","title"=>"贴近祂的心–经文：以赛亚书第27–29章"],
            "0723"=>["vid"=>"FP3nKg_MgaU","title"=>"无往而不利–经文：以赛亚书第30–33章"],
            "0724"=>["vid"=>"wDKk–qVQwaQ","title"=>"把自己和上帝绑在一起–经文：以赛亚书第34–36章"],
            "0725"=>["vid"=>"h7PP8KI_BRA","title"=>"我们为谁活着？–经文：以赛亚书第37–41章"],
            "0726"=>["vid"=>"WaaoQX6D0oQ","title"=>"神的仆人–经文：以赛亚书第42–44章"],
            "0727"=>["vid"=>"uZFQceOwgCI","title"=>"熬炼与拣选–经文：以赛亚书第45–48章"],
            "0728"=>["vid"=>"6dKTNxVuAII","title"=>"你是我的百姓–经文：以赛亚书第49–51章"],
            "0729"=>["vid"=>"Dx–vfZkCeRk","title"=>"在祂手中亨通–经文：以赛亚书第52–55章"],
            "0730"=>["vid"=>"8VtJA82qcmM","title"=>"耶和华必应允–经文：以赛亚书第56–59章"],
            "0731"=>["vid"=>"RsEgXFyyMo8","title"=>"饮于能力之源–经文：以赛亚书第60–63章"],
            "0801"=>["vid"=>"PjprejcqE0c","title"=>"先知最后的训言–经文：以赛亚书第64–66章"],
            "0802"=>["vid"=>"DOcao–nMiZI","title"=>"一根杏树枝–经文：耶利米书第1–3章"],
            "0803"=>["vid"=>"bsBv4–taACE","title"=>"实在是苦–经文：耶利米书第4–5章"],
            "0804"=>["vid"=>"pUsATsLK8LM","title"=>"访问古道–经文：耶利米书第6–7章"],
            "0805"=>["vid"=>"cK_KVHZnuPU","title"=>"认识主，真可夸–经文：耶利米书第8–10章"],
            "0806"=>["vid"=>"YrKh_Q3_yFA","title"=>"当听从遵行这约的话–经文：耶利米书第11–13章"],
            "0807"=>["vid"=>"cPsddRFz3cY","title"=>"有求不应–经文：耶利米书第14–16章"],
            "0808"=>["vid"=>"2qJlE4cSQeQ","title"=>"主是窑匠我是泥土–经文：耶利米书第17–19章"],
            "0809"=>["vid"=>"o5XonCGGNpw","title"=>"玛歌珥米撒毕–经文：耶利米书第20–22章"],
            "0810"=>["vid"=>"HNflwGGwrLA","title"=>"耶和华我们的义–经文：耶利米书第23–25章"],
            "0811"=>["vid"=>"CsVkjllj8pk","title"=>"辨别真假先知–经文：耶利米书第26–28章"],
            "0812"=>["vid"=>"K–qp0PliZJc","title"=>"另立新约–经文：耶利米书第29–31章"],
            "0813"=>["vid"=>"XlrIzsTo6nA","title"=>"谋事有大略，行事有大能–经文：耶利米书第32–33章"],
            "0814"=>["vid"=>"qj72jei6FPU","title"=>"毁约者与守约者–经文：耶利米书第34–36章"],
            "0815"=>["vid"=>"qrU5Pyq8hEU","title"=>"三问三答–经文：耶利米书第37–39章"],
            "0816"=>["vid"=>"morhBzsB5ok","title"=>"真主意假商量–经文：耶利米书第40–42章"],
            "0817"=>["vid"=>"LdKJz–kYkCk","title"=>"彼此扶持–经文：耶利米书第43–46章"],
            "0818"=>["vid"=>"loO5B0qvvH0","title"=>"天下万国的神–经文：耶利米书第47–49章"],
            "0819"=>["vid"=>"2mtnG03sBNc","title"=>"耶利米的话到此为止–经文：耶利米书第50–52章"],
            "0820"=>["vid"=>"PqHN7NK_IV4","title"=>"看我的痛苦–经文：日耶利米哀歌1–2章"],
            "0821"=>["vid"=>"Shcl9gWQrUY","title"=>"每早晨这都是新的–经文：耶利米哀歌第3–5章"],
            "0822"=>["vid"=>"HJmvO5HTnHI","title"=>"作以色列家守望的人–经文：以西结书第1–3章"],
            "0823"=>["vid"=>"8aFkzf294do","title"=>"你就知道我是耶和华—经文：以西结书第4–7章"],
            "0824"=>["vid"=>"p34Zqm9YH4g","title"=>"荣耀离开以色列了–经文：以西结书第8–11章"],
            "0825"=>["vid"=>"z9B4557yw5Q","title"=>"其实上帝并没有说–经文：以西结书第12–14章"],
            "0826"=>["vid"=>"HD5xWnfN6XU","title"=>"你一切所行的倒比她们更坏–经文：以西结书第15–16章"],
            "0827"=>["vid"=>"pNydRJmXaQ0","title"=>"为父的怎样属我，为子的也照样属我–经文：以西结书第17–19章"],
            "0828"=>["vid"=>"dM3R0km91zU","title"=>"顺从与遵行–经文：以西结书第20–21章"],
            "0829"=>["vid"=>"TWKQnaeJvlc","title"=>"堵住破口–经文：以西结书第22–23章"],
            "0830"=>["vid"=>"NBW9k–RfG9Q","title"=>"推罗的荒凉–经文：以西结书第24–26章"],
            "0831"=>["vid"=>"0MsjTb6XNnw","title"=>"忽然临到–经文：以西结书第27–29章"],
            "0901"=>["vid"=>"ec0fUqH_m9Q","title"=>"骄傲归于无有–经文：以西结书第30–32章"],
            "0902"=>["vid"=>"f_fB1Hby2hA","title"=>"坏牧人与好牧人–经文：以西结书第33–34章"],
            "0903"=>["vid"=>"Tj_rzy4Sxwo","title"=>"新心与新灵–经文：以西结书第35–37章"],
            "0904"=>["vid"=>"CrDSpkj9RRQ","title"=>"他把我带到以色列地–经文：以西结书第38–40章"],
            "0905"=>["vid"=>"0zRrNQr2cVo","title"=>"有利未人远离我–经文：以西结书第41–44章"],
            "0906"=>["vid"=>"pkmgPGfPSn4","title"=>"生命河–经文：以西结书第45–48章"],
            "0907"=>["vid"=>"t2vVpW9ahbI","title"=>"经文：但以理书第1–2章—萧旭弟兄"],
            "0908"=>["vid"=>"pNuReHskgLk","title"=>"经文：但以理书第3–4章—萧旭弟兄"],
            "0909"=>["vid"=>"smhh4CzlAGM","title"=>"经文：但以理书第5–6章—萧旭弟兄"],
            "0910"=>["vid"=>"KEoPu8xiD08","title"=>"经文：但以理书第7–9章—萧旭弟兄"],
            "0911"=>["vid"=>"iiy_bKgNoEw","title"=>"经文：但以理书第10–12章—萧旭弟兄"],
            "0912"=>["vid"=>"6xgpVXcjIi4","title"=>"我必聘你永远归我–经文：何西阿书第1–4章"],
            "0913"=>["vid"=>"HQ5viyR2ZWA","title"=>"务要认识耶和华–经文：何西阿书第5–9章"],
            "0914"=>["vid"=>"NkgGdobR–oM","title"=>"上帝的爱情–经文：何西阿书第10–14章"],
            "0915"=>["vid"=>"RD7OyUqrQXc","title"=>"耶和华的日子–经文：约珥书第1–3章"],
            "0916"=>["vid"=>"j953aHyNfbA","title"=>"我厌恶你们的节期–经文：阿摩司书第1–5章"],
            "0917"=>["vid"=>"OeZOZAfOvVI","title"=>"耶和华选召我，使我不跟从羊群–经文：阿摩司书第6–9章"],
            "0918"=>["vid"=>"H2uxVG8mXvc","title"=>"我岂能不爱惜呢–经文：俄、拿1–4章"],
            "0919"=>["vid"=>"ByvOMVCPPGA","title"=>"铸剑为犁–经文：弥迦书第1–4章"],
            "0920"=>["vid"=>"KNO–Q3x9jOY","title"=>"祂向你所要的是什么呢？–经文：弥迦书第5–7章"],
            "0921"=>["vid"=>"AskRJQKVpvo","title"=>"我要因耶和华欢欣–经文：拿鸿书第1–3章，哈该书第1–3章"],
            "0922"=>["vid"=>"LyY9p9l8oNk","title"=>"都听从耶和华他们神的话–经文：西书番雅第1–3章，哈该书第1–2章"],
            "0923"=>["vid"=>"KVIqiHUp64M","title"=>"不是依靠势力，不是依靠才能–经文：撒迦利亚书1–5章"],
            "0924"=>["vid"=>"RAPTQCVWKyc","title"=>"掌王权的祭司–经文：撒迦利亚书6–9章"],
            "0925"=>["vid"=>"–j1388IHpDc","title"=>"耶和华必作全地的王–经文：撒迦利亚书11–14章"],
            "0926"=>["vid"=>"B80vDgKJp–Y","title"=>"我还有末了的话–经文：玛拉基书1–4章"],
            "0927"=>["vid"=>"uljuBAyYAu0","title"=>"看哪我将一切都更新了–经文：马太福音1–2章"],
            "0928"=>["vid"=>"2z85IsROw0Q","title"=>"尽诸般的义–经文：马太福音3–5章"],
            "0929"=>["vid"=>"CWeLQR–g08I","title"=>"律法和先知的道理–经文：马太福音6–8章"],
            "0930"=>["vid"=>"RpLwbPZSfzw","title"=>"如羊进入狼群–经文：马太福音9–10章"],
            "1001"=>["vid"=>"ZrFmJlNvwqk","title"=>"你们出去到底要看什么–经文：马太福音11–12章"],
            "1002"=>["vid"=>"iXl0on–_dxc","title"=>"奉献与交托，经文：太13—14"],
            "1003"=>["vid"=>"g1G9myzyeFk","title"=>"你们说我是谁？–经文：太15–17章"],
            "1004"=>["vid"=>"qVUt7eFfs5A","title"=>"仆人心志的领袖–经文：马太福音18–20章"],
            "1005"=>["vid"=>"T3P7N7DRh5A","title"=>"和平之君–经文：太21–22章"],
            "1006"=>["vid"=>"G97C2FDT9Sw","title"=>"挪亚的日子–经文：太23–24章"],
            "1007"=>["vid"=>"xA5xUr968hI","title"=>"不要照我的意思，只要照你的意思–经文：马太福音25–26章"],
            "1008"=>["vid"=>"w16GCbhTS7k","title"=>"看啊，我将一切都更新了–经文：马太福音27–28章"],
            "1009"=>["vid"=>"2rXFvBPJSPg","title"=>"是神子又是仆人–经文：马可福音1–3章"],
            "1010"=>["vid"=>"8JKUX–UapK4","title"=>"上帝的许可–经文：马可福音4–5章"],
            "1011"=>["vid"=>"ZtDoFNO8ouQ","title"=>"至死忠心–经文：马可福音6–7章"],
            "1012"=>["vid"=>"Jws88_b6vD0","title"=>"登山变像–经文：马可福音8–9章"],
            "1013"=>["vid"=>"G8ncZtN2neQ","title"=>"盲者的眼光–经文：马可福音10–11章"],
            "1014"=>["vid"=>"IOpKTDwyXbw","title"=>"穷寡妇的信心–经文：马可福音12–13章"],
            "1015"=>["vid"=>"WoIIJhc4cS8","title"=>"你要三次不认我–经文：马可福音14–16章"],
            "1016"=>["vid"=>"35SwtC0hM4A","title"=>"由童贞女马利亚所生–经文：路加福音1章"],
            "1017"=>["vid"=>"GgcEJP–6gVs","title"=>"又公义又虔诚–经文：路加福音2–3章"],
            "1018"=>["vid"=>"Hbz72nNR4Yg","title"=>"你跟从我来–经文：路加福音4–5章"],
            "1019"=>["vid"=>"p0LpQeoPmvE","title"=>"爱仇敌–经文：路加福音6–7章"],
            "1020"=>["vid"=>"EzX–UdfIPaE","title"=>"再思撒种的比喻–经文：路加福音8–9章"],
            "1021"=>["vid"=>"S43Sbz–5fcM","title"=>"我们在天上的父–经文：路加福音10–11章"],
            "1022"=>["vid"=>"kEf7R7JSXR0","title"=>"不是怕祂，乃是爱祂–经文：路加福音12–13章"],
            "1023"=>["vid"=>"VEHb2Wg0Pdo","title"=>"可怜的财主–经文：路加福音14–16章"],
            "1024"=>["vid"=>"x4g5dMO7n1M","title"=>"撒该快下来–经文：路加福音17–19章"],
            "1025"=>["vid"=>"d_xfabBP99w","title"=>"两个不同的向度–经文：路加福音20–21章"],
            "1026"=>["vid"=>"edYMn9FufTk","title"=>"主果真与我同在–经文：路加福音22–24章"],
            "1027"=>["vid"=>"mKpnMH2Vtcc","title"=>"道就是神–经文：约翰福音1–2章"],
            "1028"=>["vid"=>"UmTXs61u9Ws","title"=>"这和你说话的就是祂–经文：约翰福音3–4章"],
            "1029"=>["vid"=>"Kk0y–KMiCmA","title"=>"毕士大的奇迹–经文：约翰福音5–6章"],
            "1030"=>["vid"=>"x4mOs5kuRGU","title"=>"你们中间谁是没罪的–经文：约翰福音7–8章"],
            "1031"=>["vid"=>"a7uApoWzv5E","title"=>"有一件事我知道–经文：约翰福音9–10章"],
            "1101"=>["vid"=>"2jUVHIwsIdA","title"=>"耶稣哭了–经文：约翰福音11–12章"],
            "1102"=>["vid"=>"qU–XQ_WMpYI","title"=>"我赐给你们一条新命令–经文：约翰福音13–15章"],
            "1103"=>["vid"=>"Isw4V1bmGbQ","title"=>"合而为一–经文：约翰福音16–18章"],
            "1104"=>["vid"=>"oDGXFtB_mc0","title"=>"我们都是怀疑的多马–经文：约翰福音19–21章"],
            "1105"=>["vid"=>"73qPVOSc4HY","title"=>"向初代教会学习–经文：使徒行传1–2章"],
            "1106"=>["vid"=>"L1EblLgIDM4","title"=>"迦玛列的一句话–经文：使徒行传3–5章"],
            "1107"=>["vid"=>"xyt0hwC9GjE","title"=>"做个好执事–经文：使徒行传6–7章"],
            "1108"=>["vid"=>"7TsXgxdVLyM","title"=>"蒙圣灵的安慰–经文：使徒行传8–9章"],
            "1109"=>["vid"=>"kD6wxYG0W28","title"=>"蒙主悦纳的虔诚人–经文：使徒行传10–11章"],
            "1110"=>["vid"=>"7JD54tgZcm8","title"=>"你得救了没有–经文：使徒行传12–13章"],
            "1111"=>["vid"=>"EtcC_ymMbhg","title"=>"差传的异象–经文：使徒行传14–16章"],
            "1112"=>["vid"=>"dbt8pKn0O5w","title"=>"庇哩亚人的榜样–经文：使徒行传17–19章"],
            "1113"=>["vid"=>"k3wM4x1Lj14","title"=>"施比受更有福–经文：使徒行传20–21章"],
            "1114"=>["vid"=>"e7qX37jkYek","title"=>"保罗的罪名–经文：使徒行传22–24章"],
            "1115"=>["vid"=>"hggNHXtaYdg","title"=>"宣教途中的神迹–经文：使徒行传25–28章"],
            "1116"=>["vid"=>"q3VJ0qn0hB8","title"=>"改变世界的能力–经文：罗马书1–2章"],
            "1117"=>["vid"=>"afnyhploQro","title"=>"得救之道–经文：罗马书3–5章"],
            "1118"=>["vid"=>"nnBW5r0cV00","title"=>"圣灵替我们祷告–经文：罗马书6–8章"],
            "1119"=>["vid"=>"AIcWXiE–US4","title"=>"向罪而死、为主而活–经文：罗马书9–11章"],
            "1120"=>["vid"=>"0vIlftFU9uQ","title"=>"爱的亏欠–经文：罗马书12–16章"],
            "1121"=>["vid"=>"CwL3jT1xx2o","title"=>"神要拣选的人–经文：哥林多前书1–3章"],
            "1122"=>["vid"=>"r3qo4ODsfdY","title"=>"我们成了一台戏–经文：哥林多前书4–7章"],
            "1123"=>["vid"=>"0JINGWHUte8","title"=>"三个宝贵的应许–经文：哥林多前书8–11章"],
            "1124"=>["vid"=>"Mxo–MrKpwzk","title"=>"身子与肢体–经文：哥林多前书12–14章"],
            "1125"=>["vid"=>"uAcDyi7wIBA","title"=>"基督复活的明证–经文：哥林多前书15–16章"],
            "1126"=>["vid"=>"i2_85ero78U","title"=>"得胜的凯歌–经文：哥林多后书1–5章"],
            "1127"=>["vid"=>"gB–g9ol6aeI","title"=>"基督徒的奉献–经文：哥林多后书6–9章"],
            "1128"=>["vid"=>"WM5Dh20KM2c","title"=>"恩典的刺–经文：哥林多后书10–13章"],
            "1129"=>["vid"=>"DwrKqe–BS0M","title"=>"圣灵与肉体–经文：加拉太书1–3章"],
            "1130"=>["vid"=>"LwdI3I5szeA","title"=>"种什么收什么–经文：加拉太书4–6章"],
            "1201"=>["vid"=>"P7wDTyOVoLU","title"=>"平信徒的觉醒–经文：以弗所书1–6章"],
            "1202"=>["vid"=>"iVVCM6o1f6o","title"=>"兴旺福音的四个秘诀–经文：腓立比书1–2章"],
            "1203"=>["vid"=>"Mssvt7RLFn4","title"=>"出人意外的平安–经文：腓立比书3–4章"],
            "1204"=>["vid"=>"KpX2CNVNUh8","title"=>"基督的平安在我们心里做主–经文：歌罗西书1–4章"],
            "1205"=>["vid"=>"0bUE6UxCsxc","title"=>"敬爱神的仆人–经文：帖撒罗尼迦前书1–5章"],
            "1206"=>["vid"=>"NelGoNF_nI0","title"=>"这话是可信的–经文：帖后和提前1–2章"],
            "1207"=>["vid"=>"JZzJqKwnOIQ","title"=>"今日教会的危机–经文：提摩太前书3–6章"],
            "1208"=>["vid"=>"Yww5_8NkzpU","title"=>"上帝工人的五种身份–经文：提摩太后书1–4章"],
            "1209"=>["vid"=>"psn7PnawLfM","title"=>"家里的教会–经文：提多书+腓力门书"],
            "1210"=>["vid"=>"BlHYLWErk74","title"=>"我们若认自己的罪–经文：希伯来书1–6章"],
            "1211"=>["vid"=>"Hrz68Jw5JvI","title"=>"死后且有审判–经文：希伯来书7–9章"],
            "1212"=>["vid"=>"Xi5CqvrAlE4","title"=>"未见之事的确据–经文：希伯来书10–11章"],
            "1213"=>["vid"=>"Q8YlaiJkhog","title"=>"慎言–经文：希伯来书12–13章+雅各书1章"],
            "1214"=>["vid"=>"KIoiVLluIbc","title"=>"信心加上行为–经文：雅各书2–5章"],
            "1215"=>["vid"=>"r4oO9pGypr8","title"=>"行善者何竟受苦–经文：彼得前书1–3章"],
            "1216"=>["vid"=>"lAdzzFWvRpk","title"=>"以谦卑束腰–经文：彼得前书4–5章+彼得后书1–2章"],
            "1217"=>["vid"=>"rC_9Rbm3YsA","title"=>"从此就知道何为爱–经文：彼得后书3+约翰壹书1–3章"],
            "1218"=>["vid"=>"SGOFiPR96ug","title"=>"耶稣基督的仆人–经文：约翰壹书4–5章+约翰二书+约翰三书+犹大书"],
            "1219"=>["vid"=>"OzMI8RporXs","title"=>"拔摩海岛上的七个福–经文：启示录1-3章"],
            "1220"=>["vid"=>"_9iSqEbyuHY","title"=>"末世的光景–经文：启示录4-7章"],
            "1221"=>["vid"=>"Kxb6ugOEYF8","title"=>"警惕末世的四个征兆–经文：启示录8-11章"],
            "1222"=>["vid"=>"kwy8nruKYqQ","title"=>"胜过魔鬼仇敌的三样法宝–经文：启示录12-14章"],
            "1223"=>["vid"=>"xEQzdz2opco","title"=>"愤怒的七只金碗–经文：启示录15-18章"],
            "1224"=>["vid"=>"CXWRRhKAUog","title"=>"无底坑与千禧年–经文：启示录19-20章"],
            "1225"=>["vid"=>"BeBJkNBaEJQ","title"=>"圣经里最后的祷告- 经文：启示录21-22章"],
            "1226"=>["vid"=>"1ivg__tN6vE","title"=>"终章完结"],
        ];
        $dateStr = date('md');
        // $dateStr = '0101';
        if(!isset($items[$dateStr])) {
            $item = last($items);
        }else{
            $item = $items[$dateStr];    
        }

        $vid = $item['vid'];
        $url = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".mp4";
        $image = 'https://share.simai.life/uPic/2023/Amn09V.jpg';
        $data = [
            'type' => 'link',
            'data' => [
                "url" => $url,
                'title' => "每日与主同行-{$dateStr}" ,
                'description' => $item['title'],
                'image' => $image,
                'vid' => $vid,
            ],
            'statistics' => [
                'metric' => class_basename(__CLASS__),
                "keyword" => '801',
                "type" => 'video',
            ]
        ];

        $m4a = env('R2_DOMAIN')."/@pastorpaulqiankunlu618/".$vid.".m4a";
        $addition = $data;
        $addition['type'] = 'music';
        $addition['data']['url']= $m4a;
        $addition['statistics']['type'] = 'audio';

        $data['addition'] = $addition;
        return $data; 
    }
}
