<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InterajaiController extends Controller
{
    public function webhook($config=false){
		$ret = false;
		$json = file_get_contents('php://input');


		$arr_json = Qlib::lib_json_array($json);
        $event = isset($arr_json['origem']) ? $arr_json['origem'] : '';
        $telefonezap = $this->get_telefone($arr_json);
        $nome = $this->get_nome($arr_json);
        // $email = $this->get_email($arr_json);
        // $id_cliente = $this->get_client_id($arr_json);
        Log::info('Webhook zapguru '.$event.':', $arr_json);
        $ret['exec'] = false;
        $ret['arr_json'] = $arr_json;
        if(isset($arr_json['url'])){
			$nome = $this->get_nome($arr_json);
            //Atualiza o cliente e lead de acornto com o retorno
            $dadd = [
                'interajai' => $json,
            ];
            $add_cliente = false; //para adicionar um clientes na tabela de clientes
            $where = "WHERE telefonezap='$telefonezap'";
            //checar para ver se o cadastro está com o esse telefone caso não encontrar use a consulta pelo nome
            if($add_cliente){
                if(Qlib::totalReg('clientes',$where)==0){
                    $where = "WHERE Nome='$nome'";
                }
            }
            if($telefonezap){
                //Casa ja tenha um telefonezap verificar se o lead ja virou cliente
                $where = "WHERE telefonezap='$telefonezap'";
                //checar para ver se o cadastro está com o esse telefone caso não encontrar use a consulta pelo nome
                if(Qlib::totalReg('clientes',$where)>0){
                    $cl = Qlib::update_tab('clientes',$dadd,$where,'edit_all');
                    $ret['clientes'] = $cl;
                    // dd($ret,$where,$add_cliente,$telefonezap,$dadd);
                    return $ret;
                }
            }
            if($add_cliente){
                //adiciona o cliente na tabela de clientes
                $cl = Qlib::update_tab('clientes',$dadd,$where);
                $ret['clientes'] = $cl;
            }
            //verificar se no nome tem o ID do registro no nesse caso o nome seria: Vinícius Rodrigues |36098
            $a_nome=explode('-',$nome);
            if(isset($a_nome[1])){
                $arr_cli = explode('|',trim($a_nome[1]));
                $id_cliente = isset($arr_cli[0]) ? $arr_cli[0] : null;
                if(!is_null($id_cliente)){
                    $id_cliente = trim($id_cliente);
                }
            }else{
                $id_cliente = isset($a_nome[1]) ? $a_nome[1] : null;
            }
            $whereLead = "WHERE celular='$telefonezap'";
            $whereLead2 = "WHERE nome='$nome'";
            $tabLead = 'capta_lead';
            if(Qlib::totalReg($tabLead,$whereLead)>0){
                $ddlead = [
                    'interajai' => $json,
                ];
            }elseif(Qlib::totalReg($tabLead,$whereLead2)>0){
                $ddlead = [
                    'interajai' => $json,
                ];
            }else{
                $ddlead = [
                    'nome' => $nome,
                    // 'email' => $email,
                    'celular' => $telefonezap,
                    'interajai' => $json,
                    'token' => uniqid(),
                    'tag_origem' => 'interajai',
                    'excluido' => 'n',
                    'deletado' => 'n',
                    'atualizado' => Qlib::dataLocalDb(),
                    // 'EscolhaDoc' => 'CPF',
                ];
            }
            $lead = Qlib::update_tab($tabLead,$ddlead,$whereLead);
            if(isset($lead['exec']) && !$lead['exec'] && $telefonezap){
                $whereLead = "WHERE celular='$telefonezap'";
                $lead = Qlib::update_tab($tabLead,$ddlead,$whereLead);
            }
            $ret['exec'] = isset($lead['exec']) ? $lead['exec'] : false;
            $ret['capt_lead'] = $lead;

		}



		/*$arquivo = fopen(dirname(__FILE__).'/atendimento.txt','a');

				fwrite($arquivo, $json.',');

				//Fechamos o arquivo após escrever nele

				fclose($arquivo);*/

				// dump($ret);

		return $ret;

	}
    /**
     * returna o nome do chat na query string
     * usando um campo persolanizado
     */

    public function get_telefone($int){
        if(is_string($int)){
            if(json_validate($int)){
                $int = Qlib::lib_json_array($int);
            }
        }
        $celular = isset($int['phone']) ? $int['phone'] : '';
        $celular = str_replace('+','',$celular);
        return $celular;
    }
    /**
     * returna o nome do chat na query string
     * usando um campo persolanizado
     */

    public function get_nome($int){
        if(is_string($int)){
            if(json_validate($int)){
                $int = Qlib::lib_json_array($int);
            }
        }
        $nome = isset($int['name']) ? $int['name'] : null;
        // if(empty($nome)){
        //     $nome = isset($int['campos_personalizados']['Nome']) ? $int['campos_personalizados']['Nome'] : '';
        // }
        $nome = trim($nome);

        return $nome;
    }
}
