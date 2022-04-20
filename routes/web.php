<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\SitePreferenceController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\StreamDomainController;
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
    return view('iframe');
});

$router->group(['prefix' => '/admin'], function () use ($router) {
	$router->post('/login', [AdminController::class, 'login']);
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->get('/get-profile', [AdminController::class, 'me']);
		$router->get('/logout', [AdminController::class, 'logout']);
		$router->post('/list', [AdminController::class, 'list']);
		$router->post('/edit', [AdminController::class, 'edit']);
		$router->post('/create', [AdminController::class, 'create']);
		$router->post('/update', [AdminController::class, 'update']);
		$router->post('/update-status', [AdminController::class, 'updateStatus']);
		$router->post('/update-password', [AdminController::class, 'updatePassword']);
		$router->post('/delete', [AdminController::class, 'delete']);
	});
});

$router->group(['namespace' => 'App', 'prefix' => '/stream'], function () use ($router) {
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->post('/get', [StreamController::class, 'get']);
		$router->post('/generate', [StreamController::class, 'generate']);
		$router->post('/list', [StreamController::class, 'list']);
		$router->post('/edit', [StreamController::class, 'edit']);
		$router->post('/create', [StreamController::class, 'create']);
		$router->post('/update', [StreamController::class, 'update']);
		$router->post('/update-status', [StreamController::class, 'updateStatus']);
		$router->post('/delete', [StreamController::class, 'delete']);
	});
});

$router->group(['namespace' => 'App', 'prefix' => '/domain'], function () use ($router) {
	$router->post('/iframe-stream-list', [DomainController::class, 'iframeStreamList']);
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->post('/get', [DomainController::class, 'get']);
		$router->post('/list', [DomainController::class, 'list']);
		$router->post('/monitoring-stream-list', [DomainController::class, 'monitoringStreamList']);
		$router->post('/edit', [DomainController::class, 'edit']);
		$router->post('/create', [DomainController::class, 'create']);
		$router->post('/update', [DomainController::class, 'update']);
		$router->post('/delete', [DomainController::class, 'delete']);
	});
});

$router->group(['namespace' => 'App', 'prefix' => '/stream-domain'], function () use ($router) {
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->post('/get', [StreamDomainController::class, 'get']);
		$router->post('/list', [StreamDomainController::class, 'list']);
		$router->post('/edit', [StreamDomainController::class, 'edit']);
		$router->post('/create', [StreamDomainController::class, 'create']);
		$router->post('/update', [StreamDomainController::class, 'update']);
		$router->post('/update-status', [StreamDomainController::class, 'updateStatus']);
		$router->post('/delete', [StreamDomainController::class, 'delete']);
	});
});

$router->group(['namespace' => 'App', 'prefix' => '/site-preference'], function () use ($router) {
	$router->post('/get', [SitePrefereceController::class, 'get']);
	$router->post('/get-random', [SitePrefereceController::class, 'getRandom']);
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->post('/update', [SitePrefereceController::class, 'update']);
	});
});

$router->group(['namespace' => 'App', 'prefix' => '/activity-log'], function () use ($router) {
	$router->post('/create', [ActivityLogController::class, 'create']);
	$router->group(['middleware' => 'auth:admin'], function () use ($router) {
		$router->post('/get', [ActivityLogController::class, 'get']);
		$router->post('/list', [ActivityLogController::class, 'list']);
		$router->post('/update', [ActivityLogController::class, 'update']);
		$router->post('/delete', [ActivityLogController::class, 'delete']);
	});
});