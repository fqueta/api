@php
    $_REQUEST['post'] = isset($dados) ? $dados : false;
    $post_id = isset($dados['ID']) ? $dados['ID'] : false;
    $seg1 = request()->segment(1); //link da página em questão
    $seg2 = request()->segment(2); //link da página em questão
    $seg3 = request()->segment(3); //link da página em questão
    // $slug = isset($dados['post_name']) ? $dados['post_name'] : $seg1;
    // $slug2 = isset($dados['slug2']) ? $dados['slug2'] : $seg2;
    $title = isset($dados['post_title']) ? $dados['post_title'] : false;
    $tipo_pagina = isset($dados['config']['tipo_pagina']) ? $dados['config']['tipo_pagina'] : false;
    // $main = (new App\Http\Controllers\siteController)->get_main_post($post_id);
    // $link_edit = App\Qlib\Qlib::get_link_edit_admin($post_id,$_REQUEST);
@endphp
@extends('site.layout.app')
@section('title')
    {{$title}}
@stop
@section('banner-topo')
    {{-- @if ($tipo_pagina=='principal')
        @include('site.layout.banner-home')
    @elseif ($tipo_pagina=='leiloes_publicos')
        @if ($slug2==null)
            @include('site.layout.banner-home')
        @endif
    @elseif ($tipo_pagina=='land_page')
    @elseif ($tipo_pagina=='html')
        @include('site.layout.banner-sec')
    @else
        @include('site.layout.banner-sec')
    @endif --}}
@stop
@section('main')
    {{-- @if ($tipo_pagina=='principal')
    @elseif ($tipo_pagina=='leiloes_publicos')
        @include('site.layout.main')
    @elseif ($slug=='email')
        @include('site.layout.email')
    @elseif ($tipo_pagina=='land_page')
    @elseif ($tipo_pagina=='secundaria')
        <section class="page-content">
            <div class="container py-5">
                @include('site.layout.main')
            </div>
        </section>
    @else
        @include('site.layout.main')
    @endif --}}
    {{-- {{ dd($seg1); }} --}}
    @if ($seg2=='orcamentos')
        @include('site.orcamento')
    @elseif ($seg2=='proposta-pnl-periodos')
        @include('site.orcamento')
    @elseif ($seg2=='contratos' && $seg3=='vencidos')
        @include('site.contratos_vencidos')
    @elseif ($seg2=='ass')
        @include('site.orcamento')
    @else
        @include('site.orcamento')
    @endif
    {{-- @can('is_admin')
        <section>
            <div class="container">
                <div class="row">
                    <div class="col text-end py-4 ">
                        <a href="{{@$link_edit}}" title="{{__('Editar a página')}}" class="btn btn-outline-secondary"><i class="fas fa-pen" aria-hidden="true"></i> {{__('Editar')}} <i class="fas fa-chevron-right    "></i></a>
                    </div>
                </div>
            </div>
        </section>
    @endcan --}}
@stop
@section('css')
    <link rel="stylesheet" href="{{url('/')}}/css/select2.css">
    @include('qlib.csslib')
    <link rel="stylesheet" href="{{url('/')}}/DataTables/DataTables-1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="{{url('/')}}/vendor/jquery/jquery.min.js"></script>

@stop
@section('js')
    {{-- @include('qlib.jslib') --}}
    <script src="{{url('/')}}/js/select2.min.js" ></script>
    <script src="{{url('/')}}/DataTables/datatables.min.js" ></script>
    <script>
        $('#inp-password').val('');
        $('[mask-cpf]').inputmask('999.999.999-99');
        $('[mask-cnpj]').inputmask('99.999.999/9999-99');
        $('[mask-data]').inputmask('99/99/9999');
        $('[mask-cep]').inputmask('99.999-999');
    </script>
@stop
