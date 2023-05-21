<?php

namespace App\Services\Resources;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\HtmlDomParser;

final class LyLts {
	public function _invoke($keyword) {
        // preg_match('/\d{3,}/', $keyword);
        // region 以#开头的 #101XXX-#909XXX
        // if ($keyword[0] == '#') {}
        $data = [];
        if(Str::startsWith($keyword, "#")){
            return [
            	'type' => 'music',
            	"data"=> $data,
            ];
        }
        return null;
	}
}
