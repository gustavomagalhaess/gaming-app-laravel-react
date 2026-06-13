<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('app'));

// SPA catch-all — must remain last
Route::get('/{any}', fn () => view('app'))->where('any', '.*')->name('spa-catchall');
