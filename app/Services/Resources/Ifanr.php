<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class Ifanr{
    public function __invoke($keyword)
    {
        // 周1-5
        if($keyword == "ifanr"){
            $date = date('ymd');
            $cacheKey = "xbot.keyword.ifanr";
            $data = Cache::store('redis')->get($cacheKey, false);
            if(!$data){
                
                $response = Http::get("https://sso.ifanr.com/api/v5/wp/article/?post_category=早报&position=ifr_fourth_cards_layout");
                $json =$response->json();
                $image = $json['objects'][0]['post_cover_image'];
                $link  = $json['objects'][0]['post_url'];
                $title = $json['objects'][0]['post_title'];

                $description = $json['objects'][0]['post_excerpt'];
                $description = Str::remove('\r', $description);
                $description = Str::remove('\n', $description);
                $description = Str::remove('· ', $description);

                $data =[
                    "url" => $link,
                    'image' => $image,
                    'title' => "【ifanr】{$title}",
                    'description' => $description,
                ];
                Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
            }
            return [
                'type' => 'link',
                "data"=> $data,
            ];
        }
    }
}
