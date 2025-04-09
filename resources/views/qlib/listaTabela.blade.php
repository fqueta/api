@php
    $style = isset($conf['style']) ? $conf['style'] : false;
     // remover o _adm para evitar de usar a routa de _adm para o cliente do site
     $frontend = App\Qlib\Qlib::is_frontend();
    if($frontend){
        $conf['routa'] = str_replace('_adm','',@$conf['routa']);
    }
    $routa = isset($conf['routa']) ? $conf['routa'] : false;
    $redirect = isset($conf['redirect']) ? $conf['redirect'] : @$routa;
    $campos_tabela = isset($conf['campos_tabela']) ? $conf['campos_tabela'] : false;
    $dados = isset($conf['dados']) ? $conf['dados'] : false;
    $sb = '?';
    if(isset($_GET['page'])){
        $sb = '?page='.$_GET['page'].'&';
    }
    if(isset($_GET['filter'])){
        $urlAtual = App\Qlib\Qlib::urlAtual();
        $urlAtual = rawurldecode($urlAtual);
        $redirect_base = base64_encode($urlAtual);
        $redirect = '&redirect_base='.$redirect_base.'&';
    }else{
        $redirect = route($redirect.'.index').$sb;
    }

@endphp
<style media="print">
    #DataTables_Table_0_wrapper .row:first-child{
        display: none;
    }
    .table td{
        padding: 0%;
    }
    .table thead th{
        padding: 0%;
    }
    #lista .card-body{
        padding: 0%;
    }
