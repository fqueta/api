
@php
    $redirect_base = false;
    $btn_continuar = isset($_GET['bc']) ? $_GET['bc'] : 'true';
    $btn_sair = isset($_GET['bs']) ? $_GET['bs'] : 'true';
    if(isset($config['ac']) && $config['ac']=='cad'){
        $redirect_base = isset($config['redirect'])?$config['redirect']:$redirect_base;
        if(isset($_GET['rbase']) && !empty($_GET['rbase'])){
            $redirect_base = base64_decode($_GET['rbase']);
        }
    }
    if(isset($config['ac']) && $config['ac']=='alt'){
        $_GET['redirect'] = isset($_GET['redirect']) ? $_GET['redirect'] : route($config['route'].'.index').'?idCad='.$value['id'];
        if(isset($_GET['redirect_base'])&&!empty($_GET['redirect_base'])){
            $redirect_base = base64_decode($_GET['redirect_base']);
            $redirect_base .= '&idCad='.$value['id'];
            //echo $redirect_base;
        }
    }
    $label_btn_sair = isset($_GET['lbs']) ? $_GET['lbs']:__('Salvar e sair');
    if(@$config['ac']=='cad'){
        $label_btn_permanecer = isset($_GET['lbp']) ? $_GET['lbp']:__('Salvar e continuar');
    }else{
        $label_btn_permanecer = isset($_GET['lbp']) ? $_GET['lbp']:__('Salvar e permanecer');
    }
    $frontend = App\Qlib\Qlib::is_frontend();
    if($frontend){
        $redirect_base = isset($config['redirect'])?$config['redirect']:$redirect_base;
        if(isset($_GET['rbase']) && !empty($_GET['rbase'])){
            $redirect_base = base64_decode($_GET['rbase']);
        }
    }
    // dd($config,$_GET,$redirect_base);
@endphp
<div class="col-md-12 div-salvar bg-light">
    @if (isset($redirect_base) && $redirect_base)
        <a href="{{$redirect_base}}" btn-volter="true" redirect="{{@$redirect_base}}" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i> Voltar</a>
    @else
        <button type="button" btn-volter="true" href="{{route($config['route'].'.index')}}" onclick="btVoltar($(this))" redirect="{{@$_GET['redirect']}}" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i> Voltar</button>
    @endif
        @if (isset($config['ac']) && $config['ac']=='alt')
            @php
                $r_novo_cadastro = route($config['route'].'.create');
                $btnAlt=false;
                $sec = request()->segment(1);
                if($frontend){
                    //Verifica se é precadastro
                    $origem = isset($value['config']['origem']) ? $value['config']['origem'] : false;
                    $r_novo_cadastro = url('/').'/'.App\Qlib\Qlib::get_slug_post_by_id(2);
                    if($sec==App\Qlib\Qlib::get_slug_post_by_id(14) || $origem=='precadastro'){
                        if($redirect_base && isset($_GET['rbase'])){
                            $btnAlt='<button type="submit" btn="sair" class="btn btn-primary">'.__('Salvar').'</button>';
                        }else{
                            $btnAlt='<button type="submit" btn="permanecer" class="btn btn-primary">'.__('Salvar').'</button>';
                        }
                    }
                    echo $btnAlt;
                }
                if($config['route']=='admin.sic'){
                    $config['route'] = @$config['url'];
                }
            @endphp
            @can('create',$config['route'])
                <a href="{{$r_novo_cadastro}}" title="{{ __('Incluir novo cadastro') }}" class="btn btn-default"> <i class="fas fa-plus"></i> {{ __('Novo cadastro') }}</a>
                @if ($config['route']=='clientes')
                    <button type="button" onclick="excluir_cliente('{{ $value['id'] }}','{{ route('clientes.index') }}')"  title="{{ __('Eliminar este cadastro') }}" class="btn btn-outline-danger"> <i class="fas fa-times"></i> {{ __('Excluir') }}</button>
                @endif
            @endcan
            @if ($config['route']=='perfil')
                <button type="submit" btn="permanecer" class="btn btn-primary">{{$label_btn_permanecer}}</button>
            @endif
            @can('update',$config['route'])
                @if($btn_continuar=='true')
                    <button type="submit" btn="permanecer" class="btn btn-primary">{{$label_btn_permanecer}}</button>
                @endif
                @if ($btn_sair=='true')
                    <button type="submit" btn="sair"  class="btn btn-outline-primary">Salvar e Sair <i class="fa fa-chevron-right"></i></button>
                @endif
            @endcan
        @else
            @if($frontend)
                <button type="submit" btn="permanecer" class="btn btn-primary">Salvar</button>
            @else
                @can('create',$config['route'])
                    @if (!isset($_GET['popup']))
                        @if(isset($_GET['qc']) && $_GET['qc']!='s')
                            <button type="submit" btn="permanecer" class="btn btn-primary">{{$label_btn_permanecer}}</button>
                        @endif
                    @endif
                    @if($btn_continuar=='true')
                        <button type="submit" btn="permanecer" class="btn btn-primary">{{$label_btn_permanecer}}</button>
                    @endif
                    @if(isset($_GET['qc']) && $_GET['qc']=='s')
                        <button type="submit" btn="sair"  class="btn btn-primary">Salvar e Prosseguir <i class="fa fa-chevron-right"></i></button>
                    @endif
                    @if($btn_sair=='true')
                        <button type="submit" btn="sair"  class="btn btn-outline-primary">{{$label_btn_sair}} <i class="fa fa-chevron-right"></i></button>
                    @endif
                @endcan
            @endif
        @endif
</div>
