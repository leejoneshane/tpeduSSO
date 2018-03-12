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

Route::get('/schoolAdmin', 'SchoolController@showSchoolAdminSettingForm');
Route::post('/schoolAdmin', 'SchoolController@addSchoolAdmin')->name('schoolAdmin');
Route::post('/schoolAdminRemove', 'SchoolController@delSchoolAdmin')->name('schoolAdminRemove');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/changePassword', 'HomeController@showChangePasswordForm');
    Route::post('/changePassword', 'HomeController@changePassword')->name('changePassword');
    Route::get('/changeAccount', 'HomeController@showChangeAccountForm');
    Route::post('/changeAccount', 'HomeController@changeAccount')->name('changeAccount');
    Route::get('/profile', 'HomeController@showProfileForm');
    Route::post('/profile', 'HomeController@changeProfile')->name('profile');
    Route::get('/oauth', 'oauthController@index')->name('oauth');
});

Route::group(['middleware' => 'auth.school'], function () {
    Route::get('/school', 'SchoolController@index')->name('school');
	Route::get('/school/admin', 'SchoolController@schoolAdminForm')->name('school.admin');
	Route::get('/school/profile', 'SchoolController@schoolProfileForm');
	Route::post('/school/profile', 'SchoolController@updateSchoolProfile')->name('school.profile');
	Route::get('/school/unit', 'SchoolController@schoolUnitForm');
	Route::post('/school/unit', 'SchoolController@createSchoolUnit')->name('school.unit');
	Route::post('/school/unit/{ou}', 'SchoolController@updateSchoolUnit')->name('school.updateunit');
	Route::get('/school/unit/{ou}', 'SchoolController@removeSchoolUnit')->name('school.removeunit');
	Route::get('/school/class', 'SchoolController@schoolClassForm');
	Route::post('/school/class', 'SchoolController@createSchoolClass')->name('school.class');
	Route::post('/school/class/{ou}', 'SchoolController@updateSchoolClass')->name('school.updateclass');
	Route::get('/school/class/{ou}', 'SchoolController@removeSchoolClass')->name('school.removeclass');
	Route::get('/school/role', 'SchoolController@schoolRoleForm');
	Route::post('/school/role', 'SchoolController@createSchoolRole')->name('school.role');
	Route::post('/school/role/{rid}', 'SchoolController@updateSchoolRole')->name('school.updaterole');
	Route::get('/school/role/{rid}', 'SchoolController@removeSchoolRole')->name('school.removerole');
});

