<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;

final class LyAudio{
    public function _invoke($keyword) {
    //3位数关键字xxx
    // $offset = substr($oriKeyword, 3) ?: 0;
    $keyword = substr($keyword, 0, 3);
    
    if($keyword == 600){
        $content = "=====生活智慧=====\n【610】星动一刻\n【612】书香园地\n【604】恋爱季节\n【675】不孤单地球\n【678】阅读视界\n【674】深度泛桌派\n【668】岁月正好\n【613】i-Radio爱广播\n【614】今夜心未眠\n【657】天使夜未眠\n【611】零点凡星\n=====少儿家庭=====\n【605】一起成长吧\n【659】爆米花\n【602】欢乐下课趣\n【607】将将！百宝书开箱\n【664】小羊圣经故事\n【652】喜乐葡萄剧乐部\n【606】亲情不断电\n【660】我们的时间\n=====诗歌音乐=====\n【623】齐来颂扬\n【616】长夜的牵引\n【680】午的空间\n【608】一起弹唱吧！ \n=====生命成长=====\n【601】无限飞行号\n【603】空中辅导\n【620】旷野吗哪\n【618】献上今天\n【627】故事‧心天空\n【619】拥抱每一天\n【698】馒头的对话\n【646】晨曦讲座\n【624】成主学堂 \n【630】主啊！995！ \n【640】这一刻，清心\n【628】空中崇拜\n【672】燃亮的一生\n【626】微声盼望\n=====圣经讲解=====\n【621】真道分解\n【622】圣言盛宴\n【676】穿越圣经\n【654】与神同行\n【681】卢文心底话\n【679】经典讲台\n【629】善牧良言\n【625】真理之光\n【648】天路导向\n=====课程训练=====\n【641】良友圣经学院（启航课程）\n【642】良院本科第一套\n【643】良院本科第二套\n【644】良院进深第一套\n【645】良院进深第二套\n【671】良院讲台\n=====其他语言=====\n【650】恩典与真理\n【651】爱在人间（云南话）\n【677】穿越圣经（粤）\n【649】天路导向（粤、英）\n【682】呢铺你点拣（粤语）\n【683】方形西瓜（粤语）\n【684】冷行（粤语）\n【685】王籽，谢谢你！（粤语）\n【686】陪你自由行（粤语）\n【687】清唱清谈（粤语）\n【688】好好恋爱学堂（粤语）";
        return [
            'type' => 'text',
            'data' => ['content' => $content]
        ];
    }
    if($keyword>600 && $keyword<700){
        $map = array_flip([601 => "ib",602 => 'fa',603 => "cc",604 => "se",605 => "gg",606 => "up",607 => 'bx',608 => 'pp',609 => '',610 => "hp",611 => "sa",612 => "bc",613 => "ir",614 => "rt",615 => '',616 => "ws",617 => '',618 => "dy",619 => "ee",620 => "mw",621 => "be",622 => "bs",623 => "cw",624 => "dr",625 => "th",626 => "wr",627 => "yy",628 => "aw",629 => "yp",630 => "mg",631 => '',632 => "",633 => '',634 => "",635 => '',636 => '',637 => '',638 => '',639 => '',640 => "mpa",641 => "ltsnp",642 => "ltsdp1",643 => "ltsdp2",644 => "ltshdp1",645 => "ltshdp2",646 => "ds",647 => "",648 => "wa",649 => "cwa",650 => "gt",651 => "ynf",652 => "jvc",653 => "",654 => "it",655 => '',656 => '',657 => "ka",658 => '',659 => "pc",660 => "ut",661 => '',662 => '',663 => '',664 => "cs",665 => '',666 => '',667 => '',668 => "ec",669 => '',670 => '',671 => "vp",672 => "ls",673 => '',674 => "pt",675 => "wc",676 => "ttb",677 => "cttb",678 => "bn",679 => "sc",680 => "gf",681 => "fh",
            682=>'caabg',
            683=>"caawm",
            684=>"caccp",
            685=>"caatp",
            686=>"caaco",
            687=>"cacac",
            688=>"cbbgl",
            698 => "mn",699 => "", ]);

        if($code = array_search($keyword, $map)){
            $data = Cache::store('redis')->get($code, false);//cc
            $isNoCache = in_array($code, ['cc','dy','gf']);
            if($isNoCache || !$data){
                $json = Http::get('https://open.729ly.net/api/program/'.$code)->json();
                $item = $json['data'][0];
                $data =[
                    'type' => 'music',
                    'data' => [
                        "url" => $item['link'],
                        'title' => "【{$keyword}】".str_replace('圣经','SJ',$item['program_name']).'-'.$item['play_at'],
                        'description' => str_replace('教会','JH',$item['description']),
                        'image' => "https://txly2.net/images/program_banners/{$code}_prog_banner_sq.png",
                    ],
                ];
                $data['statistics'] = [
                    'metric' => class_basename(__CLASS__),
                    "keyword" => $code,
                ];
                // Carbon::tomorrow()->diffInSeconds(Carbon::now());
                if(!$isNoCache) Cache::store('redis')->put($code, $data, strtotime('tomorrow') - time());
            }
            return $data;
        }
    }
  }
}