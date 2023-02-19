<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use voku\helper\HtmlDomParser;

final class Bible{
	public function __invoke($keyword) {
        $titles = ["创世记","出埃及记","利未记","民数记","申命记","约书亚记","士师记","路得记","撒母耳记上","撒母耳记下","列王纪上","列王纪下","历代志上","历代志下","以斯拉记","尼希米记","以斯帖记","约伯记","诗篇","箴言","传道书","雅歌","以赛亚书","耶利米书","耶利米哀歌","以西结书","但以理书","何西阿书","约珥书","阿摩司书","俄巴底亚书","约拿书","弥迦书","那鸿书","哈巴谷书","西番雅书","哈该书","撒迦利亚书","玛拉基书","马太福音","马可福音","路加福音","约翰福音","使徒行传","罗马书","哥林多前书","哥林多后书","加拉太书","以弗所书","腓立比书","歌罗西书","帖撒罗尼迦前书","帖撒罗尼迦后书","提摩太前书","提摩太后书","提多书","腓利门书","希伯来书","雅各书","彼得前书","彼得后书","约翰一书","约翰二书","约翰三书","犹大书","启示录"];
        $continue = false;
        foreach ($titles as $title) {
            if(Str::startsWith($keyword, $title)){
                $continue = true;
                $volumeName = $title;
                if(preg_match('/([\d]+)/', $keyword, $match)){
                    $index = $match[0];
                }
            }
        }
        if(in_array($keyword, $titles) || $continue == true){
            $cacheKey = "xbot.keyword.bible.".$keyword;
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                // $response = Http::get("https://www.biblegateway.com/audio/bible_data/?osis=Gen.2&version=ccb&author=biblica");
                // $json = $response->json();
                // Storage::put('books.json', json_encode($json['books']));
                $books = json_decode('[{"book":"Gen","chapters":"50","display":"创世记"},{"book":"Exod","chapters":"40","display":"出埃及记"},{"book":"Lev","chapters":"27","display":"利未记"},{"book":"Num","chapters":"36","display":"民数记"},{"book":"Deut","chapters":"34","display":"申命记"},{"book":"Josh","chapters":"24","display":"约书亚记"},{"book":"Judg","chapters":"21","display":"士师记"},{"book":"Ruth","chapters":"4","display":"路得记"},{"book":"1Sam","chapters":"31","display":"撒母耳记上"},{"book":"2Sam","chapters":"24","display":"撒母耳记下"},{"book":"1Kgs","chapters":"22","display":"列王纪上"},{"book":"2Kgs","chapters":"25","display":"列王纪下"},{"book":"1Chr","chapters":"29","display":"历代志上"},{"book":"2Chr","chapters":"36","display":"历代志下"},{"book":"Ezra","chapters":"10","display":"以斯拉记"},{"book":"Neh","chapters":"13","display":"尼希米记"},{"book":"Esth","chapters":"10","display":"以斯帖记"},{"book":"Job","chapters":"42","display":"约伯记"},{"book":"Ps","chapters":"150","display":"诗篇"},{"book":"Prov","chapters":"31","display":"箴言"},{"book":"Eccl","chapters":"12","display":"传道书"},{"book":"Song","chapters":"8","display":"雅歌"},{"book":"Isa","chapters":"66","display":"以赛亚书"},{"book":"Jer","chapters":"52","display":"耶利米书"},{"book":"Lam","chapters":"5","display":"耶利米哀歌"},{"book":"Ezek","chapters":"48","display":"以西结书"},{"book":"Dan","chapters":"12","display":"但以理书"},{"book":"Hos","chapters":"14","display":"何西阿书"},{"book":"Joel","chapters":"3","display":"约珥书"},{"book":"Amos","chapters":"9","display":"阿摩司书"},{"book":"Obad","chapters":"1","display":"俄巴底亚书"},{"book":"Jonah","chapters":"4","display":"约拿书"},{"book":"Mic","chapters":"7","display":"弥迦书"},{"book":"Nah","chapters":"3","display":"那鸿书"},{"book":"Hab","chapters":"3","display":"哈巴谷书"},{"book":"Zeph","chapters":"3","display":"西番雅书"},{"book":"Hag","chapters":"2","display":"哈该书"},{"book":"Zech","chapters":"14","display":"撒迦利亚书"},{"book":"Mal","chapters":"4","display":"玛拉基书"},{"book":"Matt","chapters":"28","display":"马太福音"},{"book":"Mark","chapters":"16","display":"马可福音"},{"book":"Luke","chapters":"24","display":"路加福音"},{"book":"John","chapters":"21","display":"约翰福音"},{"book":"Acts","chapters":"28","display":"使徒行传"},{"book":"Rom","chapters":"16","display":"罗马书"},{"book":"1Cor","chapters":"16","display":"哥林多前书"},{"book":"2Cor","chapters":"13","display":"哥林多后书"},{"book":"Gal","chapters":"6","display":"加拉太书"},{"book":"Eph","chapters":"6","display":"以弗所书"},{"book":"Phil","chapters":"4","display":"腓立比书"},{"book":"Col","chapters":"4","display":"歌罗西书"},{"book":"1Thess","chapters":"5","display":"帖撒罗尼迦前书"},{"book":"2Thess","chapters":"3","display":"帖撒罗尼迦后书"},{"book":"1Tim","chapters":"6","display":"提摩太前书"},{"book":"2Tim","chapters":"4","display":"提摩太后书"},{"book":"Titus","chapters":"3","display":"提多书"},{"book":"Phlm","chapters":"1","display":"腓利门书"},{"book":"Heb","chapters":"13","display":"希伯来书"},{"book":"Jas","chapters":"5","display":"雅各书"},{"book":"1Pet","chapters":"5","display":"彼得前书"},{"book":"2Pet","chapters":"3","display":"彼得后书"},{"book":"1John","chapters":"5","display":"约翰一书"},{"book":"2John","chapters":"1","display":"约翰二书"},{"book":"3John","chapters":"1","display":"约翰三书"},{"book":"Jude","chapters":"1","display":"犹大书"},{"book":"Rev","chapters":"22","display":"启示录"}]');
                
                // $title = $keyword;
                $volumeName = $volumeName??$title;
                $filtered = Arr::where($books, function ($value, $key) use($volumeName) {
                    return $value->display == $volumeName;
                });
                $key = array_key_first($filtered);//0-65
                $volume = $filtered[$key]->book;//Gen
                $total = $filtered[$key]->chapters; //50

                $offset = $index??now()->format('z')%$total+1;
                $chapter = "{$volume}.{$offset}";

                $version = 'ccb';
                $author = 'biblica';
                $response = Http::get("https://www.biblegateway.com/audio/bible_data/?osis={$chapter}&version={$version}&author={$author}");
                $json = $response->json();
                $mp3 = "https://stream.biblegateway.com/bibles/32/{$version}-{$author}/$chapter.{$json['curHash']}.mp3"
                ;

                $title = "$volumeName $offset ($chapter)"; // Gen.2
                $desc = "CCB - Biblica";
                $data = [
                    'type' => 'music',
                    "data"=> [
                        "url" => $mp3,
                        'title' => $title,
                        'description' => $desc,
                    ],
                ];

                //0-38-65
                if($key>38){
                    //new test hao-csbs
                    $version = 'csbs';
                    $author = 'hao';

                    $response = Http::get("https://www.biblegateway.com/audio/bible_data/?osis={$chapter}&version={$version}&author={$author}");
                    $json = $response->json();
                    $mp3 = "https://stream.biblegateway.com/bibles/32/{$version}-{$author}/$chapter.{$json['curHash']}.mp3"
                    ;
                    $desc = "CSBS - hao";
                    $addition = [
                        'type' => 'music',
                        "data"=> [
                            "url" => $mp3,
                            'title' => $title,
                            'description' => $desc,
                        ],
                    ];
                    $data = array_merge($data,['addition'=>$addition]);
                }
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
                return $data;
            }
            return $data;
        }
	}
}
