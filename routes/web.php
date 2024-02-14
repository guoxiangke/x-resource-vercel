<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Madcoda\Youtube\Facades\Youtube;
use App\Helpers\Helper;
use YouTube\YouTubeDownloader;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test/tmp', function () {
    $file = '/tmp/test.log';
    file_put_contents($file, now() . PHP_EOL, FILE_APPEND);
    return file_get_contents($file);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cache', function (){
    $cacheKey = 'cacheKey';
    $data = Cache::store('redis')->get($cacheKey, strtotime('tomorrow') - time());
    Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
    return [$data];
});

// 百度茶室
Route::get('/set/baidutea/sendIsOn', function (){
    $cacheKey = '805';
    $data = date('Y-m-d H:i:s',strtotime('tomorrow'));
    Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
    $data = Cache::store('redis')->get($cacheKey, false);
    return [$data];
});
// 主日讲道
Route::get('/set/fwdlist/sendIsOn', function (){
    $cacheKey = '806';
    $data = date('Y-m-d H:i:s',strtotime('tomorrow'));
    Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
    $data = Cache::store('redis')->get($cacheKey, false);
    return [$data];
});

Route::get('/youtube/get-last-by-playlist/{playlistId}', function ($playListId){
    $all = Helper::get_all_items_by_youtube_playlist_id($playListId);
    return collect($all)->last();
});

Route::get('/youtube/search-last-by-channel/{channelId}/{keyword}', function ($channelId,$keyword){
    $all = Youtube::searchChannelVideos($keyword, $channelId, $limit=1, $order='date');
    return collect($all)->first();
});

Route::get('/youtube/{vid}', function ($vid){
   return  $video = Youtube::getVideoInfo($vid);
});

Route::get('/youtube/{vid}/{qualityLabel}', function ($vid, $qualityLabel='all'){
    $youtube = new YouTubeDownloader();
    $downloadOptions = $youtube->getDownloadLinks("https://www.youtube.com/watch?v=".$vid);
    $all = $downloadOptions->getAllFormats() ;
    return $downloadOptions->getFirstCombinedFormat()->url;
    // $youtube = new \YouTube\YouTubeStreamer();
    $allp = Arr::keyBy($all, 'qualityLabel');
    return $qualityLabel=='all'?$all:$allp[$qualityLabel]->url;
})->whereIn('qualityLabel', ['all', '360p', '720p', '1080p']);;

Route::get('/resources/{keyword}', function ($keyword){
    $resource = app("App\Services\Resource");
    return $resource->_invoke($keyword);
})->where('keyword', '.*');
