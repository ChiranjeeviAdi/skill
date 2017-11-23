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
Route::group(array('prefix'=>'Api/v1'),function()
{
    Route::post('login','AdminController@login');
	Route::get('setTempQuiz','QuizController@setTempQuiz');
});

Route::group(['middleware'=>'csrf','prefix'=>'Api/v1'],function($router){
    Route::post('getAllQuizs','QuizController@getAllQuizs');
    Route::get('getQuizTypes','QuizController@getQuizTypes');
    Route::post('setQuiz','QuizController@setQuiz');
    Route::get('getTempQuiz/{id}','QuizController@getTempQuiz');
    Route::post('setTempQuizQuestion','QuizController@setTempQuizQuestion');
    Route::post('generateQuizUrl','QuizController@generateQuizUrl');
    Route::post('updateQuizBasicJsonInfo','QuizController@updateQuizBasicJsonInfo');
    Route::delete('deleteQuiz','QuizController@deleteQuiz');
});