<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;

final class OurDailyBread {
	public function _invoke($keyword) {
        $data = [];
        if($keyword == 'odb'){
          $s1 = date('Y/m');
          $s2 = date('m') . "-". date('d') . "-".date('y');
        	$url = "https://dzxuyknqkmi1e.cloudfront.net/odb/{$s1}/odb-{$s2}.mp3";
          $title = "Our Daily Bread" . $s2;
          $data = [
            'type' => 'music',
            "data"=> [
                  "url" => $url,
                  'title' => $title,
                  'description' => "来自Our Daily Bread",
              ],
          ];
          $data['statistics'] = [
              'metric' => class_basename(__CLASS__),
              "keyword" => $keyword
          ];
          return $data;
        }
        return null;
	}
}
