<?php

namespace App\Http\Controllers;

use App\Qlib\Qlib;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function index(Request $request){
        $ret['exec'] = false;
        // $ret = (new SiteController())->short_code('fundo_proposta',['compl'=>'']);
        $token = $request->get('token');
        $ret = (new MatriculasController)->gerar_orcamento($token);
        // $ret = Qlib::qoption('validade_orcamento');
        // $ret = Qlib::dados_tab('cursos',['id' => 97]);

        dd($ret);
        return $ret;
    }
}
