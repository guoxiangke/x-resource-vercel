<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

// use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;

// use Symfony\Component\HttpClient\Psr18Client;
// use Tectalic\OpenAi\Authentication;
// use Tectalic\OpenAi\Client;
// use Tectalic\OpenAi\Manager;
// use Tectalic\OpenAi\Models\Completions\CreateRequest;
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

Route::get('/', function () {

    return view('welcome');
});

Route::get('/cache', function (){
    $cacheKey = 'cacheKey';
    $data = Cache::store('redis')->get($cacheKey, strtotime('tomorrow') - time());
    Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
    return [$data];
});

Route::get('/set/baidutea/sendIsOn', function (){
    $cacheKey = '805';
    $data = date('Y-m-d H:i:s',strtotime('tomorrow')); //设置BJ晚上7点发送
    Cache::store('redis')->put($cacheKey, $data, strtotime('tomorrow') - time());
    $data = Cache::store('redis')->get($cacheKey, false);
    return [$data];
});


Route::get('/resources/{keyword}', function ($keyword){
    $resource = app("App\Services\Resource");
    return $resource->_invoke($keyword);
})->where('keyword', '.*');

Route::get('/tingdao/{id}', function ($id){
    // 19717 简明神学（詹姆斯·因内尔·巴刻）
    // 14930 《软弱之道-靠主得力的人生》
    // 14696 
    $response = Http::asForm()->post('https://www.tingdao.org/Record/exhibitions',['ypid'=>"19717"]);
    $json = $response->json();
    return $json;
});