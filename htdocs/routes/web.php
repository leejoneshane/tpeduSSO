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
//Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
//Route::post('register', 'Auth\RegisterController@register');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
// Email Verification Routes...
//Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
//Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
//Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

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

Route::group(['prefix' => 'sync', 'middleware' => 'auth.admin'], function () {
    Route::get('/', 'SyncController@index')->name('sync');
    Route::get('ps/runtime_test', 'SyncController@ps_testForm');
    Route::post('ps/runtime_test', 'SyncController@ps_testForm')->name('sync.ps.runtime_test');
});

Route::group(['prefix' => 'bureau', 'middleware' => 'auth.admin'], function () {
    Route::get('/', 'BureauController@index')->name('bureau');
	Route::get('admin', 'BureauController@bureauAdminForm')->name('bureau.admin');
	Route::post('admin/new', 'BureauController@addBureauAdmin')->name('bureau.createAdmin');
	Route::post('admin/remove', 'BureauController@delBureauAdmin')->name('bureau.removeAdmin');
	Route::get('organization', 'BureauController@bureauOrgForm')->name('bureau.organization');
	Route::get('organization/{dc}/update', 'BureauController@bureauOrgEditForm');
	Route::post('organization/{dc}/update', 'BureauController@updateBureauOrg')->name('bureau.updateOrg');
	Route::post('organization/{dc}/remove', 'BureauController@removeBureauOrg')->name('bureau.removeOrg');
	Route::get('organization/new', 'BureauController@bureauOrgEditForm');
	Route::post('organization/new', 'BureauController@createBureauOrg')->name('bureau.createOrg');
	Route::get('organization/json', 'BureauController@bureauOrgJSONForm');
	Route::post('organization/json', 'BureauController@importBureauOrg')->name('bureau.jsonOrg');
	Route::get('group', 'BureauController@bureauGroupForm')->name('bureau.group');
	Route::post('group', 'BureauController@createBureauGroup')->name('bureau.createGroup');
	Route::post('group/{cn}/update', 'BureauController@updateBureauGroup')->name('bureau.updateGroup');
	Route::post('group/{cn}/remove', 'BureauController@removeBureauGroup')->name('bureau.removeGroup');
	Route::post('group/{cn}/member', 'BureauController@bureauMemberForm')->name('bureau.showMember');
	Route::get('people', 'BureauController@bureauPeopleSearchForm')->name('bureau.people');
	Route::get('people/{uuid}/update', 'BureauController@bureauPeopleEditForm')->name('bureau.updatePeople');
	Route::post('teacher/{uuid}/update', 'BureauController@updateBureauTeacher')->name('bureau.updateTeacher');
	Route::post('student/{uuid}/update', 'BureauController@updateBureauStudent')->name('bureau.updateStudent');
	Route::post('people/{uuid}/remove', 'BureauController@removeBureauPeople')->name('bureau.removePeople');
	Route::post('people/{uuid}/toggle', 'BureauController@toggleBureauPeople')->name('bureau.togglePeople');
	Route::post('people/{uuid}/undo', 'BureauController@undoBureauPeople')->name('bureau.undoPeople');
	Route::post('people/{uuid}/resetpass', 'BureauController@resetpass')->name('bureau.resetpassPeople');
	Route::get('people/new', 'BureauController@bureauPeopleEditForm');
	Route::post('people/new', 'BureauController@createBureauPeople')->name('bureau.createPeople');
	Route::get('people/json', 'BureauController@bureauPeopleJSONForm');
	Route::post('people/json', 'BureauController@importBureauPeople')->name('bureau.jsonPeople');
	Route::get('orgs/{area}', 'Api\schoolController@listOrgs');
	Route::get('units/{dc}', 'Api\schoolController@allOu');
	Route::get('roles/{dc}/{ou_id}', 'Api\schoolController@allRole');
	Route::get('classes/{dc}', 'Api\schoolController@listClasses');
});

