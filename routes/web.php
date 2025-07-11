<?php

use App\Http\Controllers\admin\ZapsingController;
use App\Http\Controllers\api\OrcamentoController;
use App\Http\Controllers\MatriculasController;
use App\Http\Controllers\PdfGenerateController;
use App\Http\Controllers\PdfSnappy;
use App\Http\Controllers\TesteController;
use App\Http\Controllers\YoutubeController;
use Barryvdh\Snappy\Facades\SnappyPdf;
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
Route::get('/orcamento-pdf/{token}', [PdfGenerateController::class,'orcamento_pdf'])->name('orcamento.pdf');
Route::get('/orcamento/{token}', [MatriculasController::class,'orcamento_html'])->name('orcamento');
Route::get('/youtube', [YoutubeController::class,'envia'])->name('yt.send');
Route::get('/teste', [TesteController::class,'index'])->name('teste');
Route::get('/contratos/{token}/{type}', [MatriculasController::class,'contratos'])->name('contratos');
Route::get('/pdf-com-imagem', [PdfGenerateController::class, 'gerarPdfComImagemDeFundo'])->name('pdf.image');
Route::get('/d/{sec}/{token}', [OrcamentoController ::class,'pagina_orcamentos_site'])->name('docs');
Route::get('/d/{sec}/{token}/{token2}', [OrcamentoController ::class,'pagina_orcamentos_site'])->name('docs2');
Route::get('/orc/{sec}/{token}', [OrcamentoController ::class,'pagina_orcamentos_site'])->name('orc');
Route::get('/orc/{sec}/{token}/{token2}', [OrcamentoController ::class,'pagina_orcamentos_site'])->name('orc2');
// Route::get('/ass/{token}', [ZapsingController::class,'painel_assinaturas'])->name('ass');
// Route::get('/ass/{token}/{tk_periodos}', [ZapsingController::class,'painel_assinaturas'])->name('ass2');
