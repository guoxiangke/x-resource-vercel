<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
    $data = Cache::get($cacheKey, strtotime('tomorrow') - time());
    Cache::put($cacheKey, $data, strtotime('tomorrow') - time());
    return [$data];
});

Route::get('/resources/{keyword}', function ($keyword){
    $resource = app("App\Services\Resource");
    return $resource->__invoke($keyword);
})->where('keyword', '.*');