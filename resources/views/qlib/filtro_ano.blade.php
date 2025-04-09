@php
    $form = isset($form)?$form:true;
    $onclick=isset($onclick)?$onclick:"$('#frm-ano').submit();$('#preload').css('display','block')";
    $active=false;
@endphp
@if(isset($arr_ano) && is_array($arr_ano))
    @if($form)
    <form id="frm-ano" method="get">
    @endif
    @php
        $select_de = 'selected';
        $select_ca = '';
        $displaytpc_a = 'style="display:none"';  //mostar tipo de consulta anual
        $displaytpc_p = '';  //mostar tipo de consulta periodico

        if(isset($_GET['dataI'],$_GET['dataF'])&&!empty($_GET['dataI'])&&!empty($_GET['dataF'])){
            $displaytpc_a = '';  //mostar tipo de consulta anual
            $displaytpc_p = 'style="display:none"';  //mostar tipo de consulta periodico

        }
        if(isset($_GET['campo_data'])&&$_GET['campo_data']=='created_at'){
            $select_de = '';
            $select_ca = 'selected';
        }
        $routename = Route::currentRouteName();
    @endphp

        <div class="row mb-2 tpc-p" {!!$displaytpc_a!!}>
            <div class="col-md-2">
                <label for="">{{__('Tipo de data')}}</label>
                <select class="form-control" name="campo_data" id="campo_data">
                    <option {{$select_de}} value="data_exec">{{__('Data de Execução')}}</option>
                    <option {{$select_ca}} value="created_at">{{__('Data de Cadastro')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="">Data Inicial</label>
                <input type="date" name="dataI" class="form-control" value="{{@$_GET['dataI']}}" id="dataI">
            </div>
            <div class="col-md-2">
                <label for="">Data Final</label>
                <input type="date" name="dataF" class="form-control" value="{{@$_GET['dataF']}}" id="dataF">
            </div>
            <div class="col-md-3 pt-4">
                <button type="submit"  class="btn btn-primary mt-2" title="{{__('Consultar')}}"><i class="fas fa-search"></i></button>
                <button type="button" onclick="exibeTpc('a')" class="mt-2 btn btn-default tpc-p" title="{{__('Exibir consulta anual')}}">{{__('Consulta Anual')}}</button>

                <a class="btn btn-default mt-2" title="Limpar" href="{{route($routename)}}"><i class="fas fa-broom"></i></a>
            </div>
        </div>
        <div class="btn-group btn-group-toggle  tpc-a" {!!$displaytpc_p!!} data-toggle="buttons">
            @foreach ($arr_ano as $ka=>$va)
                @php
                    $checked=false;
                    if(isset($_GET['ano'])&&$_GET['ano']==$va->vl){
                        $active='active';
                        $checked='checked';
                    }
                @endphp
                <label class="btn btn-outline-secondary {{$active}}">
                    <input {{$checked}} onclick="{{$onclick}}" type="radio" name="ano" id="ano-{{$va->vl}}" value="{{$va->vl}}"/>
                    {{$va->vl}}
                </label>
            @endforeach
            @php
                $checkedTodos = false;
                if(isset($_GET['ano'])&&$_GET['ano']==''){
                    $checkedTodos = 'checked';
                }elseif (!isset($_GET['ano'])) {
                    $checkedTodos = 'checked';
                }
            @endphp
            <label class="btn btn-outline-secondary {{@$active}}">
                <input {{$checkedTodos}} onclick="checkTodosAnos();" type="radio" name="ano" id="todos" value=""/>
                {{__('Todos Anos')}}
            </label>
        </div>
        <button type="button" onclick="exibeTpc('p')" class="btn btn-default tpc-a d-none" {!!$displaytpc_p!!} title="{{__('Exibir consulta periódica')}}">{{__('Consulta Periódica')}}</button>
    @if($form)
    </form>
    @endif
@endif
