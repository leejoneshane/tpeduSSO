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
    Route::get('validate', function() { return response()->json(['data' => 'Token is valid!' ]); });
    Route::get('logout', 'Api\profileController@logout');
    Route::get('me', 'Api\profileController@me')->middleware('scope:me');
    Route::get('email', 'Api\profileController@email')->middleware('scope:email');
    Route::get('user', 'Api\profileController@user')->middleware('scope:user');
    Route::get('idno', 'Api\profileController@idno')->middleware('scope:idno');
    Route::get('profile', 'Api\profileController@profile')->middleware('scope:profile');
    Route::patch('user', 'Api\profileController@updateUser')->middleware('scope:account');
    Route::patch('account', 'Api\profileController@updateAccount')->middleware('scope:account');
    Route::patch('school/{dc}', 'Api\schoolController@updateSchool')->middleware('scope:schoolAdmin');
    Route::post('school/{dc}/people', 'Api\schoolController@peopleAdd')->middleware('scope:schoolAdmin');
    Route::patch('school/{dc}/people/{uuid}', 'Api\schoolController@peopleUpdate')->middleware('scope:schoolAdmin');
    Route::delete('school/{dc}/people/{uuid}', 'Api\schoolController@peopleRemove')->middleware('scope:schoolAdmin');
    Route::get('school/{dc}/people/{uuid}', 'Api\schoolController@people')->middleware('scope:schoolAdmin');
});

Route::group(['middleware' => 'clientid:schoolAdmin'], function () {
    Route::patch('school/{dc}', 'Api\schoolController@updateSchool');
    Route::post('school/{dc}/people', 'Api\schoolController@peopleAdd');
    Route::patch('school/{dc}/people/{uuid}', 'Api\schoolController@peopleUpdate');
    Route::delete('school/{dc}/people/{uuid}', 'Api\schoolController@peopleRemove');
    Route::get('school/{dc}/people/{uuid}', 'Api\schoolController@people');
});

Route::group(['middleware' => 'clientid:school'], function () {
    Route::get('school', 'Api\schoolController@all');
    Route::get('school/{dc}', 'Api\schoolController@one');
    Route::get('school/{dc}/teachers', 'Api\schoolController@allTeachersByOrg');
    Route::get('school/{dc}/subject', 'Api\schoolController@allSubject');
    Route::get('school/{dc}/subject/{subj_id}', 'Api\schoolController@oneSubject');
    Route::get('school/{dc}/subject/{subj_id}/teachers', 'Api\schoolController@allTeachersBySubject');
    Route::get('school/{dc}/subject/{subj_id}/classes', 'Api\schoolController@allClassesBySubject');
    Route::get('school/{dc}/ou', 'Api\schoolController@allOu');
    Route::get('school/{dc}/ou/{ou_id}', 'Api\schoolController@oneOu');
    Route::get('school/{dc}/ou/{ou_id}/teachers', 'Api\schoolController@allTeachersByUnit');
    Route::get('school/{dc}/ou/{ou_id}/role', 'Api\schoolController@allRole');
    Route::get('school/{dc}/ou/{ou_id}/role/{role_id}', 'Api\schoolController@oneRole');
    Route::get('school/{dc}/ou/{ou_id}/role/{role_id}/teachers', 'Api\schoolController@allTeachersByRole');
    Route::get('school/{dc}/class', 'Api\schoolController@allClass');
    Route::get('school/{dc}/class/{class_id}', 'Api\schoolController@oneClass');
    Route::get('school/{dc}/class/{class_id}/teachers', 'Api\schoolController@allTeachersByClass');
    Route::get('school/{dc}/class/{class_id}/students', 'Api\schoolController@allStudentsByClass');
    Route::get('school/{dc}/class/{class_id}/subjects', 'Api\schoolController@allSubjectsByClass');
});