Route::group(['prefix' => 'school', 'middleware' => 'auth.school'], function () {
    Route::get('{dc}', 'SchoolController@index')->name('school');
	Route::get('{dc}/admin', 'SchoolController@schoolAdminForm')->name('school.admin');
	Route::get('{dc}/profile', 'SchoolController@schoolProfileForm');
	Route::post('{dc}/profile', 'SchoolController@updateSchoolProfile')->name('school.profile');
	Route::get('{dc}/unit', 'SchoolController@schoolUnitForm')->name('school.unit');
	Route::post('{dc}/unit', 'SchoolController@createSchoolUnit')->name('school.createUnit');
	Route::post('{dc}/unit/{ou}/update', 'SchoolController@updateSchoolUnit')->name('school.updateUnit');
	Route::post('{dc}/unit/{ou}/remove', 'SchoolController@removeSchoolUnit')->name('school.removeUnit');
	Route::get('{dc}/unit/{ou}/role', 'SchoolController@schoolRoleForm')->name('school.role');
	Route::post('{dc}/unit/{ou}/role', 'SchoolController@createSchoolRole')->name('school.createRole');
	Route::post('{dc}/unit/{ou}/role/{role}/update', 'SchoolController@updateSchoolRole')->name('school.updateRole');
	Route::post('{dc}/unit/{ou}/role/{role}/remove', 'SchoolController@removeSchoolRole')->name('school.removeRole');
	Route::get('{dc}/subject', 'SchoolController@schoolSubjectForm')->name('school.subject');
	Route::post('{dc}/subject', 'SchoolController@createSchoolSubject')->name('school.createSubject');
	Route::post('{dc}/subject/{subject}/update', 'SchoolController@updateSchoolSubject')->name('school.updateSubject');
	Route::post('{dc}/subject/{subject}/remove', 'SchoolController@removeSchoolSubject')->name('school.removeSubject');
	Route::get('{dc}/class', 'SchoolController@schoolClassForm')->name('school.class');
	Route::post('{dc}/class', 'SchoolController@createSchoolClass')->name('school.createClass');
	Route::post('{dc}/class/{ou}/update', 'SchoolController@updateSchoolClass')->name('school.updateClass');
	Route::post('{dc}/class/{ou}/remove', 'SchoolController@removeSchoolClass')->name('school.removeClass');
	Route::get('{dc}/class/assign', 'SchoolController@schoolClassAssignForm');
	Route::post('{dc}/class/assign', 'SchoolController@assignSchoolClass')->name('school.assignClass');
	Route::get('{dc}/teacher', 'SchoolController@schoolTeacherSearchForm')->name('school.teacher');
	Route::get('{dc}/teacher/{uuid}/update', 'SchoolController@schoolTeacherEditForm');
	Route::post('{dc}/teacher/{uuid}/update', 'SchoolController@updateSchoolTeacher')->name('school.updateTeacher');
	Route::get('{dc}/teacher/new', 'SchoolController@schoolTeacherEditForm');
	Route::post('{dc}/teacher/new', 'SchoolController@createSchoolTeacher')->name('school.createTeacher');
	Route::get('{dc}/teacher/json', 'SchoolController@schoolTeacherJSONForm');
	Route::post('{dc}/teacher/json', 'SchoolController@importSchoolTeacher')->name('school.jsonTeacher');
	Route::get('{dc}/student', 'SchoolController@schoolStudentSearchForm')->name('school.student');
	Route::get('{dc}/student/{uuid}/update', 'SchoolController@schoolStudentEditForm');
	Route::post('{dc}/student/{uuid}/update', 'SchoolController@updateSchoolStudent')->name('school.updateStudent');
	Route::get('{dc}/student/new', 'SchoolController@schoolStudentEditForm');
	Route::post('{dc}/student/new', 'SchoolController@createSchoolStudent')->name('school.createStudent');
	Route::get('{dc}/student/json', 'SchoolController@schoolStudentJSONForm');
	Route::post('{dc}/student/json', 'SchoolController@importSchoolStudent')->name('school.jsonStudent');
	Route::get('{dc}/roles/{ou_id}', 'Api\schoolController@allRole');
	Route::get('{dc}/classes/{grade}', 'Api\schoolController@listClasses');
	Route::get('{dc}/teachers/{ou}', 'Api\schoolController@listTeachers');
	Route::post('people/{uuid}/remove', 'SchoolController@remove')->name('school.remove');
	Route::post('people/{uuid}/toggle', 'SchoolController@toggle')->name('school.toggle');
	Route::post('people/{uuid}/undo', 'SchoolController@undo')->name('school.undo');
	Route::post('people/{uuid}/resetpass', 'SchoolController@resetpass')->name('school.resetpass');
});
