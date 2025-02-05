<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\OrcamentoController;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RdstationController extends Controller
{
    public $url_padrao;
    public $token_api;
    public $version;
    public function __construct(){
        $this->version = 'v1';
        $this->url_padrao = 'https://crm.rdstation.com/api/'.$this->version;
        $this->token_api = Qlib::qoption('token_usuario_rd');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id,$endpoint=null)
    {
        $url = $this->url_padrao.'/'.$endpoint.'?token='.$this->token_api;
        $url = str_replace('{id}',$id,$url);
        $response = Http::accept('application/json')->get($url);
        $ret['exec'] = false;
        if($response){
            $ret['exec'] = true;
            $ret['json'] = $response;
            $ret['url'] = $url;
            $ret['data'] = Qlib::lib_json_array($response);
        }
        return $ret;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    /**
     * para postagem de conteudo na api rd
     * @param string $endpoint
     * @param string $data é o body
     */
    public function post($endpoint=null,$data=[])
    {
        $url = $this->url_padrao.'/'.$endpoint.'?token='.$this->token_api;
        // $url = str_replace('{id}',$id,$url);
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post($url,$data);
        $ret['exec'] = false;
        if($response){
            $ret['exec'] = true;
            $ret['json'] = $response;
            $ret['data'] = Qlib::lib_json_array($response);
        }
        return $ret;
    }
    /**
     * Atualiza o cliente com a requisição do contato do RD station
     * @param array $config Array da consulta da API do Rd
     */
    public function atualiza_cliente($config=[]){

    }
    /**
     * Extrai o id de post webhook
     * @param array $config  essa é a carga de dados de uma webhook
     */
    public function get_id_by_webhook($config=[]){
        $event = isset($config['event_name']) ? $config['event_name'] : false;
        $id = null;
        if(!$event){
            return null;
        }
        if($event=='crm_deal_created'){
            $id = isset($config['document']['id']) ? $config['document']['id'] : null;
        }
        return $id;
    }
    /**
     * Extrai o id de post webhook
     * @param array $config  essa é a carga de dados de uma webhook
     */
    public function get_event_by_webhook($config=[]){
        $event = isset($config['event_name']) ? $config['event_name'] : false;
        return $event;
    }
    /**
     * Extrai o id de post webhook
     * @param array $config  essa é a carga de dados de uma webhook
     */
    public function get_deal_id($config=[]){
        $id = isset($config['document']['id']) ? $config['document']['id'] : null;
        return $id;
    }
    /**
     * Extrai o id de post webhook
     * @param array $config  essa é a carga de dados de uma webhook
     */
    public function get_user_id($config=[]){
        $id = isset($config['document']['user']['id']) ? $config['document']['user']['id'] : null;
        return $id;
    }
    /**
     * Metodo para executar o webhook
     */
    public function webhook(){
        $ret['exec'] = false;
		@header("Content-Type: application/json");
		$json = file_get_contents('php://input');
        $d = [];
        if($json){
            $d = Qlib::lib_json_array($json);
            $event = $this->get_event_by_webhook($d);
            if($event=='crm_deal_created'){
                $id = $this->get_id_by_webhook($d);
                $dados_contato = $this->show($id,'/deals/{id}/contacts');
                $data = isset($dados_contato['data']['contacts']) ? $dados_contato['data']['contacts'] : false;
                if(is_array($data)){
                    $nomeCompleto = isset($data[0]['name']) ? $data[0]['name'] : '';
                    if (str_word_count($nomeCompleto) >= 2) {
                        // echo "Tem nome e sobrenome.";
                        $data[0]['name'] = $nomeCompleto;
                    } else {
                        // echo "Nome incompleto.";
                        $data[0]['name'] = isset($d['document']['name']) ? $d['document']['name'] : '';
                    }
                    $ret = $this->salvar_orcamento($data,$d,true);
                }else{
                    $ret['mens'] = 'Cliente não encontrado na base do RD';
                    $ret['dados_contato_RD'] = $dados_contato;
                }
            }
            $save = Qlib::saveEditJson($d,'webhook_rd.json');
            Log::info('Webhook '.$event.':', $d);
        }
        // $ret['exec'] = false;
        return $ret;
    }
    /**
     * grava cadastro do cliente e faz a ponte para o cadastro do zaguru e a criação do orçamento da integração entre o Rd station e o crm e zapguru
     * @param array $conf_contato array com a requisição contendo os dados do contato do RD station
     * @param array $conf_neg array com a postatem da webhook contendo os dados da neçãociaão do Rd station
     * @return array $ret
     */
    public function salvar_orcamento($conf_contato=[],$conf_neg=[],$chat_inic=true){
        //salvar o cliente
        $config = isset($conf_contato[0])? $conf_contato[0] : [];
        $nome = isset($config['name']) ? $config['name'] : '';
        $email = isset($config['emails'][0]['email']) ? $config['emails'][0]['email'] : '';
        $telefonezap = isset($config['phones'][0]['phone']) ? $config['phones'][0]['phone'] : '';
        $telefonezap = str_replace('+', '', $telefonezap);
        $telefonezap = str_replace(' ', '', $telefonezap);
        $telefonezap = str_replace('(', '', $telefonezap);
        $telefonezap = str_replace(')', '', $telefonezap);
        $telefonezap = str_replace('-', '', $telefonezap);
        // return $config;
        // $data = [
        //     'Nome' => $nome,
        //     'Email' => $email,
        //     'telefonezap' => $telefonezap,
        //     'rdstation' => $config['id'],
        //     'rd_ultimo_negocio' => Qlib::lib_array_json($conf_neg),
        //     'token' => uniqid(),
        //     'EscolhaDoc' => 'CPF',
        // ];
        $data = [
            'nome' => $nome,
            'email' => $email,
            'celular' => $telefonezap,
            'rdstation' => $config['id'],
            'rd_ultimo_negocio' => Qlib::lib_array_json($conf_neg),
            'token' => uniqid(),
            'tag_origem' => 'rdstation',
            'excluido' => 'n',
            'deletado' => 'n',
            'atualizado' => Qlib::dataLocalDb(),
            // 'EscolhaDoc' => 'CPF',
        ];
        $ret['exec'] = false;
        // $sc = (new ClientesController)->add_update($data);
        $sc = (new ClientesController)->add_lead_update($data);
        // return $sc;
        $ret['cad_cliente'] = $sc;
        $id_cliente = isset($sc['idCad']) ? $sc['idCad'] : null;
        //Criar chat zapguru
        $zg = new ZapguruController;
        if(isset($sc['exec']) && $telefonezap){
            $ret['exec'] = true;
            // return $ret;
            if($chat_inic){
                //quanto adicionar o chatguru tem que retornar uma webhook do zapguru
                $ret['criar_chat'] = $zg->criar_chat(array('telefonezap'=>$telefonezap,'cadastrados'=>true,'tab'=>'capta_lead'));
            }
        }

        //criar orçamento...
        // if($id_cliente){
        //     $config_orc = [
        //         'id_cliente'=>$id_cliente,
        //     ];
        //     $ret = (new OrcamentoController)->add_update($config_orc);
        // }
        return $ret;
    }
    /**
     * Metodo para Criar uma anotação apartir de um cadastro de cliente
     * @param string $id_cliente
     * @param string $text texto da anotação
     */

    public function anota_por_cliente($id_cliente,$text,$tab='clientes'){
        $dc = Qlib::dados_tab($tab,['where'=>"WHERE id='$id_cliente'"]);
        $ret['exec'] = false;
        $rd = isset($dc[0]['rd_ultimo_negocio']) ? $dc[0]['rd_ultimo_negocio'] : false;
        if(!is_array($rd)){
            $rd = isset($dc['rd_ultimo_negocio']) ? $dc['rd_ultimo_negocio'] : false;
        }
        if(is_array($rd)){
            // return $this->get_deal_id($rd);
            $ret = $this->criar_anotacao([
                'text' => $text,
                'deal_id' => $this->get_deal_id($rd),
                'user_id' => $this->get_user_id($rd),
            ]);
        }else{
            $ret['mens'] = 'Negociação não encontrada na base de dados';
        }
        return $ret;
    }
    /**
     * Criar uma anotação
     * @param string $id_cliente
     */
    public function criar_anotacao($config=[]){
        $user_id = isset($config['user_id']) ? $config['user_id'] : null;
        $deal_id = isset($config['deal_id']) ? $config['deal_id'] : null;
        $text = isset($config['text']) ? $config['text'] : null;
        $endpoint = 'activities';
        $ret['exec'] = false;
        if($user_id && $deal_id && $text){
            $ret = $this->post($endpoint,[
                'activity'=>[
                    'user_id' => $user_id,
                    'deal_id' => $deal_id,
                    'text' => $text,
                ]
            ]);
        }
        return $ret;
    }
}
