<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::group(['middleware' => ['auth:api']], function () {
    Route::group(['prefix'=>'auth','as'=>'auth.'], function (){
        Route::post('logout', 'AuthController@logout')->name('logout');
        Route::post('refresh', 'AuthController@refresh')->name('refresh');
    });

    //User
    Route::group(['prefix'=>'user'], function (){
        Route::post('change-password/{user}', 'UserController@changePassword')
            ->name('user.change-password');
        Route::post('change-image/{user}', 'UserController@changeImage')
            ->name('user.change-image');

        //userRole : Pasien
        Route::get('polymaster/{healthAgency}/', 'HealthAgencyController@userShowPolymaster')
            ->name('user.show-polymaster');
        Route::get('health-agency/{polymaster}/', 'PolyclinicController@userShowHealthAgency')
            ->name('user.show-health-agency');
        Route::get('get-waiting-list', 'WaitingListController@getWaitingList')
            ->name('user.get-waiting-list');
        Route::get('show-nearest-waiting-list', 'WaitingListController@showNearestWaitingList')
            ->name('user.show-nearest-waiting-list');
        Route::get('show-schedule/{polymaster}', 'ScheduleController@showSchedule')
            ->name('user.show-schedule');

        Route::post('search', 'HealthAgencyController@searchHealthAgency')
            ->name('user.search-health-agency');
        Route::post('search', 'HealthAgencyController@searchHealthAgency')
            ->name('user.search-health-agency');
        Route::get('get-waiting-list/{schedule}/{date}', 'WaitingListController@getCurrentWaitingListRegist')
            ->name('user.get-waiting-list-by-schedule');
    });

    //userRole : Admin
    Route::group(['prefix'=>'admin'], function (){
        Route::group(['prefix' => 'health-agency', 'as' => 'health-agency.'], function (){
            Route::get('{healthAgency}/polyclinic', 'PolyclinicController@ShowPolyclinicOfHA')
                ->name('show-polyclinic');
            Route::get('waiting-list', 'HealthAgencyController@showWaitingList')
                ->name('show-waiting-list');
        });

        Route::resource('health-agency', 'HealthAgencyController');
        Route::resource('poly-master', 'PolyMasterController');
        Route::resource('schedule', 'ScheduleController');
        Route::resource('polyclinic', 'PolyclinicController');
        Route::resource('waiting-list', 'WaitingListController');
    });

    Route::resource('user', 'UserController');
});

Route::group(['prefix'=>'auth','as'=>'auth.'], function (){
    Route::post('register', 'AuthController@register')->name('register');
    Route::post('login', 'AuthController@login')->name('login');
});
