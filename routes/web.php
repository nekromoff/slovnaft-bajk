<?php

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

Route::get('/', ['uses' => 'ScraperController@stats', 'as' => 'stats']);

Route::get('scrape', ['uses' => 'ScraperController@scrape', 'as' => 'scrape', 'middleware' => 'doNotCacheResponse']);
Route::get('process/daily', ['uses' => 'ScraperController@processDailySummary', 'as' => 'process.daily', 'middleware' => 'doNotCacheResponse']);

Route::get('ogimage/{time}', ['uses' => 'ScraperController@generateOGImage', 'as' => 'ogimage', 'middleware' => 'doNotCacheResponse']);
