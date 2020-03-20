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
Route::get('login/{provider}', 'Auth\LoginController@redirect');
Route::get('login/{provider}/callback', 'Auth\LoginController@handleCallback');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('api/logout', 'Api\profileController@logout');
Route::get('api/v2/logout', 'Api_V2\v2_profileController@logout');
// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
// Email Verification Routes...
Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

//Passport::routes();
Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')->name('passport.token');
Route::group(['prefix' => 'oauth', 'middleware' => 'auth'], function () {
	//管理介面
	Route::get('/', 'OauthController@index')->name('oauth');
	Route::post('tokens/{token_id}/revoke', 'OauthController@revokeToken')->name('revokeToken');
	//RouteRegistrar::forAuthorization()
	Route::get('authorize', '\Laravel\Passport\Http\Controllers\AuthorizationController@authorize')->name('passport.authorizations.authorize'); //->middleware('age')
	Route::post('authorize', '\Laravel\Passport\Http\Controllers\ApproveAuthorizationController@approve')->name('passport.authorizations.approve');
	Route::delete('authorize', '\Laravel\Passport\Http\Controllers\DenyAuthorizationController@deny')->name('passport.authorizations.deny');
	//RouteRegistrar::forAccessTokens()
	//Route::get('tokens', '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser')->name('passport.tokens.index');
	//Route::delete('tokens/{token_id}', '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@destroy')->name('passport.tokens.destroy');
	//RouteRegistrar::forTransientTokens()
	//Route::post('token/refresh', '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh')->name('passport.token.refresh');
	//RouteRegistrar::forClients()
	//Route::get('clients', '\Laravel\Passport\Http\Controllers\ClientController@forUser')->name('passport.clients.index');
	//Route::post('clients', '\Laravel\Passport\Http\Controllers\ClientController@store')->name('passport.clients.store');
	//Route::put('clients/{client_id}', '\Laravel\Passport\Http\Controllers\ClientController@update')->name('passport.clients.update');
	//Route::delete('clients/{client_id}', '\Laravel\Passport\Http\Controllers\ClientController@destroy')->name('passport.clients.destroy');
	//RouteRegistrar::forPersonalAccessTokens()
	//Route::get('scopes', '\Laravel\Passport\Http\Controllers\ScopeController@all')->name('passport.scopes.index');
	//Route::get('personal-access-tokens', '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@forUser')->name('passport.personal.tokens.index');
	//Route::post('personal-access-tokens', '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@store')->name('passport.personal.tokens.store');
	//Route::delete('personal-access-tokens/{token_id}', '\Laravel\Passport\Http\Controllers\PersonalAccessTokenController@destroy')->name('passport.personal.tokens.destroy');
});

Route::get('schoolAdmin', 'SchoolController@showSchoolAdminSettingForm');
Route::post('schoolAdmin', 'SchoolController@addSchoolAdmin')->name('schoolAdmin');
Route::post('schoolAdminRemove', 'SchoolController@delSchoolAdmin')->name('schoolAdminRemove');
Route::get('changePassword', 'HomeController@showChangePasswordForm');
Route::post('changePassword', 'HomeController@changePassword')->name('changePassword');
Route::get('changeAccount', 'HomeController@showChangeAccountForm');
Route::post('changeAccount', 'HomeController@changeAccount')->name('changeAccount');
Route::get('3party', 'GuestController@apply')->name('3party');
Route::post('3party/store', 'GuestController@store')->name('3party.store');
Route::post('3party/update', 'GuestController@edit')->name('3party.edit');

Route::group(['middleware' => 'auth'], function () {
	Route::get('/', 'HomeController@index')->name('home'); //->middleware('verified')
    Route::get('profile', 'HomeController@showProfileForm');
    Route::post('profile', 'HomeController@changeProfile')->name('profile');
	Route::get('gsuite/sync', 'HomeController@syncToGsuite')->name('createGsuite');
	Route::get('socialite', 'OauthController@socialite')->name('socialite');
	Route::post('socialite/remove', 'OauthController@removeSocialite')->name('socialite.remove');
});

