<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfGenerateController extends Controller
{
    public function gera_orcamento($token=false){
        if($token){
            $d = Matricula::where('token','=',$token)
            ->get();
            if($d->count() != 0){
                $d = $d->toArray();
                $orca = new MatriculasController;
                //verifica se está assinado
                $config = $orca->get_matricula_assinado($token);
                if(@$config['exec'] && @$config['data']){
                    $ret = @$config;
                }else{
                    $ret['save'] = $orca->salva_orcamento_assinado($token,$d[0]);
                    $config = $orca->get_matricula_assinado($token);
                }
                // dd($config);
                $pdf = Pdf::loadView('pdf.orcamento',$config);
                // $pdf = Pdf::view('pdf.orcamento');
                $path = storage_path('/orcamentos/');
                $filename = 'orcamento_' . $token.'.pdf';
                $arquivo = $filename;
                return $pdf->stream($arquivo);
                // return view('pdf.orcamento');
            }
        }
    }
}
