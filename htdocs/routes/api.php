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
    Route::get('email', 'Api\profileController@email')->middleware('scope:email');
    Route::get('user', 'Api\profileController@user')->middleware('scope:user');
    Route::patch('user', 'Api\profileController@updateUser')->middleware('scope:user');
    Route::get('idno', 'Api\profileController@idno')->middleware('scope:idno');
    Route::get('profile', 'Api\profileController@profile')->middleware('scope:profile');
    Route::patch('account', 'Api\profileController@updateAccount')->middleware('scope:account');
    Route::patch('school/{dc}', 'Api\schoolController@updateSchool')->middleware('scope:schoolAdmin');
    Route::post('school/{dc}/people', 'Api\schoolController@peopleAdd')->middleware('scope:schoolAdmin');
    Route::patch('school/{dc}/people/{uuid}', 'Api\schoolController@peopleUpdate')->middleware('scope:schoolAdmin');
    Route::delete('school/{dc}/people/{uuid}', 'Api\schoolController@peopleRemove')->middleware('scope:schoolAdmin');
    Route::get('school/{dc}/people/{uuid}', 'Api\schoolController@people')->middleware('scope:schoolAdmin');
});

Route::group(['middleware' => 'clientid:school'], function () {
    Route::get('school', 'Api\schoolController@all');
    Route::get('school/{dc}', 'Api\schoolController@one');
    Route::get('school/{dc}/subject', 'Api\schoolController@allSubject');
    Route::get('school/{dc}/subject/{subj_id}', 'Api\schoolController@oneSubject');
    Route::get('school/{dc}/ou', 'Api\schoolController@allOu');
    Route::get('school/{dc}/ou/{ou_id}', 'Api\schoolController@oneOu');
    Route::get('school/{dc}/ou/{ou_id}/role', 'Api\schoolController@allRole');
    Route::get('school/{dc}/ou/{ou_id}/role/{role_id}', 'Api\schoolController@oneRole');
    Route::get('school/{dc}/class', 'Api\schoolController@allClass');
    Route::get('school/{dc}/class/{class_id}', 'Api\schoolController@oneClass');
    Route::get('school/{dc}/class/{class_id}/teachers', 'Api\schoolController@allTeachers');
    Route::get('school/{dc}/class/{class_id}/students', 'Api\schoolController@allStudents');
});
