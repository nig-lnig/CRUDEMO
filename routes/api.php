<?php

use App\Http\Controllers\Demo\CreateDemoController;
use App\Http\Controllers\Demo\DeleteDemoController;
use App\Http\Controllers\Demo\DemoToolsController;
use App\Http\Controllers\Demo\ReadDemoController;
use App\Http\Controllers\Demo\UpdateDemoController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API REST « classique » — le CRUD tel qu'on l'écrit dans un vrai projet
|--------------------------------------------------------------------------
*/
Route::apiResource('products', ProductController::class)
    ->parameters(['products' => 'id'])
    ->middleware('demo.inspect');

/*
|--------------------------------------------------------------------------
| Démonstrations pédagogiques — une route par méthode Eloquent (local uniquement)
|--------------------------------------------------------------------------
*/


Route::prefix('demo')->middleware(['local.only', 'demo.inspect'])->group(function () {

    // CREATE
    Route::post('/create/save', [CreateDemoController::class, 'save']);
    Route::post('/create/create', [CreateDemoController::class, 'create']);
    Route::post('/create/insert', [CreateDemoController::class, 'insert']);
    Route::post('/create/first-or-create', [CreateDemoController::class, 'firstOrCreate']);
    Route::post('/create/update-or-create', [CreateDemoController::class, 'updateOrCreate']);

    // READ
    Route::get('/read/all', [ReadDemoController::class, 'all']);
    Route::get('/read/find/{id}', [ReadDemoController::class, 'find']);
    Route::get('/read/find-or-fail/{id}', [ReadDemoController::class, 'findOrFail']);
    Route::get('/read/filter', [ReadDemoController::class, 'filter']);
    Route::get('/read/paginate', [ReadDemoController::class, 'paginate']);

    // UPDATE
    Route::patch('/update/save/{id}', [UpdateDemoController::class, 'save']);
    Route::patch('/update/direct/{id}', [UpdateDemoController::class, 'update']);
    Route::patch('/update/bulk', [UpdateDemoController::class, 'bulk']);
    Route::patch('/update/increment/{id}', [UpdateDemoController::class, 'increment']);
    Route::patch('/update/decrement/{id}', [UpdateDemoController::class, 'decrement']);

    // DELETE
    Route::delete('/delete/model/{id}', [DeleteDemoController::class, 'delete']);
    Route::delete('/delete/destroy', [DeleteDemoController::class, 'destroy']);
    Route::get('/delete/trashed', [DeleteDemoController::class, 'trashed']);
    Route::patch('/delete/restore/{id}', [DeleteDemoController::class, 'restore']);
    Route::delete('/delete/force/{id}', [DeleteDemoController::class, 'forceDelete']);
});

/*
|--------------------------------------------------------------------------
| Outils de la présentation (local uniquement, non instrumentés)
|--------------------------------------------------------------------------
*/
Route::prefix('demo')->middleware('local.only')->group(function () {
    Route::post('/reset', [DemoToolsController::class, 'reset']);
    Route::get('/inspections/{inspection}', [DemoToolsController::class, 'inspection'])
        ->whereUuid('inspection');

});
