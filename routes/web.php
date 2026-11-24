<?php

use App\Http\Controllers\PresentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', PresentationController::class)->name('presentation');
