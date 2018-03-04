<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', 'Api\profileController@user')->middleware('scope:user');
    Route::patch('/user', 'Api\profileController@updateUser')->middleware('scope:user');
    Route::get('/idno', 'Api\profileController@idno')->middleware('scope:idno');
    Route::get('/profile', 'Api\profileController@profile')->middleware('scope:profile');
    Route::patch('/account', 'Api\profileController@updateAccount')->middleware('scope:account');
    Route::patch('/school/{sid}', 'Api\schoolController@updateSchool')->middleware('scope:schoolAdmin');
    Route::post('/school/{sid}/people', 'Api\schoolController@peopleAdd')->middleware('scope:schoolAdmin');
    Route::patch('/school/{sid}/people/{uuid}', 'Api\schoolController@peopleUpdate')->middleware('scope:schoolAdmin');
    Route::delete('/school/{sid}/people/{uuid}', 'Api\schoolController@peopleRemove')->middleware('scope:schoolAdmin');
    Route::get('/school/{sid}/people/{uuid}', 'Api\schoolController@people')->middleware('scope:schoolAdmin');
});

Route::group(['middleware' => 'clientid:school'], function () {
    Route::get('/school', 'Api\schoolController@all');
    Route::get('/school/{sid}', 'Api\schoolController@one');
    Route::get('/school/{sid}/ou', 'Api\schoolController@allOu');
    Route::get('/school/{sid}/ou/{ou_id}', 'Api\schoolController@oneOu');
    Route::get('/school/{sid}/ou/{ou_id}/role', 'Api\schoolController@allRole');
    Route::get('/school/{sid}/ou/{ou_id}/role/{role_id}', 'Api\schoolController@oneRole');
    Route::get('/school/{sid}/class', 'Api\schoolController@allClass');
    Route::get('/school/{sid}/class/{class_id}', 'Api\schoolController@oneClass');
    Route::get('/school/{sid}/class/{class_id}/teachers', 'Api\schoolController@allTeachers');
    Route::get('/school/{sid}/class/{class_id}/students', 'Api\schoolController@allStudents');
});