<?php

use App\Http\Controllers\PdfGenerateController;
use App\Http\Controllers\YoutubeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/orcamento-pdf/{token}', [PdfGenerateController::class,'gera_orcamento'])->name('orcamento.pdf');
Route::get('/youtube', [YoutubeController::class,'envia'])->name('yt.send');