Route::group(['prefix' => 'parent', 'middleware' => 'auth.parent'], function () {
	Route::get('/', 'ParentController@index')->middleware('verified')->name('parent');
	Route::get('link', 'ParentController@listLink')->name('parent.listLink');
	Route::get('link/new', 'ParentController@showLinkForm')->name('parent.showLinkForm');
	Route::post('link/apply', 'ParentController@applyLink')->name('parent.applyLink');
	Route::post('link/{id}/remove', 'ParentController@removeLink')->name('parent.removeLink');
	Route::get('authorize', 'ParentController@showGuardianAuthForm');
	Route::post('authorize', 'ParentController@applyGuardianAuth')->name('parent.guardianAuth');
	Route::get('qrcode/{uuid}', 'ParentController@qrcodeBind');
});

Route::group(['prefix' => 'tutor', 'middleware' => 'auth.tutor'], function () {
	Route::get('/', 'TutorController@index')->name('tutor');
	Route::get('{dc}/{ou}/student', 'TutorController@classStudentForm')->name('tutor.student');
	Route::get('{dc}/{ou}/student/{uuid}/update', 'TutorController@studentEditForm');
	Route::post('{dc}/{ou}/student/{uuid}/update', 'TutorController@updateStudent')->name('tutor.updateStudent');
	Route::post('{dc}/people/{uuid}/remove', 'SchoolController@remove')->name('tutor.remove');
	Route::post('{dc}/people/{uuid}/toggle', 'SchoolController@toggle')->name('tutor.toggle');
	Route::post('{dc}/people/{uuid}/undo', 'SchoolController@undo')->name('tutor.undo');
	Route::post('{dc}/people/{uuid}/resetpass', 'SchoolController@resetpass')->name('tutor.resetpass');
	Route::get('{dc}/{ou}/link', 'TutorController@classLinkForm')->name('tutor.link');
	Route::post('link/deny/{id}', 'TutorController@denyLink')->name('tutor.denyLink');
	Route::post('link/verify/{id}', 'TutorController@verifyLink')->name('tutor.verifyLink');
	Route::get('{dc}/{ou}/qrcode', 'TutorController@classQrcodeForm')->name('tutor.qrcode');
	Route::post('{dc}/{ou}/qrcode/{uuid}', 'TutorController@qrcodeGenerate')->name('tutor.generateQrcode');
	Route::post('{dc}/{ou}/qrcode/{uuid}/remove', 'TutorController@qrcodeRemove')->name('tutor.removeQrcode');
});

