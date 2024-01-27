<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(App\Http\Controllers\LoginController::class)
	->group(function() {
		Route::post("/login", 'submit');
		Route::post("/login/verify", 'verify');
	});

Route::middleware('auth:sanctum')->group(function() {
	Route::get("/user", function(Request $request) {
		return $request->user();
	});

	Route::controller(App\Http\Controllers\DriverController::class)
		->group(function() {
			Route::get("/driver", 'show');
			Route::post("/driver", 'update');
	});

	Route::controller(App\Http\Controllers\TripController::class)
		->group(function() {
			Route::get("/trip/{trip}", 'show');
			Route::post("/trip", 'store');
			Route::post("/trip/{trip}/accept", 'accept');
			Route::post("/trip/{trip}/start", 'start');
			Route::post("/trip/{trip}/end", 'end');
			Route::post("/trip/{trip}/location", 'location');
		});
});