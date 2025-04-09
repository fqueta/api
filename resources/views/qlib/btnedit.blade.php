
@php
    if(isset($config['ac']) && $config['ac']=='alt'){
        $_GET['redirect'] = isset($_GET['redirect']) ? $_GET['redirect'] : route($config['route'].'.index').'?idCad='.@$value['id'];
        if(isset($_GET['redirectPos'])&&$_GET['redirectPos']=='n'){
            $_GET['redirect'] = false;
        }
    }
    $labelEdit = 'Editar';
    if(isset($config['url'])&&$config['url']=='sic'){
        $labelEdit = 'Responder';
    }
    $id = $value['id'];
@endphp
<div class="col-md-12 div-salvar bg-light d-print-none">
        @if(isset($config['ambiente']) && $config['ambiente']=='front')
            <a btn-volter="true" href="{{route($config['route'].'.index')}}?idCad={{$id}}" redirect="{{@$_GET['redirect']}}" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i> Voltar</a>
        @else
            <button type="button" btn-volter="true" href="{{route($config['route'].'.index')}}" onclick="btVoltar($(this))" redirect="{{@$_GET['redirect']}}" class="btn btn-outline-secondary"><i class="fa fa-chevron-left"></i> Voltar</button>
        @endif
        @if (isset($config['ac']) && $config['ac']=='alt')
            @can('create',$config['url'])
                <a href="{{route($config['route'].'.create')}}" class="btn btn-default"> <i class="fas fa-plus"></i> Novo cadastro</a>
            @endcan
            @can('update',$config['url'])
                <button type="button" btn="print" onclick="window.print();" class="btn btn-outline-primary"><i class="fas fa-print"></i></button>
                <a href="{{route($config['route'].'.edit',['id'=>$config['id']])}}?redirect={{App\Qlib\Qlib::urlAtual()}}" btn="{{$labelEdit}}"  class="btn btn-outline-primary"> <i class="fa fa-pen" aria-hidden="true"></i> {{$labelEdit}}</a>
            @elsecan('is_user_front_v')
                <button type="button" btn="print" onclick="window.print();" class="btn btn-outline-primary"><i class="fas fa-print"></i></button>
            @endcan
        @else

        @endif
</div>
