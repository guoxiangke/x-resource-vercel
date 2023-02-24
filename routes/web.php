<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

use OpenAI\Laravel\Facades\OpenAI;

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

Route::get('/resources/{keyword}', function ($keyword){
    $resource = app("App\Services\Resource");
    return $resource->__invoke($keyword);
})->where('keyword', '.*');


Route::get('/test', function (){
    $keyword = '@AI助理 请介绍一下冒泡排序';
    if(Str::contains($keyword, '@AI助理')){
        // https://laravel-news.com/openai-for-laravel
        $result = OpenAI::completions()->create([
            'model'  => 'text-davinci-003',
            // 'model'  => 'text-ada-001',
            'prompt' => $keyword,
            'temperature' => 0.5,
            'max_tokens' => 800,
            'top_p'=>1,
            'frequency_penalty'=>0,
            'presence_penalty'=>0
        ]);

        return [
            "type" => "text",
            "data" => ['content'=>$result['choices'][0]['text']],
        ];

        // https://github.com/openai-php/laravel
        // https://github.com/openai-php/client

        // Build a Tectalic OpenAI REST API Client globally.
        // $auth = new Authentication(getenv('OPENAI_API_KEY'));
        $auth = new Authentication(getenv('OPENAI_API_KEY'));
        $httpClient = new \GuzzleHttp\Client();
        $client = new Client($httpClient, $auth, Manager::BASE_URI);
        // $client->completions()->create();

        // $auth = new Authentication(config('services.openai.key'));
        // $openaiClient = Manager::build(new \GuzzleHttp\Client(), $auth);
        // $keyword = trim(Str::remove('@AI助理', $keyword));
        // return [__LINE__];
        $response = $client->completions()->create(
            new CreateRequest([
                'model'  => 'text-davinci-003',
                // 'model'  => 'text-ada-001',
                'prompt' => $keyword,
                'temperature' => 0.5,
                'max_tokens' => 800,
                'top_p'=>1,
                'frequency_penalty'=>0,
                'presence_penalty'=>0
            ])
        )->toModel();

        return [
            "type" => "text",
            "data" => ['content'=>$response->choices[0]->text],
        ];
    }
});