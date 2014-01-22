<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'home', 'uses' =>'HomeController@getDashBoard'));

// User Authentication routes.
Route::get('customer/logout', array('as' => 'customer.logout', 'uses' => 'App\Controllers\Customer\AuthController@getLogout'));
Route::get('customer/login', array('as' => 'customer.login', 'uses' => 'App\Controllers\Customer\AuthController@getLogin'));
Route::post('customer/login', array('as' => 'customer.login.post', 'uses' => 'App\Controllers\Customer\AuthController@postLogin'));
Route::get('customer/register', array('as' => 'customer.register', 'uses' => 'App\Controllers\Customer\AuthController@getRegister'));
Route::post('customer/register', array('as' => 'customer.register.post', 'uses' => 'App\Controllers\Customer\AuthController@postRegister'));

//Admin Authentication routes.
Route::get('admin/logout', array('as' => 'admin.logout', 'uses' => 'App\Controllers\Admin\AuthController@getLogout'));
Route::get('admin/login', array('as' => 'admin.login', 'uses' => 'App\Controllers\Admin\AuthController@getLogin'));
Route::post('admin/login', array('as' => 'admin.login.post', 'uses' => 'App\Controllers\Admin\AuthController@postLogin'));

// Admin Route restriction.
Route::group(array('prefix' => 'admin', 'before' => 'auth.admin'), function()
{
        Route::any('/', 'App\Controllers\Admin\ManufacturersController@index');
        Route::resource('manufacturers', 'App\Controllers\Admin\ManufacturersController');
        Route::resource('models', 'App\Controllers\Admin\ModelsController');
        Route::resource('dealers', 'App\Controllers\Admin\DealersController');
        Route::resource('service_centers', 'App\Controllers\Admin\ServiceCentersController');

        # Admin Dashboard
    	Route::controller('/', 'App\Controllers\Admin\ModelsController');
    	Route::controller('/', 'App\Controllers\Admin\ManufacturersController');
});

//Customer route restriction.
Route::group(array('prefix' => 'customer', 'before' => 'auth.customer'), function()
{
	//Customer routes.
    Route::any('/', 'App\Controllers\Customer\ProfilesController@index');
	Route::resource('profiles', 'App\Controllers\Customer\ProfilesController');
	Route::resource('services', 'App\Controllers\Customer\ServicesController');
	Route::resource('vehicles', 'App\Controllers\Customer\VehiclesController');
	Route::resource('bookings', 'App\Controllers\Customer\BookingsController');

    Route::get('customer/bookings/book/{id}', array('as' => 'customer.bookings.book', 'uses' => 'App\Controllers\Customer\BookingsController@getBook'));
    Route::post('customer/bookings/book{id}', array('as' => 'customer.bookings.postbook', 'uses' => 'App\Controllers\Customer\BookingsController@postBook'));
    Route::controller('bookings', 'App\Controllers\Customer\BookingsController');
    //Route::any('/bookings', 'App\Contrsidollers\Customer\BookingsController@getCenter');
});

Route::get('/api/main/models/id/{id}', 'App\Controllers\Api\MainController@getModels');