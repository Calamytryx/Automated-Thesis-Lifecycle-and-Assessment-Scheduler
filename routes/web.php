<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/{any}', function ($any = null) {
    // Build the WordPress URL
    $url = 'http://localhost/coecsathesis/wordpress/' . $any; // Adjust the path as necessary

    // Check if the URL exists
    $response = @file_get_contents($url); // Suppress errors if the file does not exist

    // If the URL does not exist, return a 404 response
    if ($response === false) {
        abort(404);
    }

    // Return the WordPress response
    return response($response)->header('Content-Type', 'text/html');
})->where('any', '.*');