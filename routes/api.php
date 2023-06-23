<?php

use App\Http\Controllers\ddiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('/ddi',[ddiController::class,'index'])->name('index');
Route::resource('ddi','\App\Http\Controllers\DdisController',['parameters' => [
    'ddi' => 'id'
]]);
Route::resource('cursos','\App\Http\Controllers\CursosController',['parameters' => [
    'cursos' => 'id'
]]);