Route::group(['prefix' => 'v2'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('validate', function() { return response()->json(['data' => 'Token is valid!']); });
        Route::get('logout', 'Api_V2\v2_profileController@logout');
        Route::get('me', 'Api_V2\v2_profileController@me')->middleware('scope:me');
        Route::get('email', 'Api_V2\v2_profileController@email')->middleware('scope:email');
        Route::get('user', 'Api_V2\v2_profileController@user')->middleware('scope:user');
        Route::get('idno', 'Api_V2\v2_profileController@idno')->middleware('scope:idno');
        Route::get('profile', 'Api_V2\v2_profileController@profile')->middleware('scope:profile');
        Route::patch('user', 'Api_V2\v2_profileController@updateUser')->middleware('scope:account');
        Route::patch('account', 'Api_V2\v2_profileController@updateAccount')->middleware('scope:account');
        Route::patch('school/{dc}', 'Api_V2\v2_schoolController@updateSchool')->middleware('scope:schoolAdmin');
        Route::post('school/{dc}/people', 'Api_V2\v2_schoolController@peopleAdd')->middleware('scope:schoolAdmin');
        Route::patch('school/{dc}/people/{uuid}', 'Api_V2\v2_schoolController@peopleUpdate')->middleware('scope:schoolAdmin');
        Route::delete('school/{dc}/people/{uuid}', 'Api_V2\v2_schoolController@peopleRemove')->middleware('scope:schoolAdmin');
        Route::get('school/{dc}/people/{uuid}', 'Api_V2\v2_schoolController@people')->middleware('scope:schoolAdmin');
    });

    Route::group(['middleware' => 'clientid:schoolAdmin'], function () {
        Route::patch('school/{dc}', 'Api\schoolController@updateSchool');
        Route::post('school/{dc}/people', 'Api\schoolController@peopleAdd');
        Route::patch('school/{dc}/people/{uuid}', 'Api\schoolController@peopleUpdate');
        Route::delete('school/{dc}/people/{uuid}', 'Api\schoolController@peopleRemove');
        Route::get('school/{dc}/people/{uuid}', 'Api\schoolController@people');
    });

    Route::group(['middleware' => 'clientid:school'], function () {
        Route::get('school', 'Api_V2\v2_schoolController@all');
        Route::get('school/{dc}', 'Api_V2\v2_schoolController@one');
        Route::get('school/{dc}/people', 'Api_V2\v2_schoolController@peopleSearch');
        Route::get('school/{dc}/teachers', 'Api_V2\v2_schoolController@allTeachersByOrg');
        Route::get('school/{dc}/subject', 'Api_V2\v2_schoolController@allSubject');
        Route::get('school/{dc}/subject/{subj_id}', 'Api_V2\v2_schoolController@oneSubject');
        Route::get('school/{dc}/subject/{subj_id}/teachers', 'Api_V2\v2_schoolController@allTeachersBySubject');
        Route::get('school/{dc}/subject/{subj_id}/classes', 'Api_V2\v2_schoolController@allClassesBySubject');
        Route::get('school/{dc}/ou', 'Api_V2\v2_schoolController@allOu');
        Route::get('school/{dc}/ou/{ou_id}', 'Api_V2\v2_schoolController@oneOu');
        Route::get('school/{dc}/ou/{ou_id}/teachers', 'Api_V2\v2_schoolController@allTeachersByUnit');
        Route::get('school/{dc}/ou/{ou_id}/role', 'Api_V2\v2_schoolController@allRole');
        Route::get('school/{dc}/ou/{ou_id}/role/{role_id}', 'Api_V2\v2_schoolController@oneRole');
        Route::get('school/{dc}/ou/{ou_id}/role/{role_id}/teachers', 'Api_V2\v2_schoolController@allTeachersByRole');
        Route::get('school/{dc}/class', 'Api_V2\v2_schoolController@allClass');
        Route::get('school/{dc}/class/{class_id}', 'Api_V2\v2_schoolController@oneClass');
        Route::get('school/{dc}/class/{class_id}/teachers', 'Api_V2\v2_schoolController@allTeachersByClass');
        Route::get('school/{dc}/class/{class_id}/students', 'Api_V2\v2_schoolController@allStudentsByClass');
        Route::get('school/{dc}/class/{class_id}/subjects', 'Api_V2\v2_schoolController@allSubjectsByClass');
    });
});
