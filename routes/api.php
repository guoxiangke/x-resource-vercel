<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\Helper;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/resources/{keyword}', function ($keyword){
    $resource = app("App\Services\Resource");
    return $resource->_invoke($keyword);
})->where('keyword', '.*');


Route::get('/youtube/get-all-by-playlist/{playlistId}', function ($playListId){
    $all = Helper::get_all_items_by_youtube_playlist_id($playListId);
    return collect($all);
});