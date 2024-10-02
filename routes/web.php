<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('home'); // Home page
});


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
