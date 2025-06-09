<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\api\ZapsingController as ApiZapsingController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MatriculasController;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class ZapsingController extends Controller
{
    public function painel_assinaturas($token){
        if($token){
            $ret = ['exec'=>false];
            $dm = (new MatriculasController)->dm($token);
            //recuperar o painel de processos de assinaturas
            $id_matricula = isset($dm['id']) ? $dm['id'] : null;
            if($id_matricula){
                $zc = new ApiZapsingController;
                $campo_meta0 = $zc->campo_envio;
                $campo_meta1 = $zc->campo_processo;
                $campo_meta2 = $zc->campo_links;
                $processo = Qlib::get_matriculameta($id_matricula,$campo_meta1,true);
                $ret['arr_processo'] = [];
                $ret['arr_link'] = [];
                if($processo){
                    $links = Qlib::get_matriculameta($id_matricula,$campo_meta2,true);
                    $arr_processo = Qlib::lib_json_array($processo);
                    if(!isset($arr_processo['signers']) && isset($arr_processo['enviar']['response']['signers'])){
                        $arr_processo['signers'] = $arr_processo['enviar']['response']['signers'];
                    }
                    // dump($arr_processo);
                    $arr_links = Qlib::lib_json_array($links);
                    $ret['arr_processo'] = $arr_processo;
                    $ret['arr_links'] = $arr_links;
                }else{
                    $envio = Qlib::get_matriculameta($id_matricula,$campo_meta0,true);
                    if($envio){
                        $arr_envio = Qlib::lib_json_array($envio);
                        $ret['envio'] = isset($arr_envio['response']) ? $arr_envio['response'] : false;
                    }
                    //colocar um botão para enviar para o zapsing
                    // return '<p><i class="text-danger">Processo de assinatura incompleto!!</i></p>';
                }
                return view('crm.painel.assinaturas',$ret);
                // return $processo;
            }else{
                return '<p><i class="text-danger">Matricula não encontrada!!</i></p>';
            }
        }
    }
    /**
     * Metodo para adiminstrar um envio de mensagem do zapsing
     * @param string $token
     */
    public function enviar_link_assinatura($token_orcamento=null){
        $d = (new MatriculasController)->dm($token_orcamento);
        // dd($d);
        $ret['exec'] = false;
        $webhook_zapsing = isset($d['webhook_zapsing']['enviar']['response']) ? $d['webhook_zapsing']['enviar']['response'] : [];
        $email = isset($d['email']) ? $d['email'] : false;
        $app = config('app.name');
        $temm = 'Olá *{nome}* sua assinatura foi solicitada, pelo App *{app}*, para o documento, *{nome_doc}* segue o link de assinatura {link}';
        $i = 0;
        // dd($webhook_zapsing['signers']);
        if(isset($webhook_zapsing['signers'][$i]['sign_url']) && is_string($webhook_zapsing['signers'][$i]['sign_url']) && ($signers=$webhook_zapsing['signers'])){
            if(is_array($signers)){
                foreach ($signers as $k => $signer) {
                    $nome = isset($signer['name']) ? $signer['name'] : '';
                    $nome_doc = isset($signer['name']) ? $signer['name'] : '';
                    // $email = isset($signering['email']) ? $signering['email'] : $email;
                    $email = isset($signer['email']) ? $signer['email'] : '';
                    $link = isset($signer['sign_url']) ? $signer['sign_url'] : '';
                    $mens = str_replace('{nome}',$nome,$temm);
                    $mens = str_replace('{nome_doc}',$nome_doc,$mens);
                    $mens = str_replace('{link}',$link,$mens);
                    $mens = str_replace('{app}',$app,$mens);
                    $ret['signer'][$k]['name'] = $nome;
                    $ret['signer'][$k]['email'] = $email;
                    $ret['signer'][$k]['nome_doc'] = $nome_doc;
                    $ret['signer'][$k]['link'] = $link;
                    // $ret['signer'][$k]['name'] = $nome;
                    //Ver se enviar com o telefone ou o id do usuario..
                    // dump($mens,$email,$link);
                    // $email = $request->get('email') ? $request->get('email') : 'ger.maisaqui1@gmail.com';
                    // $ret['signer'][$k]['criar_chat'] = (new ZapsingController)->criar_chat(['email'=>$email,'text'=>$mens]);
                    //Registrar um log
                    // Log::info('enviar_link_assinatura para o zapguru:', $ret);
                }
            }

        }
        return $ret;
    }
}
