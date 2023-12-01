<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ddiController;
use App\Http\Controllers\MatriculasController;
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

// Route::get('/ddi',[ddiController::class,'index'])->name('index');
Route::resource('ddi','\App\Http\Controllers\DdisController',['parameters' => [
    'ddi' => 'id'
]]);
Route::resource('cursos','\App\Http\Controllers\CursosController',['parameters' => [
    'cursos' => 'id'
]]);
Route::prefix('v1')->group(function(){
    Route::post('/login',[AuthController::class,'login']);
    Route::middleware('auth:sanctum')->get('/user', [AuthController::class,'user']);
    Route::get('/matriculas',[MatriculasController::class,'index'])->middleware('auth:sanctum');
});
