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

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/botman', 'BotManController@handle');
Route::get('/botman/tinker', 'BotManController@tinker');


Route::get('clearcache', function() {
    $exitCode = Artisan::call('config:cache');
  return 'sukses';
});
Route::get('clearcache1', function() {
    $exitCode = Artisan::call('config:clear');
  return 'sukses';
});
Route::get('clearcache2', function() {
    $exitCode = Artisan::call('cache:clear');
  return 'sukses';
});
