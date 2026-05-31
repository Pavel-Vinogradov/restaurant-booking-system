<?php

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::get('/docs/api', static function () {
    return view('scramble::docs');
})->name('scramble.docs');

Route::get('/docs/api.json', action: static fn() => Scramble::generateOpenApiSpec())->name('scramble.docs.json');

Route::get('/', action: static fn() => view('welcome'));