</style>
<table class="table table-hover table-striped dataTable {{$routa}}" style="{{@$style}}">
    <thead>
        <tr>
            <th class="text-center d-print-none" style="width: 3%"><input onclick="gerSelect($(this));" type="checkbox" name="todos" id=""></th>
            <th class="text-center d-print-none" style="width: 5%">...</th>
            @if (isset($campos_tabela) && is_array($campos_tabela))
                @foreach ($campos_tabela as $kh=>$vh)
                    @if (isset($vh['label']) && $vh['active'])
                        <th style="{{ @$vd['style'] }}">{{$vh['label']}}</th>
                    @endif
                @endforeach
            @else
                <th>#</th>
                <th>Nome</th>
                <th>Area</th>
                <th>Obs</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @if(isset($dados))
            @foreach($dados as $key => $val)
            @php
                if(isset($val->ID)){
                    $val->id = $val->ID;
                }
                $rlink = 'edit';
                if($routa=='leiloes_adm'||$routa=='users'||$routa=='beneficiarios'||$routa=='lotes'||$routa=='quadras'||$routa=='bairros'){
                    $rlink = 'show';
                }
                if($routa=='leiloes'){
                    $linkShow = url('/').'/'.App\Qlib\Qlib::get_slug_post_by_id(13).'/'.$val->token;
                }elseif($routa=='lances'){
                    $linkShow = false;
                }else{
                    $linkShow = route($routa.'.'.$rlink,['id'=>$val->id]). '?redirect='.$redirect.'idCad='.$val->id;
                }
                $linkDbckp = $linkShow;
                $link_edit = route($routa.'.edit',['id'=>$val->id]).'?redirect='.$redirect.'idCad='.$val->id;
                if($frontend){
                    $link_edit = App\Qlib\Qlib::get_slug_post_by_id(13).'/'.$val->token;
                }
            @endphp

            <tr style="cursor: pointer" ondblclick="window.location='{{ $linkDbckp}}'"  id="tr_{{$val->id}}" class="@if (isset($_GET['idCad']) && $_GET['idCad']==$val->id) table-info @endif" title="DÊ DOIS CLIQUES PARA ABRIR">
                    <td>
                        <input type="checkbox" class="checkbox" onclick="color_select1_0(this.checked,this.value);" value="{{$val->id}}" name="check_{{$val->id}}" id="check_{{$val->id}}">
                    </td>
                    <td class="text-right d-flex d-print-none">

                        @if ($frontend && isset($link_edit))
                            <a href=" {{ $link_edit }} " title="Editar" class="btn btn-sm btn-outline-secondary mr-2">
                                <i class="fa fa-pen"></i>
                            </a>
                        @else
                            @can('update',$routa)
                                @if ($routa=='leiloes_adm' || $routa=='users'||$routa=='beneficiarios'||$routa=='lotes'||$routa=='quadras'||$routa=='bairros')
                                    <a href="{{ $linkShow }}" title="visualizar" class="btn btn-sm btn-outline-secondary mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                                @php

                                @endphp
                                <a href=" {{ $link_edit }} " title="Editar" class="btn btn-sm btn-outline-secondary mr-1">
                                    <i class="fa fa-pen"></i>
                                </a>
                                @else
                                @if ($routa=='leiloes' || $routa=='leiloes_adm')
                                    <a href=" {{ route($routa.'.show',['id'=>$val->id]) }} " title="visualizar" class="btn btn-sm btn-outline-secondary mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href=" {{ route($routa.'.edit',['id'=>$val->id]) }} " class="btn btn-sm btn-outline-primary mr-2" title="Visualizar">
                                        <i class="fas fa-search"></i>
                                    </a>
                                @endif

                            @endcan
                        @endif
                        @can('delete',$routa)
                            <form id="frm-{{ $val->id }}" action="{{ route($routa.'.destroy',['id'=>$val->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" data-del="true" data-id="{{$val->id}}" name="button" title="Excluir" class="btn btn-outline-danger">
                                    <i class="fa fa-times"></i>
                                </button>
                            </form>
                            @if($routa=='clientes')
                                    @if (isset($val->config['status_contrato']) && ($val->config['status_contrato']=='Cancelado' || $val->config['status_contrato']=='cancelado' || $val->config['status_contrato']=='Reativando'))
                                    <button title="{{__('Reativar cadastro')}}" onclick="reativar_cadastro('{{$val->token}}','{{ $link_edit }}')" type="button" class="btn btn-outline-secondary ml-1"><i class="fa fa-recycle"></i></button>
                                    @endif
                                    {{-- @if (isset($val->config['status_contrato']) && ($val->config['status_contrato']=='Aprovado' || $val->config['status_contrato']=='aprovado'))
                                    <button title="{{__('Cancelar cadastro')}}" onclick="calcelar_cadastro('{{$val->token}}')" type="button" class="btn btn-outline-danger mr-1"><i class="fa fa-times"></i></button>

                                    @endif --}}

                                @endif

                        @endcan
                    </td>
                @if (isset($campos_tabela) && is_array($campos_tabela))
                    @foreach ($campos_tabela as $kd=>$vd)
                        @if (isset($vd['label']) && isset($vd['active']) && $vd['active'])
                            @if (isset($vd['type']) && ($vd['type']=='select' || $vd['type']=='selector'))
                                @php
                                    if(isset($vd['cp_busca']) && !empty($vd['cp_busca'])){
                                        $cp = explode('][',$vd['cp_busca']);
                                        $kr = @$val[$cp[0]][$cp[1]];
                                        if(isset($vd['arr_opc'][$kr])){
                                            if(is_array($vd['arr_opc'][$kr])){
                                                if(isset($vd['arr_opc'][$kr]['label'])){
                                                    $td = $vd['arr_opc'][$kr]['label'];
                                                }else{
                                                    $td = false;
                                                }
                                            }else{
                                                $td = @$vd['arr_opc'][$kr];
                                            }
                                        }
                                    }else{

                                        $td = @$vd['arr_opc'][$val->$kd];
                                    }
                                    if(is_array($td) ){
                                        $td = @$td['option'];
                                    }
                                @endphp
                                <td class="{{str_replace('[]','',$kd)}}" title="{{$td}}">{{$td}}</td>
                            @elseif (isset($vd['type']) && ($vd['type']=='select_multiple'))

                                @php
                                // echo $kd;
                                $nk = str_replace('[]','',$kd);

                                $arr = $val->$nk;
                                if(isset($vd['cp_busca'])){
                                    $ak = explode('][',$vd['cp_busca']);
                                    if(isset($ak[1])&&!empty($ak[1])){
                                        $kd = $ak[1];
                                        $arr = @$val[$ak[0]][$ak[1]];
                                    }
                                }
                                $td = false;
                                if(is_array($arr)){
                                        foreach ($arr as $k => $v) {
                                            $td .= @$vd['arr_opc'][$v].',';
                                        }
                                    }
                                @endphp
                                <td class="{{str_replace('[]','',$kd)}}" title="{{@$td}}">{{@$td}}</td>
                            @elseif (isset($vd['type']) && $vd['type']=='chave_checkbox' && isset($vd['arr_opc'][$val->$kd]))
                                <td class="{{str_replace('[]','',$kd)}}" title="{{$vd['arr_opc'][$val->$kd]}}">
                                    <div class="d-none d-print-table">
                                        {{$vd['arr_opc'][$val->$kd]}}
                                    </div>
                                    @php
                                        // $vd['checked'] = $vd['arr_opc'][$val->$kd];
                                        $vd['checked'] = $val->$kd;
                                    @endphp
                                    <div class="d-print-none custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                        <input type="checkbox" onchange="update_status_post(this)" data-campo="{{@$vd['campo']}}" data-id="{{$val->id}}" data-tab="{{@$vd['tab']}}" class="custom-control-input" @if(isset($vd['checked']) && $vd['checked'] == $vd['value']) checked @endif  value="{{$vd['value']}}"  name="{{$kd}}" id="{{$kd}}-{{$val->id}}">
                                        <label class="custom-control-label" for="{{$kd}}-{{$val->id}}">{{$vd['label']}}</label>
                                    </div>
                                </td>
                            @elseif(isset($vd['cp_busca']) && !empty($vd['cp_busca']))
                                @php
                                    $cp = explode('][',$vd['cp_busca']);
                                    $td = @$val[$cp[0]][$cp[1]];
                                    if(isset($vd['type']) && $vd['type']=='date'){
                                        $td = App\Qlib\Qlib::dataExibe(@$val[$cp[0]][$cp[1]]);
                                    }
                                @endphp
                                @if (isset($cp[1]))
                                    <td class="{{$cp[1]}}" title="{{ @$val[$cp[0]][$cp[1]] }}">{{ $td }}</td>
                                @endif
                            @elseif (isset($vd['type']) && ($vd['type']=='date'))
                                @php

                                    if(isset($vd['arr_opc']) && isset($vd['arr_opc'][$val->$kd])){
                                        $td = $vd['arr_opc'][$val->$kd];
                                    }else{
                                        $td = $val->$kd;
                                    }
                                @endphp

                                <td class="{{str_replace('[]','',$kd)}}" title="{{$td}}">
                                    {{ App\Qlib\Qlib::dataExibe($td)}}
                                </td>
                            @else
                                @php
                                    if(isset($vd['arr_opc']) && isset($vd['arr_opc'][$val->$kd])){
                                        $td = $vd['arr_opc'][$val->$kd];
                                    }else{
                                        $td = $val->$kd;
                                    }
                                @endphp

                                <td class="{{str_replace('[]','',$kd)}}" title="{{$td}}">
                                    {{$td}}
                                </td>
                            @endif
                        @endif
                    @endforeach
                @else

                    <td> {{$val->id}} </td>
                    <td> {{$val->nome_completo}} </td>
                    <td> {{$val->area_alvo}} </td>
                    <td> {{$val->obs}} </td>
                @endif
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
