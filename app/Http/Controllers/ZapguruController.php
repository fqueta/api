<?php

namespace App\Http\Controllers;

use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ZapguruController extends Controller
{

    public $url;

	function __construct(){
        global $tab15;
		$this->credenciais();
        $tab15 = 'clientes';
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
     * $requet = ['telefonezap'=>'',]
     */
    public function store(Request $request)
    {
        $d = $request->all();
        return $this->criar_chat($d);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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


	function credenciais(){

		$this->url = 'https://s4.chatguru.app/api/v1?key=FQXSYNB8GPSPALZ3MIC5O618HDP0OVUBRFCZ2LAZ4XCLVA44ZA8FPOJM8UG08IJ9&account_id=5f36da757e786f40069aa881&';

	}

	public function phone_id(){
		$ret = '628d294cc5ef6cb21b445e47';
		return $ret;
	}

	function webhook($config=false){
		$ret = false;
		$json = file_get_contents('php://input');

		//$json = '{"campanha_id": "", "campanha_nome": "", "origem": "disparo_crm", "email": "553291648202@c.us", "nome": "Fernando Queta", "tags": ["Regi\u00e3o Sudeste"], "texto_mensagem": "", "campos_personalizados": {}, "bot_context": {}, "responsavel_nome": "Marcony", "responsavel_email": "marcony@aeroclubejf.com.br", "link_chat": "https://s4.chatguru.app/chats#5fbbc17d99409b35ace1b884", "celular": "553291648202", "phone_id": "5fb9305b5e3b368d9f99020c", "chat_id": "5fbbc17d99409b35ace1b884", "chat_created": "2020-11-23 14:04:45.593000", "datetime_post": "2021-03-20 14:20:52.352941"}';



		$arr_json = Qlib::lib_json_array($json);

        //lib_print($arr_json);

		if(isset($arr_json['origem'])){

			if($arr_json['origem']=='envia_campos' || $arr_json['origem']=='disparo_crm'){

				$ret['atualizaCliente'] = $this->atualizaCliente($json);//Atualiza o cadastro do clientes com json e retorma campos

				$ret['json'] = $arr_json;

				$arquivo = fopen(dirname(__FILE__).'/teste_webhook.txt','a');

				fwrite($arquivo, $json.',');

				//Fechamos o arquivo após escrever nele

				fclose($arquivo);

			}elseif($arr_json['origem']=='respondeu_nome' || $arr_json['origem']=='respondeu_email'  || $arr_json['origem']=='add_lead'){

				$ret['atendimento'] = $this->salvarCaptaLead($arr_json);

				$arquivo = fopen(dirname(__FILE__).'/atendimento.txt','a');

				fwrite($arquivo, $json.',');

				//Fechamos o arquivo após escrever nele

				fclose($arquivo);

			}else{

                $ret['req_json'] = $arr_json;

            }

		}



		/*$arquivo = fopen(dirname(__FILE__).'/atendimento.txt','a');

				fwrite($arquivo, $json.',');

				//Fechamos o arquivo após escrever nele

				fclose($arquivo);*/

				lib_print($ret);

		return $ret;

	}

	public function salvarCaptaLead($config=false){

		//Salvar o cliente recebido da webhook

		$ret = false;

		if(isset($config['responsavel_email']) && !empty($config['responsavel_email'])){

			$dadosCon = buscaValoresDb_SERVER("SELECT * FROM usuarios_sistemas WHERE trim(email)='".trim($config['responsavel_email'])."' AND ".compleDelete());

		}else{

			$dadosCon = false;

		}
		if(isset($config['origem']) && isset($config['celular']) && isset($config['texto_mensagem']) && !empty($config['texto_mensagem']) &&($config['origem']=='respondeu_nome' || $config['origem']=='respondeu_email')){

			$arr_or = explode('_',$config['origem']);

			if(!isset($arr_or[1])){

				return $ret;

			}

			$dadosForm = [

				'celular'=>$config['celular'],

				'token'=>uniqid(),

				'zapguru'=>lib_array_json($config),

				$arr_or[1]=>$config['texto_mensagem'],

				'tag_origem'=>'zapguru',

				'conf'=>'s',

			];

			if(isset($config['nome']) && !empty($config['nome'])){

				$dadosForm['nome'] = $config['nome'];

			}

			if(isset($dadosCon[0]['id']) && $dadosCon[0]['id']>0){

				$dadosForm['seguido_por'] = $dadosCon[0]['id'];

			}

			$comple = " AND ".compleDelete();

			$cond_valid = "WHERE celular = '".$config['celular']."' $comple";

			$type_alt = 1;

			$tabUser = $GLOBALS['tab88'];

			$config2 = array(

						'tab'=>$tabUser,

						'valida'=>true,

						'condicao_validar'=>$cond_valid,

						'sqlAux'=>false,

						'ac'=>'cad',

						'type_alt'=>$type_alt,

						'dadosForm' => $dadosForm

			);

			//if(isAdmin(1)){

				 //lib_print($config2);exit;

			//}

			$ret = lib_salvarFormulario($config2);//Declado em Lib/Qlibrary.php

			lib_print($ret);exit;

			$ret = json_decode($ret,true);

		}

		if(isset($config['origem']) && isset($config['celular']) &&($config['origem']=='respondeu_nome' || $config['origem']=='add_lead')){

			$arr_or = explode('_',$config['origem']);

			if(!isset($arr_or[1])){

				return $ret;

			}

			$dadosForm = [

				'celular'=>$config['celular'],

				'token'=>uniqid(),

				'zapguru'=>lib_array_json($config),

				$arr_or[1]=>@$config['texto_mensagem'],

				'tag_origem'=>'zapguru',

				'tag'=>@$config['tags'],

				'conf'=>'s',

			];

			if(isset($config['nome']) && !empty($config['nome'])){

				$dadosForm['nome'] = $config['nome'];

			}

			if(isset($dadosCon[0]['id']) && $dadosCon[0]['id']>0){

				$dadosForm['seguido_por'] = $dadosCon[0]['id'];

			}

			if(isset($config['campos_personalizados']['Nome']) && !empty($config['campos_personalizados']['Nome'])){

				$dadosForm['nome'] = $config['campos_personalizados']['Nome'];

			}

			if(isset($config['campos_personalizados']['Email']) && !empty($config['campos_personalizados']['Email'])){

				$dadosForm['email'] = $config['campos_personalizados']['Email'];

			}

			$comple = " AND ".compleDelete();

			$cond_valid = "WHERE celular = '".$config['celular']."' $comple";

			$type_alt = 1;

			$tabUser = $GLOBALS['tab88'];

			$config2 = array(

						'tab'=>$tabUser,

						'valida'=>true,

						'condicao_validar'=>$cond_valid,

						'sqlAux'=>false,

						'ac'=>'cad',

						'type_alt'=>$type_alt,

						'dadosForm' => $dadosForm

			);

			//return $config2;

			//if(isAdmin(1)){

				//  lib_print($config2);exit;

			//}
			$ret = lib_salvarFormulario($config2);//Declado em Lib/Qlibrary.php
			$telefonezap = $config2['dadosForm']['celular'];
			$verifica_cadCliente = dados_tab($GLOBALS['tab15'],'*',"WHERE telefonezap='".$telefonezap."'");
			//lib_print($verifica_cadCliente);
			if(@$verifica_cadCliente[0]['telefonezap']){
				$ret['verifica_cadCliente'] = $verifica_cadCliente;
				$ret['salvarCadCli'] = clientes::convert_LeadCliente(['id'=>$telefonezap,'campo_bus'=>'celular','type'=>'lc','cond_valid'=>"WHERE telefonezap='".$verifica_cadCliente[0]['telefonezap']."'"]);
                                lib_print($ret);
			}
			$ret = lib_array_json($ret);


		}

		return $ret;

	}
    /**
     * Metodo para criar um chat zapguru
     * @param array estrutura do array $config=['telefonezap'=>'553299999999','dialog_id'=>'opcional'];
     */
	public function criar_chat($config=false){

		//Exemplo de uso

		/*

		$zg = new ZapguruController;

		$ret = $zg->criar_chat(array('telefonezap'=>'5532984741602'));

		*/

		$ret['exec'] = false;
        $cel = isset($config['telefonezap']) ? $config['telefonezap'] : false;
        $cadastrado = isset($config['cadastrados']) ? $config['cadastrados'] : false; // permite criar chat de clientes cadastrados ou não
        $text 		 	= isset($config['text'])?$config['text'] 	:'Olá *{nome}* como podemos ajudá-lo';
        if(!$cel){
            $cel = isset($config['Celular']) ? $config['Celular'] : false;
        }
		if(isset($config['telefonezap'])){
			$comSc = " AND telefonezap='".$cel."'";
		}else{
			$comSc = " AND Celular='".$cel."'";
		}
       if($comSc){
            //dados_tab('lcf_planos',['comple_sql'=>"WHERE token_matricula='".$config['token_matricula']."' $compleSql"])
			$dadosCli = Qlib::dados_tab($GLOBALS['tab15'],['campos'=>'*','where'=>"WHERE ".Qlib::compleDelete()."$comSc"]);

			if(!$dadosCli && $cadastrado){

				$ret['mens'] = Qlib::formatMensagemInfo('Cliente com telefone '.$cel.' não encontrado!','danger');

				return $ret;

			}
            $id_cliente = isset($dadosCli[0]['id']) ? $dadosCli[0]['id'] : false;
            // dump($dadosCli);
            if($cadastrado){

                $pais = !empty($dadosCli[0]['pais'])?$dadosCli[0]['pais']:'Brasil';

                $codi_pais = false;

                if($pais=='Brasil' && !isset($config['telefonezap'])){

                    $codi_pais = '55';

                }

                $ret['dadosCli'] = $dadosCli;

                $Celular 	 	= str_replace('(','',$dadosCli[0]['Celular']);

                $Celular 		= str_replace(')','',$Celular);

                $Celular 		= str_replace('-','',$Celular);

                $nome 		 	= isset($dadosCli[0]['Nome'])?$dadosCli[0]['Nome'] 	:false;

                $sobrenome 		= isset($dadosCli[0]['sobrenome']) ? $dadosCli[0]['sobrenome'] 	:false;

                $name  = urlencode($nome.' '.$sobrenome);

                if(!empty($dadosCli[0]['telefonezap'])){

                    $chat_number 	= $dadosCli[0]['telefonezap'];

                }else{

                    $chat_number 	= $codi_pais.$Celular;
                }

			}else{
                $name = 'Senhor(a)';
                $chat_number 	= $cel;
            }
            $nome_=$nome;
            $nom = explode('+',$nome_);
            if(isset($nom[0]) && !empty($nom[0])){
                $nome_=$nome;
            }
            $text  = str_replace('{nome}',$nome_,$text);
            $text  = urlencode($text);

			$phone_id 		= isset($config['phone_id'])?$config['phone_id']:$this->phone_id();
			//Executar um dialogo opcional
			$dialog_id 		= isset($config['dialog_id'])?$config['dialog_id']:'';

			$action 		= 'chat_add';

			$ret['config'] = $config;
			$url = $this->url.'action='.$action.'&phone_id='.$phone_id.'&name='.$name.'&text='.$text.'&chat_number='.$chat_number;
			if($dialog_id){
				$url .= '&dialog_id='.$dialog_id.'';
			}

			$ret['url'] = $url;
            $response = Http::accept('application/json')->post($url);

			$ret['response'] = Qlib::lib_json_array($response,true);

			if(isset($ret['response']['code']) && $ret['response']['code']==201){
				$ret['exec'] = true;
                //gravar o status no usuario encotrado no CRM do Aero
                $ret['id_cliente'] = $id_cliente;
                if($cadastrado && $id_cliente){
                    $ret['salvar'] = DB::table($GLOBALS['tab15'])->where('id',$id_cliente)->update(['zapguru' => $response]);
                }
			}

			if(isset($ret['response']['description'])&&isset($ret['response']['result'])){

				$css = 'danger';

				if($ret['response']['result']=='success'){

					$css = $ret['response']['result'];

				}

				$ret['mens'] = Qlib::formatMensagem0($ret['response']['description'],$css);

			}

		}

		return $ret;

	}


	function enviar_mensagem($config=false){

		//Exemplo de uso

		/*

		$zg = new ZapController;

		$ret = $zg->enviar_mensagem([
            'celular_completo'=>'553291648202',
            'nome'=>'Queta',
            'text'=>'Olá *{nome}* como podemos ajudá-lo teste ',
            'dialog_id'=>'',
        ]);

		lib_print($ret);

		*/

		$ret['exec'] = false;

		if(isset($config['celular_completo'])){

			/*

			$pais = !empty($dadosCli[0]['pais'])?$dadosCli[0]['pais']:'Brasil';

			$codi_pais = false;

			if($pais=='Brasil'){

				$codi_pais = '55';

			}

			$ret['dadosCli'] = $dadosCli;

			*/

			$Celular 	 	= str_replace('(','',$config['celular_completo']);

			$Celular 		= str_replace('+','',$Celular);

			$Celular 		= str_replace(')','',$Celular);

			$Celular 		= str_replace('-','',$Celular);

			$nome 		 	= isset($config['nome'])?$config['nome'] 	:false; //caso o chat não exista irá criar um com este nome

			$sobrenome 		= isset($config['sobrenome'])?$config['sobrenome'] 	:false;

			$name  = urlencode($nome.' '.$sobrenome);

			$text 		 	= isset($config['text'])?$config['text'] 	:'Olá *'.$nome.'* como podemos ajudá-lo';

			$text  = str_replace('{nome}',$nome,$text);

			$text  = urlencode($text);

			$chat_number 	= $Celular;

			$phone_id 		= isset($config['phone_id'])?$config['phone_id']:'628d294cc5ef6cb21b445e47';

			$dialog_id 		= isset($config['dialog_id'])?$config['dialog_id'] 	:false;

			$action 		= 'message_send';

			$ret['config'] = $config;

			$url = $this->url.'action='.$action.'&phone_id='.$phone_id.'&name='.$name.'&text='.$text.'&chat_number='.$chat_number.'&dialog_id='.$dialog_id.'';

			$curl 		 	= curl_init();


			curl_setopt_array($curl, array(

			  CURLOPT_URL => $url,

			  CURLOPT_RETURNTRANSFER => true,

			  CURLOPT_ENCODING => '',

			  CURLOPT_MAXREDIRS => 10,

			  CURLOPT_TIMEOUT => 0,

			  CURLOPT_FOLLOWLOCATION => true,

			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			  CURLOPT_CUSTOMREQUEST => 'POST',

			));



			$response = curl_exec($curl);



			curl_close($curl);

			$ret['response'] = lib_json_array($response,true);
			$ret['url'] = $url;
			/*if(isset($ret['response']['code']) && $ret['response']['code'] != 201){

				$celular_completo = '55'.str_replace('(32)9','(32)',$config['celular_completo']);

				$config['celular_completo'] = $celular_completo;

				$ret['response'] = $this->enviar_mensagem($config);

			}else*/if(isset($ret['response']['code'])&&$ret['response']['code']==201){

				$ret['exec'] = true;

			}

			if(isset($ret['response']['description'])&&isset($ret['response']['result'])){

				$css = 'danger';

				if($ret['response']['result']=='success'){

					$css = $ret['response']['result'];

				}

				$ret['mens'] = formatMensagem($ret['response']['description'],$css);

			}

		}

		return $ret;

	}
	/**
	 * Metodo para o envio de mensagem para o whatsapp do cliente com a api do zapguru usando o token de um orçamento do cliente
	 * @param string $tk = token da matricula, string $text = a Mensagem a ser enviada
	 * @return array $ret contendo callback da api do zapguru.
	 * @uso $ret = (new zapguru)->envia_zap_orcamento($tk,$text);
	 */
	public function envia_zap_orcamento($tk,$text,$periodo=false){
        // $zg = new zapguru;
        $dm = cursos::dadosMatricula($tk);
        $ret['exec'] = false;
		$ret['dm'] = $dm;
		// lib_print( $celular_completo);
		// dd( $dm);
		$link_pagina = '';
		if($periodo){
			$d_periodo = (new Orcamentos)->get_periodo_array($tk,'periodo',$periodo);
			$token_periodo = isset($d_periodo['token']) ?$d_periodo['token']:'';
			$link_pagina = cursos::link_assinatura_periodo($tk,$token_periodo);
		}
		//salvar o mensagem atual no banco para futuro uso gravo no banco config com o campo: mensagem_zap_periodo
		$ret['s_mzap'] = update_option('mensagem_zap_periodo',$text);
		$text = str_replace('{periodo}',$periodo,$text);
		$text = str_replace('{link_pagina}',$link_pagina,$text);
		if($dm && $text){
			$dm = $dm[0];
			$celular_completo = isset($dm['telefonezap']) ? $dm['telefonezap'] : false;
			$ret = $this->enviar_mensagem([
				'celular_completo'=>$celular_completo ? $celular_completo : false,
				'nome'=>'Queta',
				'text'=>$text ? $text : false,
				'dialog_id'=>'',
			]);
		}
        return $ret;
    }
	public function dialog_execute($config=false){

		//Exemplo de uso

		/*

		$zg = new zapguru;

		$ret = (new zapguru)->dialog_execute(['telefonezap'=>'5532984741608','dialog_id'=>'']);

		*/

		$ret['exec'] = false;

		$compleSql = isset($config['compleSql']) ? $config['compleSql'] : false;


		if(isset($config['telefonezap'])){
			$compleSql .= " AND telefonezap='".$config['telefonezap']."'";
		}elseif(isset($config['id'])){
			$compleSql .= " AND id = '".$config['id']."'";
		}else{
			$compleSql = " AND Celular='".$config['celular_completo']."'";
		}
		if($compleSql){

			$dadosCli = dados_tab($GLOBALS['tab15'],'*',"WHERE ".compleDelete()."$compleSql");

			if(!$dadosCli){

				$ret['mens'] = formatMensagem('Cliente não encontrado!','danger');

				return $ret;

			}

			$pais = !empty($dadosCli[0]['pais'])?$dadosCli[0]['pais']:'Brasil';

			$codi_pais = false;

			if($pais=='Brasil' && !isset($config['telefonezap'])){

				$codi_pais = '55';

			}

			$ret['dadosCli'] = $dadosCli;
			if($dadosCli[0]['Celular']){

				$Celular 	 	= str_replace('(','',$dadosCli[0]['Celular']);

				$Celular 		= str_replace(')','',$Celular);

				$Celular 		= str_replace('-','',$Celular);
			}else{
				$celular = '';
			}

			$nome 		 	= isset($dadosCli[0]['Nome'])?$dadosCli[0]['Nome'] 	:false;

			$sobrenome 		= isset($dadosCli[0]['sobrenome']) ? $dadosCli[0]['sobrenome'] 	:false;
			$name  = urlencode($nome.' '.$sobrenome);

			$text 		 	= isset($config['text'])?$config['text'] 	:'Olá *'.$nome.'* como podemos ajudá-lo';

			$text  = str_replace('{nome}',$nome,$text);

			$text  = urlencode($text);

			$chat_number 	= trim($codi_pais).trim($Celular);

			$chat_number = str_replace(' ','',$chat_number);

			$phone_id 		= isset($config['phone_id'])?$config['phone_id']:$this->phone_id();

			$dialog_id 		= isset($config['dialog_id'])?$config['dialog_id']:false;
			if(!$dialog_id){
				$dialog_id = '626ff0553408059edfb29110';
			}
			$action 		= 'dialog_execute';

			$ret['config'] = $config;
			if(empty($dadosCli[0]['telefonezap'])){
				if(!empty($dadosCli[0]['zapguru']) && !empty($dadosCli[0]['zapguru'])){
					$arr_zapguru = lib_json_array($dadosCli[0]['zapguru']);
					$chat_number = isset($arr_zapguru['celular'])?$arr_zapguru['celular'] : 0;
				}
			}else{
				$chat_number = $dadosCli[0]['telefonezap'];

			}
			if(!$chat_number){
				$ret['exec'] = false;
				$ret['mens'] = formatMensagem('Celular chat invário','danger',10000);
			}

			$url = $this->url.'action='.$action.'&phone_id='.$phone_id.'&name='.$name.'&chat_number='.$chat_number.'&dialog_id='.$dialog_id.'';

			// dd($url);
			if(isAdmin(1)){

				$ret['url'] = $url;

			}

			$curl 		 	= curl_init();



			curl_setopt_array($curl, array(

			  CURLOPT_URL => $url,

			  CURLOPT_RETURNTRANSFER => true,

			  CURLOPT_ENCODING => '',

			  CURLOPT_MAXREDIRS => 10,

			  CURLOPT_TIMEOUT => 0,

			  CURLOPT_FOLLOWLOCATION => true,

			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

			  CURLOPT_CUSTOMREQUEST => 'POST',

			));



			$response = curl_exec($curl);



			curl_close($curl);

			$ret['response'] = lib_json_array($response,true);

			if(isset($ret['response']['code'])&&$ret['response']['code']==200){

				$css = 'success';

				$ret['exec'] = true;

				$ret['mens'] = formatMensagem($ret['response']['dialog_execution_return'],$css);
				if(empty($dadosCli[0]['telefonezap']) && isset($config['celular_completo'])){
					$dadosCli = dados_tab($GLOBALS['tab15'],'zapguru',"WHERE Celular='".$config['celular_completo']."' $compleSql AND ".compleDelete());
				}else{
					if(isset($dadosCli[0]['id'])){
						$dadosCli = dados_tab($GLOBALS['tab15'],'zapguru',"WHERE id='".$dadosCli[0]['id']."' $compleSql AND ".compleDelete());
					}else{
						$dadosCli = dados_tab($GLOBALS['tab15'],'zapguru',"WHERE telefonezap='".$config['telefonezap']."' $compleSql AND ".compleDelete());
					}
				}

				if($dadosCli){

					$ret['celular_completo'] = isset($config['celular_completo']) ? $config['celular_completo']:false;

					$ret['zapguru'] = lib_json_array($dadosCli[0]['zapguru']);

				}

			}

			if(isset($ret['response']['description'])&&isset($ret['response']['result'])){

				$css = 'danger';

				if($ret['response']['result']=='success'){

					$css = $ret['response']['result'];

				}

				$ret['mens'] = formatMensagem($ret['response']['description'],$css);

			}

		}

		return $ret;

	}

	function verificaLinkGuru($config=false){

		//Exemplo de uso

		/*

		$zg = new zapguru;

		$ret = $zg->verificaLinkGuru(array('celular_completo'=>'(32)98474-8644'));

		*/

		$ret = false;

		$ret['zapguru'] = false;

		if(isset($config['celular_completo'])){

			$compleSql = isset($config['compleSql']) ? $config['compleSql'] : false;

			if(isset($config['id'])){

				$compleSql .= " AND id = '".$config['id']."'";

			}



			$dadosCli = dados_tab($GLOBALS['tab15'],'*',"WHERE Celular='".$config['celular_completo']."' $compleSql AND ".compleDelete(),true);

			if($dadosCli){

				$ret['celular_completo'] = $config['celular_completo'];

				$ret['zapguru'] = lib_json_array($dadosCli[0]['zapguru']);

			}else{

				$ret['mens'] = formatMensagem('Cliente não encontrado','danger');

			}

		}else{

			$ret['mens'] = formatMensagem('Telefone não informado','danger');

		}

		return $ret;

	}

	function atualizarCampos($config=false){

		//Exemplo de uso

		/*

		$zg = new zapguru;

		$ret = $zg->atualizarCampos(array('celular_completo'=>'(32)98474-8644'));

		*/

		$ret['exec'] = false;
		$link_orcamento = isset($config['link_orcamento']) ? $config['link_orcamento'] : false;
		if(!$link_orcamento && isset($config['tk_matricula']) && ($tk_matricula=$config['tk_matricula'])){
			$dm = cursos::dadosMatricula($tk_matricula);
			// dd(isset($dm[0]['Celular']));
			if(isset($dm[0]['link_orcamento'])){
				$link_orcamento = $dm[0]['link_orcamento'];
			}
		}
		if(isset($config['celular_completo']) || isset($config['telefonezap'])){
			if(empty($config['celular_completo']) && isset($config['telefonezap'])){
				$csqlCli = " AND telefonezap='".$config['telefonezap']."'";
			}else{
				$csqlCli = " AND Celular='".$config['celular_completo']."'";
			}

			$dadosCli = dados_tab($GLOBALS['tab15'],'*',"WHERE ".compleDelete()."$csqlCli",false);

			if(!$dadosCli){

				$ret['mens'] = formatMensagem('Cliente não encontrado!','danger');

				return $ret;

			}


			if(isJson($dadosCli[0]['zapguru'])){

				$arr_zap = lib_json_array($dadosCli[0]['zapguru']);
				// dd($arr_zap);

				if(isset($arr_zap['celular'])){

					$chat_number = $arr_zap['celular'];

				}

			}else{



				$pais = !empty($dadosCli[0]['pais'])?$dadosCli[0]['pais']:'Brasil';

				$codi_pais = false;

				if($pais=='Brasil'){

					$codi_pais = '55';

				}

				//$ret['dadosCli'] = $dadosCli;

				$Celular 	 	= str_replace('(','',$dadosCli[0]['Celular']);

				$Celular 		= str_replace(')','',$Celular);

				$Celular 		= str_replace('-','',$Celular);

				$chat_number 	= $codi_pais.$Celular;

			}

			$nome 		 	= isset($dadosCli[0]['Nome'])?$dadosCli[0]['Nome'] 	:false;

			$sobrenome 		= isset($dadosCli[0]['sobrenome']) ? $dadosCli[0]['sobrenome'] 	:false;

			$name  = urlencode($nome.' '.$sobrenome);

			$cpf 		 	= $dadosCli[0]['Cpf'];

			$email 		 	= rtrim($dadosCli[0]['Email']);

			$telefone	 	= $dadosCli[0]['Celular'] ? $dadosCli[0]['Celular'] : @$dadosCli[0]['telefonezap'];

			$obs	 		= $dadosCli[0]['Obs'];

			$dialog_id 		= '6048d4bd171c171fcaa81c0d';

			$action 		= 'chat_update_custom_fields';

			$ret['config'] = $config;

			$curl 		 	= curl_init();
			$compleUrl = false;
			$key = 'FQXSYNB8GPSPALZ3MIC5O618HDP0OVUBRFCZ2LAZ4XCLVA44ZA8FPOJM8UG08IJ9';
			$phone_id = '628d294cc5ef6cb21b445e47';
			$account_id = '5f36da757e786f40069aa881';
			if($link_orcamento){
				$compleUrl = '&field__Link_da_proposta='.$link_orcamento;
			}
			$urlReq = 'https://s4.chatguru.app/api/v1?key='.$key.'&account_id='.$account_id.'&phone_id='.$phone_id.'&action='.$action.'&field__Nome='.$name.'&field__Telefone='.$telefone.'&field__ID_do_cliente='.$dadosCli[0]['id'].'&field__Email='.$email.$compleUrl.'&chat_number='.$chat_number;
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $urlReq,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
			));

			$response = curl_exec($curl);

			curl_close($curl);
			$ret['urlReq'] = $urlReq;
			$ret['response_json'] = $response;
			$ret['response'] = lib_json_array($response,true);

			if(isset($ret['response']['code'])&&$ret['response']['code']==200){

				$ret['exec'] = true;

			}

			if(isset($ret['response']['description'])&&isset($ret['response']['result'])){

				$css = 'danger';

				if($ret['response']['result']=='success'){

					$css = $ret['response']['result'];

				}

				$ret['mens'] = formatMensagem($ret['response']['description'],$css);

			}

		}

		return $ret;

	}

	function atualizaCamposLinkOrcamento($tk_matricula){
		//Atualiza campo personalizado link orçamento
		$ret['exec'] = false;
		/*
		$ret = (new zapguru)->atualizaCamposLinkOrcamento($tk_matricula='');
		*/
		if($tk_matricula){
			$dm = cursos::dadosMatricula($tk_matricula);
			// dd(isset($dm[0]['Celular']));
			if(isset($dm[0]['link_orcamento'])){
				$ret = $this->atualizarCampos([
					'celular_completo'=>$dm[0]['Celular'],
					'telefonezap'=>$dm[0]['telefonezap'],
					'link_orcamento'=>$dm[0]['link_orcamento'],
				]);
			}
			if(isAdmin(1))
			$ret['dm'] = $dm;
		}
		return $ret;
	}
	function maskTelefone($telefone=false){

		$ret = false;

		//o telefone é no formato do zapguru

		if($telefone){

			$leng = strlen($telefone);

			$codi_pais = 0;

			if($leng==13){

				$codi_pais = substr($telefone,0,-11);

				$telefone = substr($telefone,2);

			}elseif($leng==12){

				$codi_pais = substr($telefone,0,-10);

				$telefone = substr($telefone,2);

			}

			if($codi_pais=='55'){

				$leng2 = strlen($telefone);

				$novoTel = false;

				if($leng2==11){

					$arr_tel = str_split($telefone);

					if(is_array($arr_tel)){

						foreach($arr_tel As $k=>$v){

							if($k==0){

								$novoTel .= '('.$v;

							}elseif($k==2){

								$novoTel .= ')'.$v;

							}elseif($k==7){

								$novoTel .= '-'.$v;

							}else{

								$novoTel .= $v;

							}



						}

					}

				}elseif($leng2==10){

					$arr_tel = str_split($telefone);

					if(is_array($arr_tel)){

						foreach($arr_tel As $k=>$v){

							if($k==0){

								$novoTel .= '('.$v;

							}elseif($k==2){

								$novoTel .= ')9'.$v;

							}elseif($k==6){

								$novoTel .= '-'.$v;

							}else{

								$novoTel .= $v;

							}



						}

					}

				}

				$ret = $novoTel;

			}

		}

		return $ret;

	}

	function atualizaCliente($config=false){

		//Exemplo de uso

		/*

		$config = '{

		  "campanha_id": "",

		  "campanha_nome": "chat iniciado",

		  "origem": "chat_plataforma",

		  "email": "5532984748644@c.us",

		  "nome": "Fernando Teste",

		  "tags": [],

		  "texto_mensagem": "",

		  "campos_personalizados": {},

		  "bot_context": {},

		  "responsavel_nome": "Fernando",

		  "responsavel_email": "fernando@maisaqui.com.br",

		  "link_chat": "https://s4.chatguru.app/chats#6055065c2e95690bc60d89fe",

		  "celular": "5532984748644",

		  "phone_id": "5fb9305b5e3b368d9f99020c",

		  "chat_id": "6055065c2e95690bc60d89fe",

		  "chat_created": "2021-03-19 20:15:24.955000",

		  "datetime_post": "2021-03-20 08:08:48.690948"

		}';//resposta no webhook

		$zg = new zapguru;

		$ret = $zg->atualizaCliente($config);

		*/



		$ret['exec'] = false;

		if(isJson($config)){

			$arr_conf = lib_json_array($config);
			if(isset($arr_conf['celular'])){

				$celular = $this->maskTelefone($arr_conf['celular']);

				if(!$celular)

					return $ret;



				$where = "WHERE Celular='".$celular."' AND ".compleDelete();

				$dadosCli = dados_tab($GLOBALS['tab15'],'*',$where);

				if(!$dadosCli){
					$celular = $arr_conf['celular'];

					$where = "WHERE telefonezap='".$celular."' AND ".compleDelete();
				}

				$ret['exec'] = salvarAlterar("UPDATE ".$GLOBALS['tab15']." SET zapguru='".addslashes($config)."' $where");
				// echo $where;
				// dd($ret);
				if(isset($arr_conf['origem']) && $arr_conf['origem']=='envia_campos'&&is_array($arr_conf['campos_personalizados'])){

					$post['conf'] = 's';

					if($dadosCli){

						$post['token'] 	= $dadosCli[0]['token'];

						$ac				= 'alt';

					}else{

						$post['token'] 	= uniqid();

						$post['zapguru']= $config;

						$ac				= 'cad';

					}

					foreach($arr_conf['campos_personalizados'] As $k=>$v){

						if($k == 'Nome'){

							$nom = explode(' ',$v);

							$sobrenome = str_replace($nom[0],'',$v);

							$post[$k] = $nom[0];

							$post['sobrenome'] = $sobrenome;

						}else{

							$post[$k] = $v;

						}

					}

					$cond_valid = $where;

					$type_alt = 1;

					$config2 = array(

						'tab'=>$GLOBALS['tab15'],

						'valida'=>true,

						'condicao_validar'=>$cond_valid,

						'sqlAux'=>false,

						'ac'=>$ac,

						'type_alt'=>$type_alt,

						'dadosForm' => $post

					);

					$ret['atualizarCampos'] = lib_json_array(lib_salvarFormulario($config2));//Declado em Lib/Qlibrary.php

				}else{

					$ret['atualizarCampos'] = $this->atualizarCampos(array('celular_completo'=>$celular));

					$ret['exec'] = $ret['atualizarCampos']['exec'];

				}

				//$ret['mens'] = $ret['atualizarCampos']['mens'];

				//$ret['dadosCli'] = $dadosCli;

			}

		}

		return $ret;

	}

	function btnZapGuru($id_cliente=false){

		/*

		$zg = new zapguru;

		$ret = $zg->btnZapGuru($config);

		*/

		$ret = false;

		if($id_cliente){

			$dadosCli = dados_tab($GLOBALS['tab15'],'zapguru,Celular',"WHERE id='".$id_cliente."'");

			if(isJson($dadosCli[0]['zapguru'])){

				$arr_zap = lib_json_array($dadosCli[0]['zapguru']);

				if(isset($arr_zap['link_chat'])&&!empty($arr_zap['link_chat'])){

					$arr_zap['link_chat'] = str_replace('app4.zap.guru','s4.chatguru.app',$arr_zap['link_chat']);
					$ret['script'] = '<script>$(function(){ zapguru_btnAbrirChat(\''.$arr_zap['link_chat'].'\');});</script>';

				}

			}else{

				//if(isAdmin(1)){

				if(isset($dadosCli[0]['Celular'])&&!empty($dadosCli[0]['Celular'])){

					$conf_di = array('celular_completo'=>$dadosCli[0]['Celular']);

					$ret['dialog_execute'] = $this->dialog_execute($conf_di);

					if($ret['dialog_execute']['exec']){

						//$exec_dialog = json_encode($dialog_execute);

					}else{

						$conf_di['phone_id'] = '600ef75509c6487a5aaff0c2';

						$ret['dialog_execute'] = $this->dialog_execute($conf_di);

						//$exec_dialog = json_encode($dialog_execute);

					}

					if(!$ret['dialog_execute']['exec']&&isset($conf_di['celular_completo'])){

						$conf_di['celular_completo'] = str_replace(')9',')',$conf_di['celular_completo']);

						if($ret['dialog_execute']['exec']){

							//$exec_dialog = json_encode($dialog_execute);

						}else{

							$conf_di['phone_id'] = '600ef75509c6487a5aaff0c2';

							$ret['dialog_execute'] = $this->dialog_execute($conf_di);

							//$exec_dialog = json_encode($dialog_execute);

						}

					}

				}

					$ret['script'] = '<script>$(function(){ zapguru_criarChat(\''.$dadosCli[0]['Celular'].'\');});</script>';

				//}

			}

		}

		return $ret;

	}
	/**
	 * Metodo para retornar as tags da string zapguru retorna tags formatadas
	 * @param string $string_zapguru string da requisição do zapguru salva no banco de dados
	 * @param string $html string contento as tags badg
	 * @uso (new zapguru)->get_tags($string_zapguru);
	 */
	public function get_tags($string_zapguru){
		$ret = false;
		if(isJson($string_zapguru)){
			$tm = '<span class="badge">{tag}</span><br>';
			$arr = lib_json_array($string_zapguru);
			if(isset($arr['tags']) && is_array($arr['tags'])){
				foreach ($arr['tags'] as $v) {
					$ret .= str_replace('{tag}',$v,$tm);
				}
			}
		}
		return $ret;
	}
}
