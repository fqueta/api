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
        // $rd = new RdstationController;
        // dd($rd->token_api);
        $dados = [
                'Nome' => 'Maria josé',
                'Email' => 'maria@example.com',
                'token' => uniqid(),
                'senha' => bcrypt('senha_secreta')
            ];

        // $ret = Qlib::saveEditJson($data);
        // $ret = Qlib::update_tab('clientes',$dados,"WHERE Email='".$dados['Email']."'");
        // $zg = new ZapguruController;

		// $ret = $zg->criar_chat(array('telefonezap'=>'5532984748644','cadastrados'=>true));
        // dd(Qlib::qoption('dominio'));
        // $ret = (new ZapguruController)->post('553291648202','dialog_execute',$comple_url='&dialog_id=679a438a9d7c8affe47e29b5');
        $rdc = new RdstationController;
        $ret = $rdc->get_contact('67a4f69c968ad00014a6773f');
        return $ret;
    }
}
