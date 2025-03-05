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
                $campo_meta1 = $zc->campo_processo;
                $campo_meta2 = $zc->campo_links;
                $processo = Qlib::get_matriculameta($id_matricula,$campo_meta1,true);
                if($processo){
                    $links = Qlib::get_matriculameta($id_matricula,$campo_meta2,true);
                    $arr_processo = Qlib::lib_json_array($processo);
                    $arr_links = Qlib::lib_json_array($links);
                    $ret['arr_processo'] = $arr_processo;
                    $ret['arr_links'] = $arr_links;
                }
                // return $processo;
            }
            return view('crm.painel.assinaturas',$ret);
        }
    }
}
