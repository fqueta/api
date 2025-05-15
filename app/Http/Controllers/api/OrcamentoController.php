<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\MatriculasController;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrcamentoController extends Controller
{
    /**
     * Grava um orçamento de uma requisição da API
     */
    public function gerar_orcamento(Request $request)
    {
        $d = $request->all();
        $ret = ['exec'=>false];
        if(isset($d['id_cliente']) && isset($d['id_curso'])){
            $d['token'] 	= isset($d['token'])	?$d['token']	:uniqid();
            $d['status'] 	= isset($d['status'])	?$d['status']	:1;
            $d['situacao'] 	= isset($d['situacao'])	?$d['situacao']	:'a';
            $d['excluido'] 	= isset($d['excluido'])	?$d['excluido']	:'n';
            $d['deletado'] 	= isset($d['deletado'])	?$d['deletado']	:'n';
            $d['ac'] 	= isset($d['ac'])	?$d['ac']	:'cad';
            $d['id_responsavel'] = isset($d['id_responsavel'])	? $d['id_responsavel']	: 0;
            $d['etapa_atual'] = isset($d['etapa_atual']) ? $d['etapa_atual'] : 4; //Lead interessado
            //agora precisamos gerar um valor padrão
            $cursos_c = new CursosController;
            $mc = new MatriculasController;
            if($d['ac']=='cad' && isset($d['id_curso'])){
                $tipo_curso = $cursos_c->tipo($d['id_curso']);
                if($tipo_curso==1){
                    $total_curso = Qlib::buscaValorDb0('cursos','id',$d['id_curso'],'valor');
                }else{
                    $total_curso = 0;
                }
                $d['total'] = $total_curso;
                $d['valor'] = isset($d['valor']) ? $d['valor'] : 0; //Lead interessado
                $d['acao'] = $d['ac'];
                $d['html_exibe'] = false;
                $turmas = $cursos_c->selectTurma($d);
                $d['id_turma'] = isset($turmas['arr_id_turma'][0]) ? $turmas['arr_id_turma'][0] : 0;
                $arr_tabelas = $this->select_tabela_preco($d['id_curso'],$d['id_turma']);
                if($tipo_curso==2 && $arr_tabelas){
                    //array de orçamento
                    $d['orc'] = '';
                    $sele_valores = isset($arr_tabelas['dados'][0]['nome']) ? $arr_tabelas['dados'][0]['nome'] : '';
                    if($arr_tabelas){
                        $orc = [
                            'sele_valores'=>$sele_valores,
                            'sele_pag_combustivel'=>'por_voo',
                        ];
                        $d['orc'] = Qlib::lib_array_json($orc);
                    }
                    $d['id_turma'] = isset($turmas['arr_id_turma'][0]) ? $turmas['arr_id_turma'][0] : 0;
                    // dd($arr_tabelas);
                }
            }
            $ret = $mc->salvarMatricula($d);
            if(is_string($ret)){
                $ret = Qlib::lib_json_array($ret);
            }
            if(isset($ret['exec']) && isset($ret['idCad']) && ($id_matricula = $ret['idCad'])){
                $id_matricula = base64_encode($id_matricula);
                $link = Qlib::raiz().'/admin/cursos?sec=aW50ZXJlc3NhZG9z&list=false&regi_pg=100&pag=0&acao=alt&id='.$id_matricula.'&redirect_base=aHR0cHM6Ly9jcm0uYWVyb2NsdWJlamYuY29tLmJyL2FkbWluL2N1cnNvcz9zZWM9YVc1MFpYSmxjM05oWkc5eg==';
                $ret['link_proposta_admin'] = $link;
            }
            if(isset($ret['exec']) && isset($ret['dados']['token']) && ($token_matricula = $ret['dados']['token'])){
                $link_cliente = 'https://propostas.aeroclubejf.com.br/orcamento-pdf/'.$token_matricula;
                $ret['link_proposta_cliente'] = $link_cliente;
            }
        }
        return $ret;
        // dd($d);
    }
    /**
     * Gera um array contendo uma lista de todas as tabelas disponiveis para a turma e o curso selecionado
     */
    public function select_tabela_preco($id_curso,$id_turma=0){
        $token_curso = Qlib::buscaValorDb0('cursos','id',$id_curso,'token');
		if($id_turma){
            $token_turma = Qlib::buscaValorDb0('turmas','id',$id_turma,'token');
            $sql = "SELECT * FROM tabela_nomes WHERE ativo = 's' AND libera = 's' AND ".Qlib::compleDelete()." AND (cursos LIKE '%".$token_curso."%') AND (turmas LIKE '%".$token_turma."%') ORDER BY nome ASC";
			$arr_tabelas = Qlib::sql_array($sql,'nome','url','','','',false);
            $dados = Qlib::buscaValoresDb($sql);
            // dd($arr_tabelas,$id_turma);
		}else{
            $sql = "SELECT * FROM tabela_nomes WHERE ativo = 's' AND libera = 's' AND ".Qlib::compleDelete()." AND (cursos LIKE '%".$token_curso."%' OR cursos='') ORDER BY nome ASC";
            $arr_tabelas = Qlib::sql_array($sql,'nome','url');
        }
        $dados = Qlib::buscaValoresDb($sql);
        $ret['dados'] = $dados;
        $ret['arr_tabelas'] = $arr_tabelas;
        return $ret;
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
    public function show(string $id)
    {
        $d = request()->all();
        $token = $id;
        $exibe_parcelamento = isset($d['ep']) ? $d['ep'] : null;
        $ret = (new MatriculasController)->gerar_orcamento($token, $exibe_parcelamento);

        return response()->json($ret);
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
     * Para diparar a assinatura de uma proposta mediante o token da matricula
     */
    public function assinar_proposta(string $token){
        $d = request()->all();
        if(!isset($d['token_matricula'])){
            $d['token_matricula'] = $token;
        }
        $ret = (new MatriculasController)->assinar_proposta($d);
        return $ret;
    }

    public function add_update($config=[]){
        //indentificar o curso
        //selecionar a primeira turma disponivel
        //
    }
    /**
     * Webhook para interagir com o CRM
     */
    public function webhook($d=[]){
        $d['token_externo'] = isset($d['token_externo']) ? $d['token_externo'] : '';
        $id = isset($d['id']) ? $d['id'] : '';
        $ret['exec'] = false;
        $ret['status'] = 500;
        $ret['message'] = 'Error updating';
        $tab = 'matriculas';
        if($d){
            $ret['exec'] = DB::table($tab)->where('id',$id)->update($d);
            if($ret['exec']){
                //salvar um meta_campo
                if($id_contrato=$d['token_externo']){
                    $ret['meta'] = Qlib::update_matriculameta($id,'id_contrato_leilao',$id_contrato);
                }
                $ret['status'] = 200;
                $ret['message'] = 'updated successfully';
                $ret['data'] = DB::table($tab)->find($id);
            }
        }
        return response()->json($ret);
    }
}
