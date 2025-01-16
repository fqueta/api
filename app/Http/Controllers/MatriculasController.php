<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculasController extends Controller
{
    public $table;
    public function __construct()
    {
        $this->table = 'matriculas';
    }
    public function index(Request $request)
    {
        // dd($request->get('status'));
        $d = DB::table($this->table)->select('matriculas.*','clientes.Nome','clientes.sobrenome','clientes.Email')
        ->join('clientes', 'clientes.id','=','matriculas.id_cliente')
        ->where('matriculas.excluido','=','n')->where('matriculas.deletado','=','n')->orderBy('matriculas.id','asc');
        $limit = 25;
        if($request->has('limit')){
            $limit = $request->get('limit');
        }
        if($request->has('status')){
            if($request->get('status')=='todos_matriculados'){
                $d = $d->where('matriculas.status', '!=',1);
            }else{
                $d = $d->where('matriculas.status', '=',$request->get('status'));
            }
        }
        if($request->has('token_externo')){
            $tkex = $request->get('token_externo');
            if($tkex=='null'){
                $d = $d->whereNull('matriculas.token_externo');
            }elseif(is_null($tkex)){
                $d = $d->whereNotNull('matriculas.token_externo');
            }else{
                $d = $d->where('matriculas.token_externo', '=',$request->get('token_externo'));
            }
        }
        if($request->has('id_cliente')){
            $d = $d->where('matriculas.id_cliente', '=',$request->get('id_cliente'));
        }
        if($limit=='todos'){
            $d = $d->get();
        }else{
            $d = $d->paginate($limit);
        }
        $exibe_contrato = $request->has('contrato') ? $request->has('contrato') : 's';
        $ret['exec'] = false;
        $ret['status'] = 404;
        $ret['total'] = 0;
        $ret['data'] = [];
        if($d->count() > 0){
            if($exibe_contrato=='s'){
                foreach ($d as $k => $v) {
                    if($nc=$this->numero_contrato($v->id)){
                        $d[$k]->numero_contrato = $nc;
                    }
                }
            }
            $ret['total'] = $d->count();
            $ret['data'] = $d;
            $ret['exec'] = true;
            $ret['status'] = 200;
        }
        return $ret;
    }
    /**
     * Metodo para exibir o numero do contrato
     * @param int $id_matricula
     */
    public function numero_contrato($id_matricula=false){
        $ret = false;
        if($id_matricula){
            //uso $ret = (new CursosController)->numero_contrato($id_matricula);
            $ret = false;
            if($id_matricula){
                $json_contrato = Qlib::buscaValorDb0('matriculas','id',$id_matricula,'contrato');
                $arr_contrato = Qlib::lib_json_array($json_contrato);
                if(isset($arr_contrato['data_aceito_contrato']) && !empty($arr_contrato['data_aceito_contrato'])){
                    $arrd = explode('-',$arr_contrato['data_aceito_contrato']);
                    if(isset($arrd[1])){
                        $ret = $id_matricula.'.'.$arrd[1].'.'.$arrd[0];
                    }
                }

            }
            return $ret;
        }
    }
    /**
     * Metodos para salvar um orçamento assinado para ser exibido dps no painel do CRM.
     * @param string $token_matricula,array $dm= dados da matricula
     */
    public function salva_orcamento_assinado($token=false,$dm=false){
        $ret['exec'] = false;
        $campo_meta1 = 'assinado';
        $campo_meta2 = 'contrato_assinado';
        // $campo_meta3 = 'total_assinado';
        if($token && !$dm){
            $dm = Matricula::where('token',$token)->get();
            if($dm->count() > 0){
                $dm = $dm[0];
            }
        }
        $ret['dm'] = $dm;
        if(isset($dm['id']) && isset($dm['contrato']) && isset($dm['orc'])  && isset($dm['total'])){
            $ret['s1'] = Qlib::update_matriculameta($dm['id'],$campo_meta1,'s');
            $ret['s2'] = Qlib::update_matriculameta($dm['id'],$campo_meta2,Qlib::lib_array_json([
                'orc'=>$dm['orc'],
                'totais'=>@$dm['totais'],
                'subtotal'=>@$dm['subtotal'],
                'total'=>@$dm['total'],
                'cliente_id'=>@$dm['id_cliente'],
                'porcentagem_comissao'=>@$dm['porcentagem_comissao'],
                'comissao'=>@$dm['valor_comissao'],
            ]));
            if($ret['s1'] && $ret['s2']){
                $ret['exec'] = true;
            }
        }
        // $ret['dm'] = $dm;
        return $ret;
    }
    /**
     * Metodo para retornar um array com os dados do contrato assinado
     */
    public function get_matricula_assinado($token=false){
        $matricula_id = Qlib::get_matricula_id_by_token($token);
        //verifica se está assinado
        $ret['exec'] = false;
        $ret['data'] = [];
        if(!$matricula_id){
            return $ret;
        };
        $campo_meta1 = 'assinado';
        $campo_meta2 = 'contrato_assinado';
        $ver = Qlib::get_matriculameta($matricula_id,$campo_meta1,true);
        if($ver=='s'){
            $data = Qlib::get_matriculameta($matricula_id,$campo_meta2,true);
            if($data){
                $ret['exec'] = true;
                $dm = $this->dm($token);
                // if(!$dm){

                // }
                $ret['dm'] = $dm;
                $ret['data'] = Qlib::lib_json_array($data);
                $aer = DB::table('aeronaves')->where('excluido', '=','n')->where('deletado', '=','n')->get();
                $aeronaves_arr = [];
                if(count($aer)!=0){
                    foreach ($aer as $ka => $va) {
                        $aeronaves_arr[$va->id] = $va->nome;
                    }
                }
                $ret['aeronaves'] = $aeronaves_arr;
            }
        }
        return $ret;
    }
    /**
     * Dados de um orçamento ou dados da matricula
     * @param string $token
     * @param array $ret
     */
    public function dm($token){
        $dm = Matricula::select('matriculas.*',
        'clientes.Nome','clientes.sobrenome','clientes.telefonezap','clientes.Tel','clientes.Email',
        'cursos.tipo as tipo_curso','cursos.config','cursos.modulos as modulos_curso','cursos.parcelas as parcelas_curso','cursos.valor_parcela as valor_parcela_curso','cursos.nome as nome_curso','cursos.titulo as titulo_curso','cursos.inscricao as inscricao_curso','cursos.valor as valor_curso','cursos.token as token_curso')
        ->join('clientes','matriculas.id_cliente','=','clientes.id')
        ->join('cursos','matriculas.id_curso','=','cursos.id')
        ->where('matriculas.token',$token)
        ->get();
        if($dm->count() > 0){
            $dm = $dm->toArray();
            $dm = $dm[0];
        }else{
            return false;
        }
        $ret = $dm;
        return $ret;
    }
    // public function gerenciarPromocao($totalOrcamento,$id_curso){
    //     global $tab73;
    //     $id_curso = addslashes('"'.$id_curso.'"');

    //     $sql = "SELECT * FROM `".$GLOBALS['tab73']."` WHERE `id_produto` LIKE '%".$id_curso."%' AND `ativo`='s' AND `inicio` <= '".date('Y-m-d H:m:i')."' AND `fim` >= '".date('Y-m-d H:m:i')."' AND `quantidade` > '0'";
    //     if(isset($_GET['f']))
    //         echo $sql;
    //     // $dados = buscaValoresDb($sql);
    //     $dados = false;

    //     $ret['precoInicial'] = $totalOrcamento;

    //     $ret['precoInicial_html'] = '<span class="preco-custo">R$ '.number_format($totalOrcamento,'2',',','.').'</span>';

    //     $ret['precoFinal_html'] = false;

    //     //print_r($dados);

    //     if($dados){

    //         $ret['precoInicial'] = $totalOrcamento;

    //         $ret['tipo_reducao'] = $dados['tipo_reducao'];

    //         $ret['valor_reducao'] = $dados['valor'];

    //         $ret['id_cupom'] = $dados['id'];

    //         if($dados['tipo_reducao'] == 'valor'){

    //             $ret['precoFinal'] = ($ret['precoInicial']) - ($dados['valor']);

    //             $ret['precoInicial_html'] = '<span class="preco-custo riscado">De: R$ '.number_format($ret['precoInicial'],'2',',','.').'</span><br>';

    //             $ret['precoFinal_html'] = '<span class="preco-custo">Por: R$ '.number_format($ret['precoFinal'],'2',',','.').'</span>';

    //         }elseif($dados['tipo_reducao'] == 'porcentagem'){

    //             $porce = ($ret['precoInicial']) * ($dados['valor'] / 100) ;

    //             $ret['valorPorcentagem'] = $porce;

    //             $ret['precoFinal'] = ($ret['precoInicial']) - ($porce);

    //             $ret['precoInicial_html'] = '<span class="preco-custo riscado">De: R$ '.number_format($ret['precoInicial'],'2',',','.').'</span><br>';

    //             $ret['precoFinal_html'] = '<span class="preco-custo">Por: R$ '.number_format($ret['precoFinal'],'2',',','.').'</span>';

    //         }

    //     }else{

    //             $ret['precoFinal'] = $ret['precoInicial'];

    //             //$ret['precoInicial_html'] = '<span class="preco-custo riscado">De: R$ '.number_format($ret['precoInicial'],'2',',','.').'</span><br>';

    //             //$ret['precoFinal_html'] = '<span class="preco-custo">Por: R$ '.number_format($ret['precoFinal'],'2',',','.').'</span>';

    //     }

    //     return $ret;

    // }
    public function tag_apresentacao_orcamento($dados){
        $dadosD = explode(' ',$dados['atualizado']);
        $dias = isset($dias)?$dias: 7;
        $validade = Qlib::CalcularVencimento(Qlib::dataExibe($dadosD[0]),$dias);
        $ret = '
                <p align="center" style="font-size:15pt;">
                    <b>Cliente:</b> '.$dados['Nome'].' '.$dados['sobrenome'].'  <b>N°: </b> '.$dados['id'].'
                    <br>
                    <b>Telefone:</b> '.$dados['telefonezap'].'  '.$dados['Tel'].' <br>
                    <b>Email:</b> '.$dados['Email'].'  <br>
                    <b>Data:</b> '.Qlib::dataExibe($dados['atualizado']).' <b>Validade:</b> '.Qlib::dataExibe($validade).'<br>
                </p>';
        return $ret;
    }
    /**
     * Metodo para gerar um orçamento atualizado
     * @param string $tokenOrc token do orçamento
     * @param string $exibir_parcelamento 's' para sim 'n' para não
     */
    public function gerar_orcamento($tokenOrc,$exibir_parcelamento=false){
        global $tab10,$tab12,$tab15,$tab50;
        $tab10 = 'cursos';
        $tab15 = 'clientes';
        $tab12 = 'matriculas';
        $tab50 = 'tabela_nomes';
        if($tokenOrc){
            $mensComb = false;
            $tab12 = 'matriculas';
			$is_signed = $this->verificaDataAssinatura(['campo_bus'=>'token','token'=>$tokenOrc]);
			$arr_tabelas = Qlib::sql_array("SELECT * FROM $tab50 WHERE ativo = 's' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'nome','url');
			$dados = $this->dm($tokenOrc);

			$dias = isset($dias)?$dias: Qlib::qoption('validade_orcamento');
            if($dados){
				$dadosOrc = false;
				$tipo_curso = $dados['tipo_curso'];
				$valor_combustivel = 0;
                $btn_aceito_aceitar = '';
                // $aceito_proposta = buscaValorDb($GLOBALS['tab12'],'token',$_GET['tk'],'contrato');
				// $arr_aceito = lib_json_array($aceito_proposta);
				if($is_signed){
                    // $men = 'Proposta aceita em '.dataExibe(@$arr_aceito['data_aceito_contrato']).' Ip: '.$arr_aceito['ip'].'';
					$men = 'Proposta aceita em '.Qlib::dataExibe($is_signed).'';
					$btn_a = '<span style="color:#b94a48">'.__($men).'</span>';
				}else{
                    $btn_aceito_proposta = (new SiteController)->short_code('btn_aceito_proposta',false,false);
                    $link = 'https://crm.aeroclubejf.com.br/solicitar-orcamento/proposta/'.$tokenOrc;
					$btn_a = '<a href="'.$link.'" target="_BLANK" style="display:block;height: 65px; width:250px"><span style="display:none;">cliente aqui</span><img src="'.@$btn_aceito_proposta[0]['url'].'" style="width:250px;cursor:pointer"/></a>';
				}
				$btn_aceito_aceitar = '<div align="center">'.$btn_a.'</div>';
				if(!empty($dados['orc'])){
                        if(is_array($dados['orc'])){
                           $dadosOrc = $dados['orc'];
                        }else{
                           $dadosOrc = json_decode($dados['orc'],true);
                        }
					//$dadosOrc['desconto_porcento'] = buscaValorDb($GLOBALS['tab50'],'');
					$dados['sele_valores'] = @$dadosOrc['sele_valores'];
					$arr_config_tabela = array();
					// lib_print($dadosOrc['tipo']);
                       if(isset($dadosOrc['sele_valores']) && !empty($dadosOrc['sele_valores'])){
						$ret['tabela_preco'] = $dadosOrc['sele_valores'];
						$configTabela = Qlib::buscaValorDb0($tab50,'url',$dadosOrc['sele_valores'],'config');
						if(!empty($configTabela)){
							$arr_config_tabela = json_decode($configTabela,true);
							if(isset($arr_config_tabela['desconto']['valor'])&&!empty($arr_config_tabela['desconto']['valor'])){
								$dadosOrc['desconto_porcento'] = $arr_config_tabela['desconto']['valor'];
							}
                               if(isset($arr_config_tabela['validade']['dias'])&&!empty($arr_config_tabela['validade']['dias'])){
								$dias = $arr_config_tabela['validade']['dias'];
							}
						}
					}
					$dados['modulos'] = @$dadosOrc['modulos'];
					$dados['taxas'] = @$dadosOrc['taxas'];
					$dados['combustivel'] = isset($dadosOrc['combustivel'])?$dadosOrc['combustivel']:false;
					$dados['desconto_porcento'] = @$dadosOrc['desconto_porcento'];
					if(isset($dadosOrc['desconto']) && !empty($dadosOrc['desconto'])){
						$dados['desconto'] = @$dadosOrc['desconto'];
						$dados['desconto'] = Qlib::precoDbdase(@$dados['desconto']);
					}
					$dados['entrada'] = @$dadosOrc['entrada'];
					$dados['entrada'] = Qlib::precoDbdase($dados['entrada']);
				}
				$ret['nome_arquivo'] = 'Proposta '.$dados['id'];
				// $sqlDdCli ="SELECT * FROM ".$tab15." WHERE `id` = '".$dados['id_cliente']."'";
				// $sqlDdCu ="SELECT * FROM ".$tab10." WHERE `id` = '".$dados['id_curso']."'";
				// $dadosCli = DB::select($sqlDdCli);
				// $dadosCu = DB::select($sqlDdCu);
				// $descontoFooter = false;
                   // if(isset($dadosCu[0])){
                   //     $dadosCu[0] = (array)$dadosCu[0];
                   // }
                   // if(isset($dadosCli[0])){
                   //     $dadosCli[0] = (array)$dadosCli[0];
                   // }
                   //if($dados['tipo_curso']==2){
                if($dados['tipo_curso']==2){
					$configMet=$dados;
					$configMet['email'] = $dados['Email'];
					// metricasOrcamento($configMet);//para salvar as estatisticas do orçamento;
					$arr_wid = array('5%','50%','25%','10%','10%');
					if(!isset($dados['sele_valores'])){
						$ret['table'] = Qlib::formatMensagem0('Erro: Tabela não selecionada!!','danger',100000);
						return $ret;
					}
					$label_sele_valores = isset($arr_tabelas[$dados['sele_valores']])?$arr_tabelas[$dados['sele_valores']]:false;
					$arr_wid2 = array('5%','20%','70%','10%');
					if(isset($dados['Nome']) && isset($dados['nome_curso'])){
						$ret['id_curso'] = $dados['id_curso'];
						$dadosD = explode(' ',$dados['atualizado']);
						// $valdata = explode('-',$dadosD[0]);
						$ret['nome_arquivo'] = 'Proposta '.$dados['id']. ' '.$dados['Nome'].' '.$dados['nome_curso'];
						//$validade = ultimoDiaMes($valdata[1],$valdata[0]).'/'.$valdata[1].'/'.$valdata[0];
						if(!$dias){
                                  $dias = 7;
                              }
                              $dadosD = explode(' ',$dados['atualizado']);
                              $validade =  Qlib::CalcularVencimento(Qlib::dataExibe($dadosD[0]),$dias);
                              $validade = Qlib::dataExibe($validade);
                              $dadosCli = $this->tag_apresentacao_orcamento($dados);
                            if($this->is_pdf()){
                                $dadosCli .= $btn_aceito_aceitar;
                            }
						$ret['validade'] = $validade;
						// $ret['dadosCli'] = '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>';
						$ret['dadosCli'] = $dadosCli;
						$ret['desconto'] = $dados['desconto'];
						if(isset($dados['desconto']) && $dados['desconto']>0){
							$espacoTable = false;
						}else{
							//$espacoTable = '<p></p>';
							$espacoTable = false;
						}
						if(isset($dados['modulos']) && !empty($dados['modulos'])){
							if(is_array($dados['modulos'])){
								$arr_modu = $dados['modulos'];
							}else{
								$arr_modu = json_decode($dados['modulos'],true);
							}
							$ret['vencido'] = false;
							if(strtotime(Qlib::dtBanco($validade))<strtotime(date('Y-m-d'))){
								//$ret['table'] = 'Orçamento válido até '.$validade.'';
								$ret['table'] = '<div class="col-md-12 mt-3 mb-3" style="color:#dc3545;font-size:20px;text-align:center"><b >SEU ORÇAMENTO EXPIROU, SOLICITE AO SEU CONSULTOR UM ORÇAMENTO ATUALIZADO</b></div>';
								$ret['table_adm'] = $ret['table'];
								$ret['totalCurso'] = NULL;
								$ret['vencido'] = true;
								//if(isAdmin(2)){
								//}else{
									return $ret;
								//}
							}
							$tema = '
							<p class="apresentacao" style="">Prezado(a) <strong>'.$dados['Nome'].'</strong>,<br>
								Temos o prazer em lhe apresentar nossa proposta comercial<br>Curso: <strong>'.$dados['titulo_curso'].'</strong></p>
							<table id="table1" class="table"  cellspacing="0" >

												<thead >

													<tr>

														<th style="width:'.$arr_wid[0].'"><div align="center">ITEM</div></th>

														<th style="width:'.$arr_wid[1].'"><div align="center">CRONOGRAMA</div></th>

														<th style="width:'.$arr_wid[2].'"><div align="center">AERONAVE</div></th>

														<th style="width:'.$arr_wid[3].'"><div align="center">HORAS</div></th>

														<th style="width:'.$arr_wid[4].'"><div align="right">VALOR</div></th>

													</tr>

												</thead>

												<tbody class="jss526">{{table}}

												</tbody>

												<tfoot class="jss526">{{footer}}

												</tfoot>

							</table>
							<br><br>
							<table id="table2" class="table" cellspacing="0" style="">
								<thead >
									<tr>
										<th style="width:'.$arr_wid2[0].'"><div align="center">ITEM</div></th>
										<th style="width:85%"><div align="center">DESCRIÇÃO</div></th>
										<th style="width:'.$arr_wid2[3].'"><div align="right">TOTAL</div></th>
									</tr>
								</thead>
								<tbody class="jss526">{{table2}}
								</tbody>
							</table>'.$espacoTable.'
							<table cellspacing="0" class="table">
									<tbody class="jss526">
                                        {{table3}}
									</tbody>
							</table>
							<p style="font-family:arial;font-size:9pt;text-align:right;display:none">*'.$label_sele_valores.'</p>
							';
							$tema_admn = '
							<div class="col-md-12">
								<div class="table-responsive padding-none tabe-1">
									<table id="table-admin" class="table table-striped table-hover">
										<thead >
											<tr class="th-1">
												<th style="width:100%" colspan="5"><div align="center">&nbsp;</div></th>
											</tr>
											<tr>
												<th style="width:'.$arr_wid[0].'"><div align="center">ITEM</div></th>
												<th style="width:'.$arr_wid[1].'"><div align="center">CRONOGRAMA</div></th>
												<th style="width:'.$arr_wid[2].'"><div align="center">AERONAVE</div></th>
												<th style="width:'.$arr_wid[3].'"><div align="center">HORAS/AULA</div></th>
												<th style="width:'.$arr_wid[4].'"><div align="right">TOTAL</div></th>
											</tr>
										</thead>
										<tbody class="jss526">{{table}}
										</tbody>
										<tfoot class="jss526">{{footer}}
										</tfoot>
									</table>
								</div>
								<br>
								<div class="table-responsive padding-none tabe-2">
									<table id="table3" class="table" cellspacing="0"  style="border-spacing:6px 12px;padding:10px 4px 10px 4px">
										<thead >
											<tr>
												<th style="width:'.$arr_wid2[0].'"><div align="center">ITEM</div></th>
												<th  style="width:'.$arr_wid2[2].'"><div align="center">DESCRIÇÃO</div></th>
												<th style="width:'.$arr_wid2[3].'"><div align="right">TOTAL</div></th>
											</tr>
										</thead>
										<tbody class="jss526">{{table2}}
										</tbody>
									</table>'.$espacoTable.'
								</div>
								<div class="table-responsive padding-none tabe-3">
									<table class="table" >
										<tbody class="">
                                            <tr>{{table3}}</tr>
										</tbody>
									</table>
									<p style="font-family:arial;font-size:9pt;text-align:right">*'.$label_sele_valores.'</p>
								</div>
							</div>
							<div class="row">
							{link_proposta}
							</div>
							';
							$tr = false;
							$tr2 = false;
							$tr3 = NULL;
							$tr_adm = false;
							$tr2_adm = false;
							$tr3_adm = NULL;
							$i = 1;
							$i2 = 1;
							$totalHoras = 0;
							$totalCurso = NULL;
							$total_com_desconto = NULL;
							$descontoFooter = NULL;
							if(is_array($arr_modu)){
								$ret['total'] = NULL;
								$ret['total_com_desconto'] = NULL;
								$salvaTotais = [];
								$arrTotais = false;
								if(isset($dados['totais']) && QLib::isJson($dados['totais'])){
									$arrTotais = Qlib::lib_json_array($dados['totais']);
								}
								$_GET['id_turma'] = @$dados['id_turma'];
								$arr_totais = [];
								// if(isAdmin(1)){
									// echo $is_signed;
									$arr_totais = Qlib::lib_json_array($dados['totais']);
								// }
                                      $ret['custo'] = 0;
								foreach($dados['modulos'] AS $kei=>$valo){
									$valo['id_curso'] = @$dados['id_curso'];
									$tota = $this->calcPrecModulos($valo,$dados['sele_valores'],$arr_modu);
									$total = @$tota['padrao']; //usa so valor da hora padrão
									$total_com_desconto = @$tota['valor']; //usa o valor das horas das respectiva tabelas
									if(Qlib::isAdmin(10)){
									}else {
										if($arrTotais && is_array($arrTotais)){
											$total = $arrTotais[$kei];
										}
									}
									if($is_signed){
										$total = @$arr_totais[$kei];
									}
									$ret['total'] += (double)$total;
									$ret['total_com_desconto'] += (double)$total_com_desconto;
									$salvaTotais[$kei] = @$tota['valor'];
									if(isset($tota['custo'])){
										$custo = @$tota['custo'];
										$ret['custo'] += $custo;
									}
									$valo['horas'] = isset($valo['horas'])?$valo['horas']:0;
									$valo['horas'] = (int)$valo['horas'];
									$totalHoras += @$valo['horas'];
                                  	$tr .= '<tr id="lin_'.$kei.'">
                                  				<td style="width:'.$arr_wid[0].'"><div align="center">'.$i.'</div></td>
                                  				<td style="width:'.$arr_wid[1].'"><div align="left">'.@$valo['titulo'].'</div></td>
                                  				<td style="width:'.$arr_wid[2].'"><div align="center">'.Qlib::buscaValorDb0($GLOBALS['tab54'],'id',@$valo['aviao'],'nome').'</div></td>
                                  				<td style="width:'.$arr_wid[3].'"><div align="center">'.@$valo['horas'].'</div></td>
                                  				<td style="width:'.$arr_wid[4].'"><div align="right"> '.@number_format($total,'2',',','.').'</div></td>
                                  			</tr>
                                  	';
                                  	$tr_adm .= '<tr id="lin_'.$kei.'">
                                  				<td><div align="center">'.$i.'</div></td>
                                  				<td><div align="left">'.@$valo['titulo'].'</div></td>
                                  				<td><div align="center"> '.Qlib::buscaValorDb0($GLOBALS['tab54'],'id',@$valo['aviao'],'nome').'</div></td>
                                  				<td><div align="center">'.$valo['horas'].'</div></td>
                                  				<td><div align="right"> '.@number_format($total,'2',',','.').'</div></td>
                                  			</tr>
                                  	';
                                  	$i++;
                                }
								$ret['salvaTotais'] = $salvaTotais;
								$subtotal1 = $ret['total'];
								$subtotal1comDesconto = $ret['total_com_desconto'];
								/*Desconto*/
								$footer = '';
								$totalCurso = $subtotal1;
								//precisamos verificar se o total padrão é maior que o valor
								if($subtotal1>$subtotal1comDesconto){
									$descontoFooter = NULL;
									$footer .= '
									<tr>
										<td colspan="3"><div align="right"> Subtotal</div></td>
										<td><div align="center"><b>'.$totalHoras.'</b></div></td>
										<td><div align="right"><b>'.number_format($totalCurso,'2',',','.').'</b></div></td>
									</tr>';
									//verificar qual o valor da diferença por isso é o desconto aplicado em cima do valor padrão
									$desconto0 = (double)$subtotal1 - (double)$subtotal1comDesconto;
									if($desconto0>0){
										$descontoFooter .= '
										<tr class="vermelho">
											<td colspan="4">
												<div align="right"><strong>DESCONTO</strong></div>
											</td>
											<td>
												<div align="right"><b> '.number_format($desconto0,'2',',','.').'</b></div>
											</td>
										</tr>';
										$totalCurso = ($totalCurso) - $desconto0;
									}
									$descontoFooter .= '<tr class="verde"><td colspan="4" class="total-curso"><div align="right"><strong>Total do curso:</strong></div></td><td class="total-curso"><div align="right"><b> '.number_format($totalCurso,'2',',','.').'</b></div></td></tr>';
									$subtotal1 = $totalCurso;
								}
								$desconto_turma = Qlib::get_matriculameta($dados["id"],'desconto');
                                if((isset($dados['desconto']) && $dados['desconto'] >0) || (isset($dados['entrada']) && $dados['entrada'] >0) || (isset($dados['desconto_porcento']) && $dados['desconto_porcento']>0) || $desconto_turma){
									// $totalCurso = $ret['total'];
									if(!$footer){
										$footer = '
										<tr>
											<td colspan="3"><div align="right"> Subtotal</div></td>
											<td><div align="center"><b>'.$totalHoras.'</b></div></td>
											<td><div align="right"><b>'.number_format($totalCurso,'2',',','.').'</b></div></td>
										</tr>';
									}
									if(isset($dados['desconto'])&&$dados['desconto']>0){

										$dados['desconto'] = (double)$dados['desconto'];
										$totalCurso = ($totalCurso) - $dados['desconto'];
										//$totalOrcamento = ($totalCurso) - ($dados['desconto']);
										$totalOrcamento = ($totalCurso);
										$ret['desconto'] = $dados['desconto'];
										$descontoFooter .= '
										<tr class="vermelho">
											<td colspan="4">
												<div align="right"><strong>DESCONTO ESPECIAL</strong></div>
											</td>
											<td>
												<div align="right"><b> '.number_format($dados['desconto'],'2',',','.').'</b></div>
											</td>
										</tr>';
									}
									if(isset($dados['desconto_porcento'])&& $dados['desconto_porcento']>0){
                                        $valor_descPor = ((double)$dados['desconto_porcento']*$subtotal1)/100;
										$valRoud = (round($valor_descPor,2));
										$totalCurso = ($totalCurso) - $valRoud;
										$ret['desconto'] += $valRoud;
										$totalOrcamento = $totalCurso;
										$descontoFooter .= '
										<tr class="vermelho">
											<td colspan="4">
												<div align="right"><strong>DESCONTO ('.$dados['desconto_porcento'].'%) </strong></div>
											</td>
											<td>
												<div align="right"><b> '.number_format($valor_descPor,'2',',','.').'</b></div>
											</td>
										</tr>';
									}
                                    // dump($dados);
									if($desconto_turma && $desconto_turma>0){
                                        $id_matricula = isset($dados['id']) ? $dados['id'] : null;
                                        $tipo = 'v';
                                        $nome_desconto = 'DESCONTO ESPECIAL';
                                        if($id_matricula){
                                            $d_desconto = Qlib::get_matriculameta($id_matricula,'d_desconto');
                                            if($d_desconto){
                                                $arr_desconto = Qlib::decodeArray($d_desconto);
                                                $tipo = isset($arr_desconto['tipo']) ? $arr_desconto['tipo'] : $tipo;
                                                if($tipo == 'v'){
                                                    $nome_desconto = @$arr_desconto['nome'];
                                                }
                                            }
                                        }
                                        if($tipo=='v'){
                                            $valor_descPor = $desconto_turma;
                                        }else{
                                            $valor_descPor = ((double)$desconto_turma*$totalCurso)/100;
                                            $nome_desconto .= ' ('.$desconto_turma.'%)';
                                        }
                                        $valRoud = (round($valor_descPor,2));
                                        $totalCurso = ($totalCurso) - $valRoud;
										$ret['desconto'] += $valRoud;
										$totalOrcamento = $totalCurso;
										$descontoFooter .= '
										<tr class="vermelho">
											<td colspan="4">
												<div align="right"><strong>'.$nome_desconto.'</strong></div>
											</td>
											<td>
												<div align="right"><b> '.number_format($valor_descPor,'2',',','.').'</b></div>
											</td>
										</tr>';
									}
									if(isset($dados['entrada'])&&$dados['entrada']>0){
										$dados['entrada'] = (double)$dados['entrada'];
										$totalCurso = ($totalCurso) - $dados['entrada'];
										$totalOrcamento = ($totalCurso);
										$descontoFooter .= '<tr><td colspan="4"><div align="right">Entrada</div></td><td><div align="right"> - '.number_format($dados['entrada'],'2',',','.').'</div></td></tr>';
									}
									$descontoFooter .= '<tr class="verde"><td colspan="4" class="total-curso"><div align="right"><strong>Total do curso:</strong></div></td><td class="total-curso"><div align="right"><b> '.number_format($totalCurso,'2',',','.').'</b></div></td></tr>';
									$subtotal1 = $totalCurso;
								}
								$ret['subtotal'] = $subtotal1;
								/*Fim desconto*/
								$taxasHtml = false;
								$taxasValor = 0;
								$subtotal2 = $subtotal1;
                                if($dados['status']==1){
									$subtotal2 = $subtotal1+$dados['inscricao_curso'];
									$totalOrcamento = $subtotal2;
									$labelSub = 'Curso + Matrícula';
									$tr2 .= '
										<tr id="matri">
											<td style="width:'.$arr_wid2[0].'"><div align="center">'.$i2.'</div></td>
											<td style="width:85%"><div align="left"> Matrícula</div></td>
											<td style="width:'.$arr_wid2[3].'"><div align="right"> '.number_format($dados['inscricao_curso'],'2',',','.').'</div></td>
										</tr>';
									$tr2 .= '
										<tr id="matri">
											<td style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></td>
											<td style="width:85%"><div align="right"> <b>'.$labelSub.'</b></div></td>
											<td style="width:'.$arr_wid2[3].'"><div align="right"> <b>'.number_format($subtotal2,'2',',','.').'</b></div></td>
										</tr>';
									$tr2_adm .= '
									<tr id="matri">
										<td style="width:'.$arr_wid2[0].'"><div align="center">'.$i2.'</div></td>
										<td style="width:85%"><div align="left"> Matrícula</div></td>
										<td style="width:'.$arr_wid2[3].'"><div align="right"> '.number_format($dados['inscricao_curso'],'2',',','.').'</div></td>
									</tr>';
									$tr2_adm .= '
									<tr id="matri">
										<th style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></th>
										<th style="width:85%"><div align="right"> '.$labelSub.'</div></th>
										<th style="width:'.$arr_wid2[3].'"><div align="right">'.number_format($subtotal2,'2',',','.').'</div></th>
									</tr>';
								}
								$taxasHtml = false;
								$taxasValor = 0;
								$combustivelHtml = false;
								$mens_taxa = '<br><span>*valor de taxas não incluso</span>';
                                if(!empty($dados['taxas'])){
									if(is_array($dados['taxas'])){
										$arr_taxas = $dados['taxas'];
									}else{
										$arr_taxas = json_decode($dados['taxas'],true);
									}
									if(is_array($arr_taxas)){
										$label = false;
										$i2++;
										foreach($arr_taxas As $ket=>$valt){
											$valt = Qlib::precoDbdase($valt);
											$taxasValor += (double)$valt;
											if($ket =='checador'){
												$label = 'Taxas de Checador';
											}elseif($ket =='anac'){
												$label = 'Taxas ANAC';
											}elseif($ket =='envio_de_processo'){
												$label = 'Taxa de Envio de Processo ';
											}elseif($ket =='noturno'){
												$label = 'Taxas de Noturno';
											}else{
												$label = $ket;
											}
											if($valt >0){
                                                $taxasHtml .=
                                                '<tr id="matri">
													<td style="width:'.$arr_wid2[0].'"><div align="center">'.$i2.'</div></td>
													<td style="width:85%"><div align="left">'.$label.'</div></td>
													<td style="width:'.$arr_wid2[3].'"><div align="right"> '.number_format($valt,'2',',','.').'</div></td>
												</tr>';
												$i2++;
											}
										}
									}
								}
								if(!empty($dados['orc']) && ($arr_tx2=Qlib::lib_json_array($dados['orc']))){
									if(isset($arr_tx2['taxas2'])&&is_array($arr_tx2['taxas2'])){
										$i2++;
										foreach ($arr_tx2['taxas2'] as $kt => $vt) {
											$valt = Qlib::precoDbdase($vt['name_valor']);
											$taxasValor += (double)$valt;
											$label = isset($vt['name_label'])?$vt['name_label']:'N/I';
											if($valt){
												$v_exibe = number_format($valt,'2',',','.');
											}else{
												$v_exibe = '0,00';
											}
											$taxasHtml .=
                                            '<tr id="matri">
												<td style="width:'.$arr_wid2[0].'"><div align="center">'.$i2.'</div></td>
												<td style="width:85%"><div align="left">'.$label.'</div></td>
												<td style="width:'.$arr_wid2[3].'"><div align="right"> '.$v_exibe.'</div></td>
											</tr>';
											$i2++;
										}
									}
								}
								if($taxasHtml){
									$mens_taxa = false;
								}
								//$taxasHtml = $taxasHtml ? $taxasHtml : '<span>*valor de taxas não incluso</span>';
								$tr2 .=		$taxasHtml;
								$tr3_adm .= $taxasHtml;
							}
							/*
							if($dados['status']==1){
								$totalOrcamento = ($ret['total']) + ($dados['inscricao_curso']);
							}else{
								$totalOrcamento = ($ret['total']);
							}*/
							///Incluir matricula
							$incluir_matricula_parcelamento = Qlib::qoption('incluir_matricula_parcelamento')?Qlib::qoption('incluir_matricula_parcelamento'):'n';
							if($incluir_matricula_parcelamento=='s')
								$ret['total'] = ($ret['total']) + ($dados['inscricao_curso']);
							$totalCurso = isset($totalCurso) ? $totalCurso:$ret['total'];
							//fim incluir matricula
							if($taxasValor>0){
								if(Qlib::qoption('somar_taxas_orcamento')=='s'){
									if($tipo_curso==2){
										$totalOrcamento += $taxasValor;
									}
									$subtotal2 += $taxasValor;
								}
								$taxasValorMatri = ($taxasValor);
								/*if(isset($dados['inscricao_curso'])){
									$taxasValorMatri = ($taxasValor)+($dados['inscricao_curso']);
								}*/
									$tr3 .= '
									<tr id="matri" class="total">
										<td style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></td>
										<td style="width:85%"><div align="right"> <strong style="color:#F00;">Total de TAXAS não inclusas no Orçamento</strong></div></td>
										<td style="width:'.$arr_wid2[3].'"><div align="right" style="color:#F00;"> <b>'.number_format($taxasValorMatri,'2',',','.').'</b></div></td>
									</tr>';
									$lbCurm = 'Curso + Matrícula';
									if(Qlib::qoption('somar_taxas_orcamento')=='s'){
										$lbCurm .= ' + Taxas';
									}
									$tr3 .= '
									<tr id="matri" class="total">
										<td style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></td>
										<td style="width:85%"><div align="right"> <b>'.$lbCurm.'</b></div></td>
										<td style="width:'.$arr_wid2[3].'"><div align="right"> <b>'.number_format($subtotal2,'2',',','.').'</b></div></td>
									</tr>';
								$laber_taxas = 'Total de taxas Não inclusas no orçamento';
								if(Qlib::qoption('somar_taxas_orcamento')=='s'){
									$laber_taxas = 'Total de taxas (A vista)';
								}
								$tr3_adm .='<tr class="vermelho">
												<td colspan="2" style="width:100%"><div align="right"><strong>'.$laber_taxas.':</strong></div></td>
												<td colspan="" style="width:100%"><div align="right"><b>'.number_format($taxasValorMatri,'2',',','.').'</b></div></td>
											</tr>';
								$tr3_adm .='<tr id="">
												<td colspan="2" style="width:100%"><div align="right"><strong>'.$lbCurm.'</strong></div></td>
												<td colspan="" style="width:100%"><div align="right"><b>'.number_format($subtotal2,'2',',','.').'</b></div></td>
											</tr>';
								$ret['total_taxas'] = @$taxasValorMatri;
							}
							//Combustivel
							$totalOrcamento = isset($totalOrcamento)?$totalOrcamento:$ret['total'];
							// $ret['precoCurso'] = gerenciarPromocao($totalOrcamento,$dados['id_curso']);
							$ret['precoCurso'] = $totalOrcamento;
							$sc = $this->simuladorCombustivel($dados['token'],$dados);
							if($sc['valor']){
								$dados['combustivel'] = number_format($sc['valor'],2,',','.');
								$dados['valor_litro'] = number_format($sc['valor_litro'],2,',','.');
								$somar_cobustivel_orcamento = Qlib::qoption('somar_cobustivel_orcamento')?Qlib::qoption('somar_cobustivel_orcamento'):'s';
								if($somar_cobustivel_orcamento=='s'){
									$somar_cobustivel_total = Qlib::qoption('somar_cobustivel_total')?Qlib::qoption('somar_cobustivel_total'):'n';
									if(isset($dadosOrc['sele_pag_combustivel'])&&$dadosOrc['sele_pag_combustivel']=='antecipado'){
										$somar_cobustivel_total = 's';
									}
									if($somar_cobustivel_total=='s') {
										$totalOrcamento = $totalOrcamento + $sc['valor'];
									}
									$lbCurm = 'Gasto estimado de combustível:';
									if($sc['valor_litro']){
										//$lbCurm .= ' <small style="font-weight:500">Litro - R$ '.$sc['valor_litro'].'</small> Total:';
										$label_sele_valores .= '<br>* '.$sc['valor_litro'].' Preço por litro ';
										// if(isAdmin(1));
										// dd($label_sele_valores);
									}
									if($somar_cobustivel_total == 's'){
										$tr3 .= '
										<tr id="matri" class="total">
											<td style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></td>
											<td style="width:85%"><div align="right"> <b>'.$lbCurm.'</b></div></td>
											<td style="width:'.$arr_wid2[3].'"><div align="right"> <b>'.$dados['combustivel'].'</b></div></td>
										</tr>';
										$tr3_adm .='
											<tr id="">
												<td colspan="2" style="width:100%"><div align="right"><strong>'.$lbCurm.'</strong></div></td>
												<td colspan="" style="width:100%"><div align="right"><b>'.$dados['combustivel'].'</b><input type="hidden" value="'.Qlib::precoDbdase($sc['valor']).'" name="combustivel" /></div></td>
											</tr>';
									}
								}
							}
							$linkComprar = Qlib::qoption('dominio').'/area-do-aluno/meus-pedidos/p/'.$dados['id'];
							if($dados['id_turma']>0){
								$linkComprar .= '/'.base64_encode($dados['id_turma']);
							}
							//<!--<a href="'.$linkComprar.'" target="_BLANK" style="padding:5px;">Comprar</a>-->
							$tr3 .= '
								<tr id="matri" class="total verde">
									<td style="width:'.$arr_wid2[0].'"><div align="center">&nbsp;</div></td>
									<td style="width:85%"><div align="right"> <strong class="color-price1">TOTAL DA PROPOSTA A VISTA:</strong></div></td>
									<td style="width:'.$arr_wid2[3].'"><div align="right"> <b>'.number_format($totalOrcamento,'2',',','.').'</b></div></td>
								</tr>
							';
							$tr3_adm .= '<td colspan="2" width="85%"><div align="center"><strong class="verde">TOTAL DA PROPOSTA A VISTA:</strong></div></td><td><div align="right"> <span class="verde"><b>'.number_format($totalOrcamento,'2',',','.').'</b></span></div></td>';
							if(@$dados['combustivel']){
							    $valor_combustivel = Qlib::precoDbdase($dados['combustivel']);
								$info_cobustivel = (new SiteController)->short_code('info_cobustivel',false,@$_GET['edit']);
								$temaComb = '
								<div align="center" style="">
									'.$info_cobustivel.'
								</div>
								';
								$label = __('Combustível estimado gasto em TODO curso prático. (O pagamento do combustível será realizado a cada voo. O valor pode variar de acordo com o preço do combustível.)');
								$combustivelHtml = str_replace('{item}',$i2,$temaComb);
								$combustivelHtml = str_replace('{label}',$label,$combustivelHtml);
								$combustivelHtml = str_replace('{width0}',$arr_wid2[0],$combustivelHtml);
								$combustivelHtml = str_replace('{width1}',$arr_wid2[1],$combustivelHtml);
								$combustivelHtml = str_replace('{width3}',$arr_wid2[3],$combustivelHtml);
								$combustivelHtml = str_replace('{valor_combustivel}','<span style="color:#F00;">'.$dados['combustivel'].'</span>',$combustivelHtml);
								$combustivelHtml = str_replace('{valor_litro}',@$dados['valor_litro'],$combustivelHtml);
								//$totalOrcamento = $subtotal2 + $valor_combustivel;
								//$tr3 .= 	$combustivelHtml;
								//$tr2 .= 	$combustivelHtml;
								//$tr3_adm .= $combustivelHtml;
								$mensComb = $combustivelHtml;
							}else{
								$totalOrcamento = $subtotal2;
							}
							$ret['totalOrcamento'] = $totalOrcamento;
							$incluir_taxas_parcelamento = Qlib::qoption('incluir_taxas_parcelamento')?Qlib::qoption('incluir_taxas_parcelamento'):'n';
							if($incluir_taxas_parcelamento=='s'){
								$ret['totalCurso'] = $subtotal2;
							}else{
								$ret['totalCurso'] = $totalCurso;
							}
							// if(isAdmin(1)){
							// 	echo $totalCurso.'<br>';
							// 	echo $incluir_matricula_parcelamento.'<br>';
							// 	dd($ret);
							// }
							$ret['table'] = str_replace('{{table}}',$tr,$tema);
							/*$footer = '
							<tr>
								<td colspan="3"><div align="right">Subtotal</div></td>
								<td><div align="center">'.$totalHoras.'</div></td>
								<td><div align="right"> '.number_format($subtotal1,'2',',','.').'</div></td>
							</tr>';*/
							$footer = isset($footer)?$footer:'
							<tr>
								<td colspan="3"><div align="right">Subtotal</div></td>
								<td><div align="center"><b>'.$totalHoras.'</b></div></td>
								<td><div align="right"> <b>'.number_format($subtotal1,'2',',','.').'</b></div></td>
							</tr>';
							$footer .= $descontoFooter;
							$ret['table'] = str_replace('{{footer}}',$footer,$ret['table']);
							$ret['table'] = str_replace('{{table2}}',$tr2,$ret['table']);
							$ret['table'] = str_replace('{{table3}}',$tr3,$ret['table']);
							$ret['table_adm'] = str_replace('{{table}}',$tr_adm,$tema_admn);
							$ret['table_adm'] = str_replace('{{footer}}',$footer,$ret['table_adm']);
							$ret['table_adm'] = str_replace('{{table2}}',$tr2_adm,$ret['table_adm']);
							$ret['table_adm'] = str_replace('{{table3}}',$tr3_adm,$ret['table_adm']);
							$url_prop = Qlib::qoption('dominio_site').'/area-do-aluno/meus-pedidos/p/'.$dados['id'];
							// $link_proposta = queta_formfield4('input-group-text', 12, 'link_proposta-', $url_prop, '', @$val['event'], @$val['clrw'], @$val['obs'], 'Link da proposta', '','','sm');
							$link_proposta = $url_prop;
							$ret['table_adm'] = str_replace('{link_proposta}',$link_proposta,$ret['table_adm']);
							$ret['table'] .= $mensComb.$mens_taxa;
							$ret['table_adm'] .= $mensComb.$mens_taxa;
						}
					}else{
						$ret['table'] = Qlib::formatMensagem0('Erro: Cliente ou curso não encontrado(s)!!','danger',10000);
					}
				}elseif($dados['tipo_curso']==1 || $dados['tipo_curso']==3 || $dados['tipo_curso']==4){
					$arr_wid2 = array('5%','80%','15%');
					if(isset($dados['Nome']) && isset($dados['nome_curso'])){
						$ret['id_curso'] = $dados['id_curso'];
						$dadosD = explode(' ',$dados['atualizado']);
						$valdata = explode('-',$dadosD[0]);
                              $valor_curso = $dados['valor_curso'];
						if($is_signed){
							$valor_curso = isset($dados['subtotal']) ? $dados['subtotal'] : @$dados['valor_curso'];
						}
						$totalOrcamento = $valor_curso;
						$espacoTable = false;
						$ret['nome_arquivo'] = 'Proposta '.$dados['id']. ' '.$dados['Nome'].' '.$dados['nome_curso'];
						//$validade = ultimoDiaMes($valdata[1],$valdata[0]).'/'.$valdata[1].'/'.$valdata[0];
						$dias = isset($dias)?$dias: 7;
						$validade = Qlib::CalcularVencimento(Qlib::dataExibe($dadosD[0]),$dias);
						$ret['validade'] = $validade;
						$ret['total'] = $totalOrcamento;
						$tema = '
							<p class="apresentacao" style="font-family:helvetica;font-size:13pt;">Prezado(a) <strong>'.$dados['Nome'].'</strong>,<br>
							Temos o prazer em lhe apresentar nossa proposta comercial<br>Curso: <strong>'.$dados['titulo_curso'].'</strong></p>
							<br>
							<table id="table4" cellspacing="0" class="table">
								<thead >
									<tr>
										<th style="width:'.$arr_wid2[0].'"><div align="left">ITEM</div></th>
										<th style="width:'.$arr_wid2[1].'"><div align="center">DESCRIÇÃO</div></th>
										<th style="width:'.$arr_wid2[2].'"><div align="right">TOTAL</div></th>
									</tr>
								</thead>
								<tbody class="jss526">{{table2}}
								</tbody>
								<tfoot class="jss526">{{footer}}
								</tfoot>
							</table>'.$espacoTable.'
							';
						$tema2 = '
							<tr>
								<td style="width:'.$arr_wid2[0].'"><div align="left">{num}</div></td>
								<td style="width:'.$arr_wid2[1].'"><div align="center">{descricao}</div></td>
								<td style="width:'.$arr_wid2[2].'"><div align="right">{valor}</div></td>
							</tr>
						';
						$ret['nome_arquivo'] = 'Proposta '.$dados['id']. ' '.$dados['Nome'].' '.$dados['nome_curso'];
						//$validade = ultimoDiaMes($valdata[1],$valdata[0]).'/'.$valdata[1].'/'.$valdata[0];
						$dadosD = explode(' ',$dados['atualizado']);
                        $dias = isset($dias)?$dias: 7;
                        $validade = Qlib::CalcularVencimento(Qlib::dataExibe($dadosD[0]),$dias);
                        $dadosCli = $this->tag_apresentacao_orcamento($dados);
                        if($this->is_pdf()){
                            $dadosCli .= $btn_aceito_aceitar;
                        }
						$ret['dadosCli'] =  $dadosCli;
						$i=1;
						$tr = str_replace('{num}',$i,$tema2);
						$tr = str_replace('{descricao}','Curso '.$dados['titulo_curso'],$tr);
						$tr = str_replace('{valor}',number_format($dados['valor_curso'],2,',','.'),$tr);
						$i++;
						$ret['totalCurso'] = $dados['valor_curso'];
						if(isset($dados['inscricao_curso'])&&$dados['inscricao_curso']>0){
							$tr .= str_replace('{num}',$i,$tema2);
							$tr = str_replace('{descricao}','Matrícula '.$dados['titulo_curso'],$tr);
							$tr = str_replace('{valor}',number_format($dados['inscricao_curso'],2,',','.'),$tr);
							$totalOrcamento +=  $dados['inscricao_curso'];
						}
						if(isset($dados['desconto']) && $dados['desconto']>0){
							$desconto = number_format($dados['desconto'],2,',','.');
							$tr .= str_replace('{num}',$i,$tema2);
							$tr = str_replace('{descricao}','Desconto ',$tr);
							$tr = str_replace('{valor}','<span style="color:#F00">- '.$desconto.'</span>',$tr);
							$espacoTable = false;
							$totalOrcamento = $dados['total'] ? $dados['total']:$dados['valor_curso'];
						}else{
							//$espacoTable = '<p></p>';
							$espacoTable = false;
						}
						if($dados['tipo_curso']==4){
                            if($dados['valor_parcela_curso']==0){
                                $totalOrcamento = '1X R$ '.number_format($dados['inscricao_curso'],2,',','.').' + 1 X R$ '.number_format($dados['valor'],2,',','.');
                            }else{
                                $totalOrcamento = '1X R$ '.number_format($dados['inscricao_curso'],2,',','.').' + '.$dados['parcelas_curso'].'X'.number_format($dados['valor_parcela_curso'],2,',','.');
                            }
							$ret['totalOrcamento'] = $totalOrcamento;
							$totGeral = $totalOrcamento;
						}else{
							$ret['totalOrcamento'] = $totalOrcamento;
							$totGeral = 'R$'.number_format($totalOrcamento,'2',',','.');
						}
                        $valorParcelado = false;
						if(isset($dados['parcelas_curso'])&&$dados['parcelas_curso']>0){
							if($dados['tipo_curso']==4){
								$valorParcelado = '';
							}else{
								// $valorParcelado = round($ret['totalOrcamento']/$dados['parcelas_curso'],2);
								// $valorParcelado = ' ou '.$dados['parcelas_curso'].' X '.number_format($valorParcelado,2,',','.').' no cartão';
								$valorParcelado = '';
							}
						}
						$tema = '<style>
                                       .color-price1{
                                           color: #062d4a !important;
                                       }
                                   </style>'.$tema;
						$ret['table'] = str_replace('{{table}}',$tr,$tema);
						$ret['table2'] = str_replace('{{table}}',$tr,$tema);
						$listMod['html'] = false;
						if($dados['tipo_curso']==4){
							$dadosCu[0]['token_matricula'] = isset($dados['token'])?$dados['token']:false;
							$listMod = $this->get_modulos_cursos($dados);
							if(isset($listMod['total_taxas'])){
								$ret['totalOrcamento'] = (double)$ret['totalOrcamento']+(double)$listMod['total_taxas'];
								$ret['listMod'] = $listMod;
							}
							$tabela_parcelamento = $ret['totalOrcamento'];
							if(isset($dados['token'])){
								$resumo = Qlib::infoPagCurso([
									'token'=>$dados['token'],
								]);
                                if(isset($resumo['tabela_parcelamento']) && !empty($resumo['tabela_parcelamento'])){
									$tabela_parcelamento =  $resumo['tabela_parcelamento'];
									// $ret['table'] = $listMod['html'].$resumo['tabela_parcelamento'];
									if($this->is_pdf()){
										$ret['table'] = $listMod['html'].'<br>';
									}else{
										$ret['table'] = $listMod['html'].'<div class="col-12 obs-plano">'.@$resumo['tabela_parcelamento_cliente'].'</div>';
									}
								}else{
									//se não tiver tabela de parcelamento na exime orçamento
									$ret['table'] = false;
								}
							}
							$footer = '<tr><td colspan="3">'.$tabela_parcelamento.'</td></tr>';
						}else{
							$footer = '<tr><td colspan="1"><div align="right"><b>Total</b></div></td><td colspan="2"><div align="right"><b>'.$totGeral.' '.$valorParcelado.'</b></div></td></tr>';
						}
						$ret['table'] = str_replace('{{footer}}',$footer,$ret['table']);
						$ret['table'] = str_replace('{{table2}}',$tr,$ret['table']);
						$ret['table2'] = str_replace('{{footer}}',$footer,$ret['table2']);
						$ret['table2'] = str_replace('{{table2}}',$tr,$ret['table2']);
						//$ret['table'] = str_replace('{{table3}}',$tr3,$ret['table']);
					}
				}
            }else{
                $ret['table'] = Qlib::formatMensagem0('Erro: Orçamento não encontrado!!','danger',10000);
			}
            //Adcionar as tabelas de parcelamentos
            $ret['parcelamento'] = '';
            if(isset($ret['totalCurso']) && $ret['totalCurso']> 0 && ($exibir_parcelamento=='s')){
                $configPar['valor'] 	= $ret['totalCurso'];
                $configPar['titulo'] 	= 'PAGAMENTO PARCELADO';
                $configPar['tam'] 		= 6;
                $configPar['id_curso'] 	= isset($dados['id_curso']) ? $dados['id_curso'] : null;
                $configPar['id_turma'] 	= isset($dados['id_turma']) ? $dados['id_turma'] : null;
                $configPar['token_matricula'] 	= $dados['token'];
                if(isset($dados['sele_valores'])){
                    $configPar['tabela_preco'] 	= $dados['sele_valores'];
                }
                $parcelamentoT = new FinanceiroController;
                $parcelamento = '<div class="col-sm-12 padding-none planos-parcelamentos">'.$parcelamentoT->execute($configPar).'</div>';
                $ret['parcelamento'] = $parcelamento;
            }

			$ret['dados'] = @$dados;
			// $dados = (new CursosController)->dadosMatricula(@$dados['token']);
			// if($dados){
			// 	$ret['dados_gravados'] = @$dados;
			// }
            return $ret;
	    }
    }
    public function get_modulos_cursos($config=false,$orc=false){
        $ret['exec'] = false;
        $ret['html'] = false;
        $ret['total_taxas'] = 0;
        $id_curso = isset($config['id']) ? $config['id'] : false;
        $token_matricula = isset($config['token']) ? $config['token'] : false; //html ou pdf
        $orc = isset($config['orc']) ? $config['orc'] : false; //html ou pdf
        $config['modulos'] = isset($config['modulos_curso']) ? $config['modulos_curso'] : false; //html ou pdf
        $is_pdf = $this->is_pdf();
        $arr_orc=[];
        // dd($orc);
        if($token_matricula && !$orc){
            if(!$orc){
                $d_or =  $this->dm($token_matricula);//dados od orçamento
            }
            $orc = isset($d_or['orc'])?$d_or['orc']: false;
            if($orc){
                $arr_orc = Qlib::lib_json_array($orc);
            }
        }else{
            $arr_orc = $orc;
        }
        if(!isset($config['modulos']) && $id_curso){
            // $dm = Qlib::dados_tab($GLOBALS['tab10'],'*',"WHERE id = '$id_curso'");
            $dm = Qlib::dados_tab($GLOBALS['tab10'],[
                'id'=>$id_curso
            ]);
            if($dm){
                $config['modulos'] = $dm[0]['modulos'];
                $config['config'] = $dm[0]['config'];
            }
        }
        $client = false;
        if(isset($config['modulos']) && $config['modulos']!='[]'){
            if(is_array($config['modulos'])){
                $arr_mod = $config['modulos'];
            }else{
                $arr_mod = Qlib::lib_json_array($config['modulos']);
            }
            $arr_mod_save = false;
            if(isset($arr_orc['modulos'])){
                $arr_mod_save = $arr_orc['modulos'];
            }
            if(!Qlib::is_admin_area() && isset($arr_orc['modulos'])){
                $arr_mod = $arr_orc['modulos'];
                $client = true;
            }else{
                if($is_pdf && isset($arr_orc['modulos'])){
                    $arr_mod = $arr_orc['modulos'];
                    $client = true;
                }
            }

            if($is_pdf){
                $tm1 = '
                    <div class=""><h3>Detalhamento</h3></div>
                    <table class="table get_modulos_cursos">
                        <thead>
                        <!--<tr>
                            <th colspan="3" class="text-left"></th>
                        </tr>-->
                        <tr>
                            <th style="width:53%"><div align="left"><b>Descrição</b></div></th>
                            <th style="width:37%"><b>Etapa</b></th>
                            <th style="width:10%"><div align="right"><b>C. Horária</b></div></th>
                        </tr>
                        </thead>
                        <tbody>
                            {tr}
                        </tbody>
                    </table>&nbsp;&nbsp;';
                $tm2 = '
                    <tr>
                        <td style="width:53%">{descricao}</td>
                        <td style="width:37%">{curso}</td>
                        <td style="width:10%"><div align="right">{carga}</div></td>
                    </tr>';
            }else{
                $tm1 = '
                    <table class="table table-striped get_modulos_cursos">
                        <thead>
                        <tr>
                            <th colspan="3" class="text-left"><h3>Detalhamento</h3></th>
                        </tr>
                        <tr>
                            <th style="width:45%"><div align="left"><b>Descrição</b></div></th>
                            <th style="width:30%"><b>Etapa</b></th>
                            <th style="width:15%"><div align="right"><b>Carga Horária</b></div></th>
                        </tr>
                        </thead>
                        <tbody>
                            {tr}
                        </tbody>
                    </table>&nbsp;&nbsp;';
                $tm2 = '
                    <tr>
                        <td>{descricao}</td>
                        <td>{curso}</td>
                        <td><div align="right">{carga}</div></td>
                    </tr>';
            }

            $tr=false;
            if(is_array($arr_mod)){
                foreach ($arr_mod as $id => $v) {
                    if($client){
                        $siga = isset($v['sele'])?$v['sele']:false;
                    }else{
                        $siga = true;
                        if($is_pdf){
                            $siga = isset($v['sele'])?$v['sele']:false;
                        }
                        // dump($is_pdf);
                    }
                    if((isset($v['curso_id']) || isset($v['curso'])) && $v['titulo']!='' && $siga){
                        // if(isAdmin(1)){
                        $tipo = $v['tipo'];
                        if($client){
                            $curso = @$v['curso'];
                        }else{
                            $curso = Qlib::buscaValorDb0($GLOBALS['tab10'],'id',@$v['curso_id'],'titulo');
                        }
                        $titulo = $v['titulo'];
                        if(Qlib::isAdmin(6) && Qlib::is_admin_area() && !$is_pdf){
                            if($arr_mod_save){
                                if(isset($arr_mod_save[$id]['sele'])){
                                    $checkbox = 'checked';
                                    $disabled_titulo = false;
                                    $disabled_limite = false;
                                    $disabled_curso = false;
                                    $disabled_tipo = false;
                                }else{
                                    $checkbox = '';
                                    $disabled_titulo = 'disabled';
                                    $disabled_limite = 'disabled';
                                    $disabled_curso = 'disabled';
                                    $disabled_tipo = 'disabled';

                                }
                            }else{
                                $checkbox = 'checked';
                                $disabled_titulo = false;
                                $disabled_limite = false;
                                $disabled_curso = false;
                                $disabled_tipo = false;
                            }
                            $titulo = '<div class="col-sm-1">
                                            <input  onclick="orcamentos_selectModuloPlano(this);" type="checkbox" '.$checkbox.' data-id="'.$id.'" name="dados[orc][modulos]['.$id.'][sele]"/>
                                        </div>
                                        <div class="col-sm-11">
                                            <input type="hidden" '.$disabled_titulo.' name="dados[orc][modulos]['.$id.'][titulo]" value="'.@$v['titulo'].'">
                                            <input type="hidden" '.$disabled_limite.' name="dados[orc][modulos]['.$id.'][limite]" value="'.@$v['limite'].'">
                                            <input type="hidden" '.$disabled_curso.' name="dados[orc][modulos]['.$id.'][curso]" value="'.$curso.'">&nbsp;'.$v['titulo'].'
                                            <input type="hidden" '.$disabled_tipo.' name="dados[orc][modulos]['.$id.'][tipo]" value="'.$tipo.'">&nbsp;'.$v['titulo'].'
                                        </div>';
                        }else{
                            //lib_print($v);
                        }
                        $tr .= str_replace('{descricao}',$titulo,$tm2);
                        $tr = str_replace('{carga}',$v['limite'],$tr);
                        $tr = str_replace('{curso}',$curso,$tr);
                        $tr = str_replace('{tipo}',$tipo,$tr);
                    }
                }
            }
            $dtx = $this->get_taxas_curso($config);
            $ret['html'] = str_replace('{tr}',$tr,$tm1);
            if($this->is_pdf()){
                $ret['html'] .= '<br>'.$dtx['html'];
            }else{
                $ret['html'] .= $dtx['html'];
            }
            $ret['total_taxas'] = $dtx['total'];
        }
        return $ret;
    }
    /**
     * Metodo para verificar se a requisição é de uma pagina de pdf válida
     */
    public function is_pdf(){
        $route_name = request()->route()->getName();
        if($route_name=='orcamento.pdf'){
            $ret = true;
        }else{
            $ret = false;
        }
        return $ret;
    }
    /**
     * Metodo para exibir um table com as taxas dos cursos especificos*
    */
    function get_taxas_curso($config=false){
        $ret['exec'] = false;
        $ret['html'] = false;
        $ret['total'] = 0;
        $id_curso = isset($config['id']) ? $config['id'] : false;
        $tmsomaTaxa = '
        <tfoot>
            <tr>
                <th colspan=""><b>Total das taxas</b></th>
                <th><div align="right"><b>{total}</b></div></th>
            </tr>
        </tfoot>';
        $tmsomaTaxa = false;
        if($this->is_pdf()){
            $tm1 = '<table class="table" style="">
                        <thead>
                        <tr>
                            <th colspan="2" class="text-left"><h3>Taxas</h3></th>
                        </tr>
                        <!--<tr>
                            <th style="width:90%"><div align="left"><b>Descrição</b></div></th>
                            <th style="width:10%"><div align="right"><b>Valor</b></div></th>
                        </tr>-->
                        </thead>
                        <tbody>
                            {tr}
                        </tbody>
                        '.$tmsomaTaxa.'
                    </table>&nbsp;&nbsp;';
        $tm2 = '<tr>
                    <td style="width:90%">{descricao}</td>
                    <td style="width:10%"><div align="right">{valor}</div></td>
                </tr>';
        }else{
            $tm1 = '<table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="3" class="text-left"><h3>Taxas</h3></th>
                        </tr>
                        <!--<tr>
                            <th style="width:90%"><div align="left"><b>Descrição</b></div></th>
                            <th style="width:10%"><div align="right"><b>Valor</b></div></th>
                        </tr>-->
                        </thead>
                        <tbody>
                            {tr}
                        </tbody>
                        '.$tmsomaTaxa.'
                    </table>&nbsp;&nbsp;';
        $tm2 = '<tr>
                    <td style="width:90%">{descricao}</td>
                    <td style="width:10%"><div align="right">{valor}</div></td>
                </tr>';
        }
        if(!isset($config['config']) && $id_curso){
            $dm = Qlib::dados_tab('cursos',['id' => $id_curso]);
            if($dm){
                $config['config'] = $dm['config'];
            }
        }
        if(is_array($config['config'])){
            $arr_config = $config['config'];
        }else{
            $arr_config = Qlib::lib_json_array($config['config']);
        }
        $total = NULL;
        $tr = false;
        if(isset($arr_config['tx2']) && is_array($arr_config['tx2'])){
            foreach ($arr_config['tx2'] as $k => $v) {
                if(!empty($v['name_label'])){
                    $descricao = $v['name_label'];
                    if($v['name_valor']){
                        $valor = Qlib::precoDbdase($v['name_valor']);

                        $total+=$valor;
                    }else{
                        $valor = 0;
                    }
                    if($valor){
                        $val = number_format($valor,2,',','.');
                    }else{
                        $val = 'Gratuito';
                    }
                    $tr .= str_replace('{descricao}',$descricao,$tm2);
                    $tr = str_replace('{valor}',$val,$tr);
                    // $tr = str_replace('{tipo}',$tipo,$tr);
                }
            }
            $ret['total'] = (double)$total;
        }
        if(!$tr){
            $tm1 = '';
        }
        $ret['html'] = str_replace('{tr}',$tr,$tm1);
        $ret['html'] = str_replace('{total}',number_format($total,'2',',','.'),$ret['html']);
        return $ret;
    }

    public function verificaDataAssinatura($config,$type_return='bool'){
		$campo_bus = isset($config['campo_bus'])?$config['campo_bus'] : 'token';
		$token = isset($config['token'])?$config['token'] : 'token';
		$contrato = isset($config['contrato'])?$config['contrato'] : false;
		if($contrato){
			$dt = $contrato;
		}else{
			$dt = Qlib::buscaValorDb0('matriculas',$campo_bus,$token,'contrato');
		}
		if($dt){
			$arr = Qlib::lib_json_array($dt);
			$dataContrato = isset($arr['data_aceito_contrato']) ? $arr['data_aceito_contrato']:false;
			// if(isAdmin(1)){
			// 	lib_print($arr);
			// 	lib_print($dataContrato);
			// }
		}else{
			$dataContrato = false;
		}
        if($type_return=='array'){
            return $arr;
        }else{
            return $dataContrato;
        }
	}
    public function pacotesAeronaves($id_aviao=false){
        global $tab54;
        $tab54 = 'aeronaves';
        $config = Qlib::buscaValorDb0($tab54,'id',$id_aviao,'pacotes');
        $hora_padrao = Qlib::buscaValorDb0($tab54,'id',$id_aviao,'hora_rescisao');

        $ret = false;

        if($config){
            $arr_con = Qlib::lib_json_array($config);
            // if(isAdmin(1)){
            // 	lib_print($arr_con);
            // }
            if(is_array($arr_con)){

                $fin = new CotacaoDolarController;

                $cota = $fin->cotacaoDolar();

                foreach ($arr_con as $k => $va) {

                    if(isset($va['moeda']) && $va['moeda']=='USD'){

                        if(isset($va['horas_livre_dolar']) && $va['horas_livre_dolar'] && isset($cota['cotacao']['valor']) && $cota['cotacao']['valor']){

                            $vlDolcar = str_replace('U$','',Qlib::precoDbdase($va['horas_livre_dolar']));

                            $vlDolcar = (double)$vlDolcar;

                            $to = $cota['cotacao']['valor']*$vlDolcar;
                            if($to>0){
                                $arr_con[$k]['horas_livre'] = 'R$ '.number_format($to,2,',','.');
                            }

                        }

                    }
                    $arr_con[$k]['hora_padrao'] = $hora_padrao;

                }

                // if(isAdmin(1)){
                // 	lib_print($arr_con);
                // }


            }



            $ret = $arr_con;

        }

        return $ret;

    }

    public function somaHoraAviao($arrPedido=false,$aviao=false){

        //para somar as horas por aviao no pedido

        $ret = false;

        if($arrPedido){

            if(is_array($arrPedido)){

                $horas =  NULL;
                foreach($arrPedido As $key=>$val){
                    $val['aviao'] = isset($val['aviao'])?$val['aviao']:0;
                    if(isset($val['horas']) && ($aviao == $val['aviao'])){
                        $val['horas'] = (int)$val['horas'];
                        // if(is_sandbox()){
                        // 	dd($val['horas']);
                        // }
                        $horas += @$val['horas'];

                    }

                }

                $ret = $horas;

            }

        }

        return $ret;

    }

    public function somaHoraAviao2($arrPedido=false,$aviao=false){

        //para somar as horas por aviao no pedido

        $ret = false;

        if($arrPedido){

            if(is_array($arrPedido)){

                $horas =  NULL;

                foreach($arrPedido As $key=>$val){

                    if(in_array($aviao, $val['aviao'])){

                        $horas += $val['limite'];

                    }

                }

                $ret = $horas;

            }

        }

        return $ret;

    }
    /**
     * Metodo para calcular os preco atraves dos modulos selecionados num orçamento
     */
    public function calcPrecModulos($config=false,$sele_valores=false,$todosModulos=false){
        global $tab50;
        $ret['padrao'] = 0;
        $ret['custo'] = 0;
        $ret['custo'] = 0;

        if(isset($config['aviao'])&&!empty($config['aviao'])){
            $arr_pacotoes = $this->pacotesAeronaves($config['aviao']);
            $arr_tabelas = Qlib::sql_array("SELECT * FROM ".$tab50." WHERE ativo = 's' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'url2','url');
            $id_curso = isset($config['id_curso']) ? $config['id_curso'] : 0;
            if($id_curso){
                //verificar se esse curso é recheck
                $is_recheck = (new CursosController)->is_recheck($id_curso);
            }
            $valor = NULL;
            $padrao = NULL;
            $custo = NULL;
            if(is_array($arr_pacotoes)){
                foreach($arr_pacotoes As $kei=>$val){

                    if($todosModulos){
                        $horas = $this->somaHoraAviao($todosModulos,$config['aviao']);
                    }else{
                        $horas = $config['horas'];
                    }
                    // if($horas > 0 && $horas >= @$val['limite']){ //apartir de >=
                    if($horas > 0 && $horas >= @$val['limite']){ //apartir de >=
                        $valor = @$val[$arr_tabelas[$sele_valores]];
                        $custo += @$val['custo_real'];
                        $padrao = @$val['hora_padrao'];
                        if($is_recheck){
                            //Para os cursos de recheck mamter o valor da tabela com valor padrão
                            $padrao = $valor;

                        }
                    }

                }
            }
            // if(isAdmin(1)){
            // 	lib_print($config);
            // 	dd($arr_pacotoes);
            // }
            if($valor){

                $valor = str_replace('R$','',$valor);
                $valor1 = Qlib::precoDbdase($valor);
                $valor = (double)$valor1;
                $valor = ((int)$config['horas']) * (Qlib::precoDbdase($valor));
                $ret['valor'] = $valor;
            }
            if($custo){
                $custo = str_replace('R$','',$custo);
                $custo = (double)$custo;
                $custo = ((int)$config['horas']) * (Qlib::precoDbdase($custo));
                $ret['custo'] = Qlib::precoDbdase($custo);
            }
            if($padrao){
                $padrao = str_replace('R$','',$padrao);
                $padrao = Qlib::precoDbdase($padrao);
                $padrao = (double)$padrao;
                // if(isAdmin(1)){
                // 	lib_print($padrao);
                // }
                $padrao = ((int)$config['horas']) * (Qlib::precoDbdase($padrao));
                $ret['padrao'] = Qlib::precoDbdase($padrao);
            }
        }else{

            $ret = 'Avião não selecionado';

        }
        return $ret;

    }

    public function calcPrecModulos2($config=false,$ajax='',$sele_valores=false,$todosModulos=false){

        $ret = false;

        if($config){

            if($ajax =='n'){

                $id_av = $config['aviao'][0];

            }else{

                $id_av = $ajax;

            }

            $arr_pacotoes = $this->pacotesAeronaves($id_av);

            $valor = NULL;

            $arr_tabelas = Qlib::sql_array("SELECT * FROM ".$GLOBALS['tab50']." WHERE ativo = 's' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'url2','url');
            if(is_array($arr_pacotoes)){
                foreach($arr_pacotoes As $kei=>$val){

                    if($todosModulos){

                        $horas = somaHoraAviao2($todosModulos,$id_av);

                    }else{

                        $horas = $config['limite'];

                    }

                    if($horas > 0 && $horas >= $val['limite']){ //apartir de >=

                        /*if($sele_valores == 'tabela-hora-padao'){

                            $valor = $val ['horas_c_comb'];

                        }

                        if($sele_valores == 'valores_s_comb'){

                            $valor = $val ['horas_s_comb'];

                        }

                        if($sele_valores == 'valores_fumec'){

                            $valor = isset($val ['horas_fumec']) ? $val ['horas_fumec'] : false;

                        }

                        if($sele_valores == 'valores_livre'){

                            $valor = isset($val ['horas_livre']) ? $val ['horas_livre'] : false;

                        }*/

                        $valor = $val[$arr_tabelas[$sele_valores]];

                    }

                }
            }

            if($valor){

                $valor = str_replace('R$','',$valor);

                //$valor = precoDbdase($valor); //teste

                $valor = ($config['limite']) * (Qlib::precoDbdase($valor));

                $ret = $valor;

            }

        }

        return $ret;

    }

    public function calcPrecModulos3($config=false,$sele_valores=false,$todosModulos=false){

        $ret['valor'] = 0;
        $ret['custo'] = 0;
        if(isset($config['aviao'])&&!empty($config['aviao'])){
            $arr_pacotoes = $this->pacotesAeronaves($config['aviao']);
            $arr_tabelas = Qlib::sql_array("SELECT * FROM ".$GLOBALS['tab50']." WHERE ativo = 's' AND ".Qlib::compleDelete()." ORDER BY nome ASC",'url2','url');

            $valor = NULL;
            $custo = NULL;
            $id_turma = isset($_GET['id_turma']) ? $_GET['id_turma'] : 0;
            $numePrevTurma = (new CursosController)->numePrevTurma(['id_turma'=>$id_turma]);
            dd($numePrevTurma);
            foreach($arr_pacotoes As $kei=>$val){
                if($todosModulos){
                    $horas = somaHoraAviao($todosModulos,$config['aviao']);
                }else{
                    $horas = $config['horas'];
                }
                if(isset($val['turma'])){
                    if($val['turma']==$numePrevTurma){
                        if($horas > 0 && $horas >= @$val['limite']){ //apartir de >=
                            $valor = @$val[$arr_tabelas[$sele_valores]];
                            $custo += @$val['custo_real'];
                        }
                    }
                // }else{
                // 	if(@$val[$arr_tabelas[$sele_valores]]){

                // 		echo @$val[$arr_tabelas[$sele_valores]];
                // 		lib_print($val);
                // 	}
                // 	if($horas > 0 && $horas >= @$val['limite']){ //apartir de >=
                // 		$valor = @$val[$arr_tabelas[$sele_valores]];
                // 		$custo += @$val['custo_real'];
                // 	}
                }

            }

            if($valor){
                $valor = str_replace('R$','',$valor);
                $valor = ($config['horas']) * (precoDbdase($valor));
                $ret['valor'] = $valor;
            }
            if($custo){
                $custo = str_replace('R$','',$custo);
                $custo = ($config['horas']) * (precoDbdase($custo));
                $ret['custo'] = precoDbdase($custo);
            }
        }else{

            $ret = 'Avião não selecionado';

        }
        // if(isAdmin(1)){
        // 	// lib_print($arr_pacotoes);
        // 	lib_print($ret);
        // 	// lib_print($arr_pacotoes);
        // }
        return $ret;

    }
    /**
     * Metodo para gerar uma simução do valor do comustivel no orçamento
     */
    public function simuladorCombustivel($token = null,$dados=false)
	{

		$ret['exec'] = false;
		$ret['valor'] = 0;
		$ret['valor_litro'] = null;
		$ret['tipo_pagamento'] = '';
		$ret['color_tipo_pagamento'] = '';

		if($token){

			if(!$dados){

				$dados = $this->dm($token);

			}
			if(!isset($dados['modulos']) && isset($dados['orc'])){
				$arr_mod = Qlib::lib_json_array($dados['orc']);
				if(isset($arr_mod['modulos'])){
					$dados['modulos'] = $arr_mod['modulos'];
				}
			}

			if(isset($dados['modulos']) && is_array($dados['modulos'])){

				$arr_mod = $dados['modulos'];

				$previsao_consumo = NULL;
				$preco_litro = null;
				foreach ($arr_mod as $k => $v) {
					$v['aviao'] = isset($v['aviao'])?$v['aviao']:0;
					$dAviao = Qlib::buscaValorDb0($GLOBALS['tab54'],'id',$v['aviao'],'config');

					if($dAviao){

						$arr_dAv = Qlib::lib_json_array($dAviao);

						if(isset($arr_dAv['combustivel']['consumo_hora']) && isset($arr_dAv['combustivel']['preco_litro']) && isset($arr_dAv['combustivel']['ativar']) && $arr_dAv['combustivel']['ativar']=='s'){

							$p_litro = Qlib::qoption('preco_litro')?Qlib::qoption('preco_litro'): $arr_dAv['combustivel']['preco_litro'];
							$preco_litro = Qlib::precoDbdase($p_litro);
							$consumo = ((int)$arr_dAv['combustivel']['consumo_hora'] * (int)$v['horas']); //
							$previsao_consumo += ($preco_litro * $consumo);
						}

					}

				}

				if($previsao_consumo){

					$ret['valor'] = $previsao_consumo;
					$ret['valor_litro'] = $preco_litro;
					$ret['tipo_pagamento'] = $this->pagamento_combustivel($token,@$dados['orc']);
					$ret['exec'] = true;
					if($ret['tipo_pagamento']=='antecipado'){
						$ret['color_tipo_pagamento'] = 'text-success';
					}else{
						$ret['color_tipo_pagamento'] = 'text-danger';
					}
					// if(isAdmin(1)){
					// 	dd($ret);
					// }
				}



			}

		}
		return $ret;
	}
    /**
	 * Retora o numero de horas de um orçamento
	 * @param string $id_matricula
	 * @return integer $ret
	 */
	public function horas_orcamento($id_matricula){
		$ret = 0;
		if($id_matricula){
			$json_orc = buscaValorDb($GLOBALS['tab12'],'id',$id_matricula,'orc');
			if($json_orc){
				$arr = lib_json_array($json_orc);
				//veriricar o tipo de curso se for plando de formação vai buscar na tabela de eventos as horas relacionadas
				if(isset($arr['modulos'])){
					foreach ($arr['modulos'] as $k => $v) {
					   $ret += (int)@$v['horas'];
					}
				}
			}
		}
		return $ret;
	}
	/**
	 * Metodo para veridicar a forma de pagamento de comustivel escolhido
	 * uso $ret = (new Orcamentos)->pagamento_combustivel($token,$org);
	 */
	public function pagamento_combustivel($token,$orc=false){
		$ret = false;
		if(!$orc && $token){
            $dm = $this->dm($token);
            if(isset($dm['orc'])){
                $orc = $dm['orc'];
            }
		}
		if($orc){
			$arr_orc = Qlib::lib_json_array($orc);
			if(isset($arr_orc['sele_pag_combustivel'])){
				$ret = $arr_orc['sele_pag_combustivel'];
			}
		}
		return $ret;
	}
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $d = DB::table($this->table)->find($id);

        if(is_null($d)){
            $ret['exec'] = false;
            $ret['status'] = 404;
            $ret['data'] = [];
            return response()->json($ret);
        }else{
            $ret['exec'] = true;
            $ret['status'] = 200;
            $ret['data'] = $d;
            return response()->json($ret);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $d = $request->all();
        $ret['exec'] = false;
        $ret['status'] = 500;
        $ret['message'] = 'Error updating';
        if($d){
            $ret['exec'] = DB::table($this->table)->where('id',$id)->update($d);
            if($ret['exec']){
                $ret['status'] = 200;
                $ret['message'] = 'updated successfully';
                $ret['data'] = DB::table($this->table)->find($id);
            }
        }
        return response()->json($ret);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