Route::group(['prefix' => 'sync', 'middleware' => 'auth.admin'], function () {
    Route::get('/', 'SyncController@index')->name('sync');
    Route::get('ps/runtime_test', 'SyncController@ps_testForm');
    Route::post('ps/runtime_test', 'SyncController@ps_testForm')->name('sync.ps.runtime_test');
    Route::get('ps/sync_school', 'SyncController@ps_syncOrg')->name('sync.ps.sync_school');
    Route::get('ps/sync_class', 'SyncController@ps_syncClassForm');
    Route::post('ps/sync_class', 'SyncController@ps_syncClassForm')->name('sync.ps.sync_class');
    Route::get('ps/sync_subject', 'SyncController@ps_syncSubjectForm');
    Route::post('ps/sync_subject', 'SyncController@ps_syncSubjectForm')->name('sync.ps.sync_subject');
	Route::get('ps/sync_teacher', 'SyncController@ps_syncTeacherForm');
    Route::post('ps/sync_teacher', 'SyncController@ps_syncTeacherForm')->name('sync.ps.sync_teacher');
    Route::get('ps/sync_student', 'SyncController@ps_syncStudentForm');
    Route::post('ps/sync_student', 'SyncController@ps_syncStudentForm')->name('sync.ps.sync_student');
    Route::get('ps/auto', 'SyncController@ps_autoSync');
    Route::post('ps/auto', 'SyncController@ps_autoSync')->name('sync.ps.auto');
    Route::get('js/runtime_test', 'SyncController@js_testForm');
    Route::post('js/runtime_test', 'SyncController@js_testForm')->name('sync.js.runtime_test');
    Route::get('js/sync_school', 'SyncController@js_syncOrg')->name('sync.js.sync_school');
    Route::get('js/sync_ou', 'SyncController@js_syncOuForm');
    Route::post('js/sync_ou', 'SyncController@js_syncOuForm')->name('sync.js.sync_ou');
    Route::get('js/sync_class', 'SyncController@js_syncClassForm');
    Route::post('js/sync_class', 'SyncController@js_syncClassForm')->name('sync.js.sync_class');
    Route::get('js/sync_subject', 'SyncController@js_syncSubjectForm');
    Route::post('js/sync_subject', 'SyncController@js_syncSubjectForm')->name('sync.js.sync_subject');
	Route::get('js/sync_teacher', 'SyncController@js_syncTeacherForm');
    Route::post('js/sync_teacher', 'SyncController@js_syncTeacherForm')->name('sync.js.sync_teacher');
    Route::get('js/sync_student', 'SyncController@js_syncStudentForm');
    Route::post('js/sync_student', 'SyncController@js_syncStudentForm')->name('sync.js.sync_student');
    Route::get('js/auto', 'SyncController@js_autoSync');
    Route::post('js/auto', 'SyncController@js_autoSync')->name('sync.js.auto');
    Route::get('hs/runtime_test', 'SyncController@hs_testForm');
    Route::post('hs/runtime_test', 'SyncController@hs_testForm')->name('sync.hs.runtime_test');
    Route::get('hs/sync_school', 'SyncController@hs_syncOrg')->name('sync.hs.sync_school');
    Route::get('hs/sync_ou', 'SyncController@hs_syncOuForm');
    Route::post('hs/sync_ou', 'SyncController@hs_syncOuForm')->name('sync.hs.sync_ou');
    Route::get('hs/sync_class', 'SyncController@hs_syncClassForm');
    Route::post('hs/sync_class', 'SyncController@hs_syncClassForm')->name('sync.hs.sync_class');
    Route::get('hs/sync_subject', 'SyncController@hs_syncSubjectForm');
    Route::post('hs/sync_subject', 'SyncController@hs_syncSubjectForm')->name('sync.hs.sync_subject');
	Route::get('hs/sync_teacher', 'SyncController@hs_syncTeacherForm');
    Route::post('hs/sync_teacher', 'SyncController@hs_syncTeacherForm')->name('sync.hs.sync_teacher');
    Route::get('hs/sync_student', 'SyncController@hs_syncStudentForm');
    Route::post('hs/sync_student', 'SyncController@hs_syncStudentForm')->name('sync.hs.sync_student');
    Route::get('hs/auto', 'SyncController@hs_autoSync');
    Route::post('hs/auto', 'SyncController@hs_autoSync')->name('sync.hs.auto');
    Route::get('fix/remove_fake', 'SyncController@removeFake')->name('sync.remove_fake');
    Route::get('fix/remove_deleted', 'SyncController@removeDeleted')->name('sync.remove_deleted');
    Route::get('fix/remove_parent', 'SyncController@removeParent')->name('sync.remove_parent');
	Route::get('fix/remove_gsuite', 'SyncController@removeGsuite')->name('sync.remove_gsuite');
	Route::get('fix/transfer_domain', 'SyncController@transferDomain')->name('sync.transfer_domain');
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
	Route::get('project', 'BureauController@listProjects')->name('bureau.project');
	Route::get('project/new', 'BureauController@createProject')->name('bureau.createProject');
	Route::post('project/store', 'BureauController@storeProject')->name('bureau.storeProject');
	Route::get('project/update/{uuid}', 'BureauController@projectEditForm')->name('bureau.updateProject');
	Route::post('project/remove/{uuid}', 'BureauController@removeProject')->name('bureau.removeProject');
	Route::get('project//deny/{uuid}', 'BureauController@showDenyProjectForm');
	Route::post('project/deny/{uuid}', 'BureauController@denyProject')->name('bureau.denyProject');
	Route::post('project/pass/{uuid}', 'BureauController@passProject')->name('bureau.passProject');
	Route::get('client', 'BureauController@listClients')->name('bureau.client');
	Route::get('client/update/{uuid}', 'BureauController@updateClient');
	Route::post('client/update/{uuid}', 'BureauController@storeClient')->name('bureau.updateClient');
	Route::post('client/toggle/{uuid}', 'BureauController@toggleClient')->name('bureau.toggleClient');
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
    Route::get('{dc}/sync_unit', 'SyncController@syncOuHelp');
    Route::post('{dc}/sync_unit', 'SyncController@syncOuHelp')->name('school.sync_unit');
	Route::get('{dc}/subject', 'SchoolController@schoolSubjectForm')->name('school.subject');
	Route::post('{dc}/subject', 'SchoolController@createSchoolSubject')->name('school.createSubject');
	Route::post('{dc}/subject/{subject}/update', 'SchoolController@updateSchoolSubject')->name('school.updateSubject');
	Route::post('{dc}/subject/{subject}/remove', 'SchoolController@removeSchoolSubject')->name('school.removeSubject');
    Route::get('{dc}/sync_subject', 'SyncController@syncSubjectHelp');
    Route::post('{dc}/sync_subject', 'SyncController@syncSubjectHelp')->name('school.sync_subject');
	Route::get('{dc}/class', 'SchoolController@schoolClassForm')->name('school.class');
	Route::post('{dc}/class', 'SchoolController@createSchoolClass')->name('school.createClass');
	Route::post('{dc}/class/{ou}/update', 'SchoolController@updateSchoolClass')->name('school.updateClass');
	Route::post('{dc}/class/{ou}/remove', 'SchoolController@removeSchoolClass')->name('school.removeClass');
	Route::get('{dc}/class/assign', 'SchoolController@schoolClassAssignForm');
	Route::post('{dc}/class/assign', 'SchoolController@assignSchoolClass')->name('school.assignClass');
    Route::get('{dc}/sync_class', 'SyncController@syncClassHelp');
    Route::post('{dc}/sync_class', 'SyncController@syncClassHelp')->name('school.sync_class');
	Route::get('{dc}/teacher', 'SchoolController@schoolTeacherSearchForm')->name('school.teacher');
	Route::get('{dc}/teacher/{uuid}/update', 'SchoolController@schoolTeacherEditForm');
	Route::post('{dc}/teacher/{uuid}/update', 'SchoolController@updateSchoolTeacher')->name('school.updateTeacher');
	Route::get('{dc}/teacher/new', 'SchoolController@schoolTeacherEditForm');
	Route::post('{dc}/teacher/new', 'SchoolController@createSchoolTeacher')->name('school.createTeacher');
	Route::get('{dc}/teacher/json', 'SchoolController@schoolTeacherJSONForm');
	Route::post('{dc}/teacher/json', 'SchoolController@importSchoolTeacher')->name('school.jsonTeacher');
    Route::get('{dc}/sync_teacher', 'SyncController@syncTeacherHelp');
    Route::post('{dc}/sync_teacher', 'SyncController@syncTeacherHelp')->name('school.sync_teacher');
	Route::get('{dc}/student', 'SchoolController@schoolStudentSearchForm')->name('school.student');
	Route::get('{dc}/student/{uuid}/update', 'SchoolController@schoolStudentEditForm');
	Route::post('{dc}/student/{uuid}/update', 'SchoolController@updateSchoolStudent')->name('school.updateStudent');
	Route::get('{dc}/student/new', 'SchoolController@schoolStudentEditForm');
	Route::post('{dc}/student/new', 'SchoolController@createSchoolStudent')->name('school.createStudent');
	Route::get('{dc}/student/json', 'SchoolController@schoolStudentJSONForm');
	Route::post('{dc}/student/json', 'SchoolController@importSchoolStudent')->name('school.jsonStudent');
    Route::get('{dc}/sync_student', 'SyncController@syncStudentHelp');
    Route::post('{dc}/sync_student', 'SyncController@syncStudentHelp')->name('school.sync_student');
	Route::get('{dc}/roles/{ou_id}', 'Api\schoolController@allRole');
	Route::get('{dc}/classes/{grade}', 'Api\schoolController@listClasses');
	Route::get('{dc}/teachers/{ou}', 'Api\schoolController@listTeachers');
	Route::post('{dc}/people/{uuid}/remove', 'SchoolController@remove')->name('school.remove');
	Route::post('{dc}/people/{uuid}/toggle', 'SchoolController@toggle')->name('school.toggle');
	Route::post('{dc}/people/{uuid}/undo', 'SchoolController@undo')->name('school.undo');
	Route::post('{dc}/people/{uuid}/resetpass', 'SchoolController@resetpass')->name('school.resetpass');
	Route::get('{dc}/tokens', 'SchoolController@schoolToken')->name('school.tokens');
	Route::get('{dc}/tokens/new', 'SchoolController@showCreateTokenForm');
	Route::post('{dc}/tokens/new', 'SchoolController@storeToken')->name('school.createToken');
	Route::post('{dc}/tokens/{token_id}/revoke', 'SchoolController@revokeToken')->name('school.revokeToken');
});
