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

//Auth::routes();
// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/', 'HomeController@index')->middleware(['auth', 'auth.email'])->name('home');

Route::get('schoolAdmin', 'SchoolController@showSchoolAdminSettingForm');
Route::post('schoolAdmin', 'SchoolController@addSchoolAdmin')->name('schoolAdmin');
Route::post('schoolAdminRemove', 'SchoolController@delSchoolAdmin')->name('schoolAdminRemove');

Route::group(['middleware' => 'auth'], function () {
    Route::get('changePassword', 'HomeController@showChangePasswordForm');
    Route::post('changePassword', 'HomeController@changePassword')->name('changePassword');
    Route::get('changeAccount', 'HomeController@showChangeAccountForm');
    Route::post('changeAccount', 'HomeController@changeAccount')->name('changeAccount');
    Route::get('profile', 'HomeController@showProfileForm');
    Route::post('profile', 'HomeController@changeProfile')->name('profile');
    Route::get('oauth', 'oauthController@index')->name('oauth');
});

Route::group(['prefix' => 'school', 'middleware' => 'auth.school'], function () {
    Route::get('/', 'SchoolController@index')->name('school');
	Route::get('admin', 'SchoolController@schoolAdminForm')->name('school.admin');
	Route::get('profile', 'SchoolController@schoolProfileForm');
	Route::post('profile', 'SchoolController@updateSchoolProfile')->name('school.profile');
	Route::get('unit', 'SchoolController@schoolUnitForm')->name('school.unit');
	Route::post('unit', 'SchoolController@createSchoolUnit')->name('school.createUnit');
	Route::post('unit/{ou}/update', 'SchoolController@updateSchoolUnit')->name('school.updateUnit');
	Route::post('unit/{ou}/remove', 'SchoolController@removeSchoolUnit')->name('school.removeUnit');
	Route::get('unit/{ou}/role', 'SchoolController@schoolRoleForm')->name('school.role');
	Route::post('unit/{ou}/role', 'SchoolController@createSchoolRole')->name('school.createRole');
	Route::post('unit/{ou}/role/{role}/update', 'SchoolController@updateSchoolRole')->name('school.updateRole');
	Route::post('unit/{ou}/role/{role}/remove', 'SchoolController@removeSchoolRole')->name('school.removeRole');
	Route::get('class', 'SchoolController@schoolClassForm')->name('school.class');
	Route::post('class', 'SchoolController@createSchoolClass')->name('school.createClass');
	Route::post('class/{ou}/update', 'SchoolController@updateSchoolClass')->name('school.updateClass');
	Route::post('class/{ou}/remove', 'SchoolController@removeSchoolClass')->name('school.removeClass');
	Route::get('class/assign', 'SchoolController@schoolClassAssignForm');
	Route::post('class/assign', 'SchoolController@assignSchoolClass')->name('school.assignClass');
	Route::get('teacher', 'SchoolController@schoolTeacherSearchForm')->name('school.teacher');
	Route::get('teacher/{uuid}/update', 'SchoolController@schoolTeacherEditForm');
	Route::post('teacher/{uuid}/update', 'SchoolController@updateSchoolTeacher')->name('school.updateTeacher');
	Route::post('teacher/{uuid}/remove', 'SchoolController@removeSchoolTeacher')->name('school.removeTeacher');
	Route::post('teacher/{uuid}/toggle', 'SchoolController@toggleSchoolTeacher')->name('school.toggleTeacher');
	Route::post('teacher/{uuid}/undo', 'SchoolController@undoSchoolTeacher')->name('school.undoTeacher');
	Route::get('teacher/new', 'SchoolController@schoolTeacherEditForm');
	Route::post('teacher/new', 'SchoolController@createSchoolTeacher')->name('school.createTeacher');
	Route::get('teacher/json', 'SchoolController@schoolTeacherJSONForm');
	Route::post('teacher/json', 'SchoolController@importSchoolTeacher')->name('school.jsonTeacher');
	Route::get('student', 'SchoolController@schoolStudentSearchForm')->name('school.student');
	Route::get('student/{uuid}/update', 'SchoolController@schoolStudentEditForm');
	Route::post('student/{uuid}/update', 'SchoolController@updateSchoolStudent')->name('school.updateStudent');
	Route::post('student/{uuid}/remove', 'SchoolController@removeSchoolTeacher')->name('school.removeStudent');
	Route::post('student/{uuid}/toggle', 'SchoolController@toggleSchoolTeacher')->name('school.toggleStudent');
	Route::post('student/{uuid}/undo', 'SchoolController@undoSchoolTeacher')->name('school.undoStudent');
	Route::get('student/new', 'SchoolController@schoolStudentEditForm');
	Route::post('student/new', 'SchoolController@createSchoolStudent')->name('school.createStudent');
	Route::get('student/json', 'SchoolController@schoolStudentJSONForm');
	Route::post('student/json', 'SchoolController@importSchoolStudent')->name('school.jsonStudent');
	Route::get('roles/{dc}/{ou_id}', 'Api\schoolController@allRole');
	Route::get('classes/{dc}/{grade}', 'Api\schoolController@listClasses');
	Route::get('teachers/{dc}/{ou}', 'Api\schoolController@listTeachers');
});

