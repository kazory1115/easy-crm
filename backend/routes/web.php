<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 測試 API
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
