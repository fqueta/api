<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\ZapsingController;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function index(Request $request){
        $ret['exec'] = false;
        // $ret = (new SiteController())->short_code('fundo_proposta',['compl'=>'']);
        $token = $request->get('token');
        // $ret = (new MatriculasController)->gerar_orcamento($token);
        // $ret = Qlib::qoption('validade_orcamento');
        // $ret = Qlib::dados_tab('cursos',['id' => 97]);
        // $rd = new RdstationController;
        // dd($rd->token_api);

        // $ret = Qlib::saveEditJson($data);
        // $ret = Qlib::update_tab('clientes',$dados,"WHERE Email='".$dados['Email']."'");
        // $zg = new ZapguruController;

		// $ret = $zg->criar_chat(array('telefonezap'=>'5532984748644','cadastrados'=>true));
        // dd(Qlib::qoption('dominio'));
        // $ret = (new ZapguruController)->post('553291648202','dialog_execute',$comple_url='&dialog_id=679a438a9d7c8affe47e29b5');
        // $rdc = new RdstationController;
        // $ret = $rdc->get_contact('67a4f69c968ad00014a6773f');
        // $ret = Qlib::buscaValoresDb_SERVER('SELECT * FROM usuarios_sistemas');
        // $ret = Qlib::dados_tab('cursos',['where' =>'WHERE '.Qlib::compleDelete()." AND id='69'"]);
        // $token_matricula = '66e99d69953c0';
        // $ret = (new MatriculasController)->grava_contrato_statico($token_matricula);
        // $json = '{
        //     "token": "679d1019169b2",
        //     "pagina": "1",
        //     "token_matricula": "679d10356bccd",
        //     "Nome": "João Victtor",
        //     "pais": "Brasil",
        //     "DtNasc2": "1986-01-26",
        //     "Cpf": "123.456.789-09",
        //     "canac": "",
        //     "Ident": "v1555",
        //     "Cep": "36035-720",
        //     "Endereco": "Rua Eduardo Sathler",
        //     "Numero": "15",
        //     "Compl": "",
        //     "Bairro": "Serra D\'Água",
        //     "Cidade": "Juiz de Fora",
        //     "Uf": "MG",
        //     "nacionalidade": "Brasileiro",
        //     "profissao": "Programador",
        //     "sexo": "m",
        //     "config": {
        //         "altura": "175",
        //         "peso": "45"
        //     },
        //     "meta": {
        //         "situacao_cadastro": {
        //             "transferido": "Sim",
        //             "cma_em_dia": "Sim",
        //             "cma_class": "1ª Classe",
        //             "banca": "Sim"
        //         },
        //         "ciente": {
        //             "taxa_alojamento": "s",
        //             "hora_seca": "s",
        //             "headset": "s",
        //             "prazo_conclusao": "s",
        //             "altura_peso": "s",
        //             "gs": "s",
        //             "uniforme": "s"
        //         }
        //     },
        //     "campo_bus": "id",
        //     "campo_id": "id"
        // }';
        // $json2 = '{
        //     "token": "679d1019169b2",
        //     "pagina": "2",
        //     "token_matricula": "679d10356bccd",
        //     "contrato": {
        //         "declaracao": "on",
        //         "aceito_contrato": "on",
        //         "aceito_contrato_combustivel": "on",
        //         "aceito_termo_concordancia": "on",
        //         "aceito_termo_concordancia_escola_voo": "on",
        //         "aceito_termo_antecipacao_combustivel": "on",
        //         "data_aceito_contrato": "2025-02-21 13:23:33",
        //         "id_matricula": "7119",
        //         "ip": "172.70.140.46"
        //     },
        //     "campo_bus": "id",
        //     "campo_id": "id"
        // }';

        // // $config = Qlib::lib_json_array($json);
        // $config = Qlib::lib_json_array($json2);

        // $ret = (new MatriculasController)->assinar_proposta($config);

        //zapsing

        // enviar anexo.
        $body = [
            'name'=>'Termo teste',
            'url_pdf'=>'https://oficina.aeroclubejf.com.br/storage/pdfs/termo_pdf',
        ];
        // $endpoint = 'docs/d460cbeb-aba7-421f-a776-6c34cd60d1ae/upload-extra-doc';
        // $ret = (new ZapsingController)->post([
        //     "endpoint" => $endpoint,
        //     "body" => $body,
        // ]);
        $ret = (new MatriculasController)->send_to_zapSing($token);
        return $ret;
    }
}
