<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Madcoda\Youtube\Facades\Youtube;
final class Youtube{
    // https://youtu.be/Y8X8JXNbBbI
    // https://www.youtube.com/watch?v=Y8X8JXNbBbI&list=RDwwpK3p4heEM&index=2
    // https://www.youtube.com/embed/JHdB1dYAteA?si=b2G7vdIdVbNQzH4X
    // https://youtu.be/Y8X8JXNbBbI?si=iglGPQxxloV8MpWI
    // https://www.youtube.com/watch?v=Y8X8JXNbBbI&list=RDwwpK3p4heEM&index=2
    // https://m.youtube.com/shorts/6vsRGl4IsMs?si=QBA11T3v1Sl6ijxo
    // https://www.youtube.com/shorts/6vsRGl4IsMs?app=desktop
	public function _invoke($keyword)
	{
        if(preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=)|youtube\.com\/shorts\/)([\w\-]{11})/', $keyword, $matches)){
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
                    'description' => '来自Youtube频道精选',
                    'image' => $image,
                ],
                'statistics' => [
                    'metric' => 'youtube',
                    "keyword" => $vid,
                    "type" => 'video',
                ],
                'addition'=> [
                	'type' => 'text',
            		'data' => ['content' => '5分钟后方可播放,24小时后过期!']
                ],
            ];
            $data = [
                'type' => 'music',
                "data"=> [
                    "url" => $mp3,
                    'title' => $title,
                    'description' => '来自Youtube频道精选',
                ],
                'statistics' => [
                    'metric' => 'youtube',
                    "keyword" => $vid,
                    "type" => 'audio',
                ],
                'addition'=>$addition,
            ];
            $key = 'youtube-vids-need-download';
            $value = Cache::get($key,[]);
            array_unshift($value, $vid);
            Cache::put($key, array_unique($value));
            return $data;
        }
        return null;
	}
}