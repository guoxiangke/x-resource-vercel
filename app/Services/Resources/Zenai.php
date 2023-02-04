<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class Zenai {
	public function __invoke($keyword) {

    if($keyword == 700){
        $content = "=====节目分组1=====\n【701】灵程真言\n【702】喜乐灵程\n【703】认识你真好 \n【704】真爱驻我家\n【705】尔道自建\n【706】旷野吗哪\n【707】真道分解\n【708】馒头的对话\n【709】拥抱每一天\n【710】星动一刻";
        return [
            'type' => 'text',
            'data' => ['content' => $content]
        ];
    }
// https://depk9mke9ym92.cloudfront.net/tllczy/tllczy221203.mp3
        $res = [
            // https://d3pc7cwodb2h3p.cloudfront.net/all_tllczy_songs.json
            '701' =>[ // 1 - 7
                'title' => '灵程真言',
                'code' => "tllczy",
            ],
            '702' =>[ // 1-7
                'title' => '喜乐灵程',
                'code' => "tljd",
            ],
            '703' =>[//1-7
                'title' => '认识你真好',
                'code' => "vof",
            ],
            '704' =>[ // 1 - 5 真爱驻我家 是周一休息
                'title' => '真爱驻我家',
                'code' => "tltl",
            ],
            '705' =>[
                'title' => '尔道自建',
                'code' => "edzj",
            ],
            '706' =>[
                'title' => '旷野吗哪',
                'code' => "mw",
            ],
            '707' =>[
                'title' => '真道分解',
                'code' => "be",
            ],
            '708' =>[
                'title' => '馒头的对话',
                'code' => "mn",
            ],
            '709' =>[
                'title' => '拥抱每一天',
                'code' => "ee",
            ],
            '710' =>[
                'title' => '星动一刻',
                'code' => "hp",
            ],
        ];

        if(in_array($keyword, array_keys($res))){
            $cacheKey = "xbot.700.{$keyword}";
            $data = Cache::get($cacheKey, false);
            if($data) return $data;

            if(!$data){
                $res = $res[$keyword];
                $response = Http::get("https://d3pc7cwodb2h3p.cloudfront.net/all_{$res['code']}_songs.json");
                $json =$response->json();
                $jdata = last($json);
                $title = "【{$keyword}】{$res['title']}-".substr($jdata['time'],2);

                $code = $res['code'];
                $mp3Code = $res['code'];
                if($code == 'tltl'){
                    $weekCode=['ht',1,'tl','ms','pc','sp','gr'];//0-6
                    // 20220910
                    $d = \DateTime::createFromFormat('Ymd', $jdata['time']);
                    $dayOfWeek = $d->format('w');
                    if($dayOfWeek == 1) return;//周一没有
                    $mp3Code = 'tl' . $weekCode[$dayOfWeek];
                }

                $image = "https://d33tzbj8j46khy.cloudfront.net/{$code}.png";
                $codeStr = "/{$code}/$mp3Code" . substr($jdata['time'], 2);
                $mp3Domain = 'd20j6nxnxd2c4l';//depk9mke9ym92
                $mp3 = "https://{$mp3Domain}.cloudfront.net{$codeStr}.mp3";

                // https://depk9mke9ym92.cloudfront.net/     tltl/tlgr221203.mp3
                // 不好意思，我们的手机app，国内有些地方使用有问题，所以做了新的配置：
                // https://d20j6nxnxd2c4l.cloudfront.net/tllczy/tllczy230104.mp3
                // https://d7jf0n9s4n8dc.cloudfront.net/html/tlgr/tlgr221203.html


                $data = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3,
                        'title' => $title,
                        'description' => $jdata['title'],
                        'image' => $image,
                    ],
                ];
                if(0&&$jdata['hasArtistHtml']){
                    $codeStr = "/{$mp3Code}/$mp3Code" . substr($jdata['time'], 2);
                    $addition = [
                        'type' => 'link',
                        'data' => [
                            'image' => $image,
                            "url" => "https://dxd6tocqg9xyb.cloudfront.net/html{$codeStr}.html",
                            'title' => $title,
                            'description' => '节目文本-'. $jdata['title'],
                        ],
                    ];
                    $data = array_merge($data,['addition'=>$addition]);
                    Cache::put($cacheKey, $data, strtotime('tomorrow') - time());
                    return $data;
                }
                Cache::put($cacheKey, $data, strtotime('tomorrow') - time());
                return $data;
            }
        }
        return null;
	}
}
