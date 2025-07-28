<style>
    .vermelho{
        color:red;
    }
    .verde{
        color:green;
    }
</style>
<div class="container mt-4 pt-4 mb-3">
    <h2>
        {!! $conteudo !!}
    </h2>
    {{-- {{ dd($dados) }} --}}
    @if (is_array($dados) && isset($dados['dm']) && is_array($dados['dm']))
        <div class="row">
            <div class="col-md-6">
                <b>Data da consulta:</b>
                <span>
                    {{ date('d/m/Y') }}
            </div>
            <div class="col-md-6 text-right">
                </span>
                <b>Total vencidos:</b>
                <span>
                    {{ $dados['total_vencidos'] }}
                </span>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-hover dataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Validade</th>
                            <th>Telefone</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dados['dm'] as $k=>$v )
                            @php
                                $acao = '<a href="'.@$v['link_contrato'].'" target="_blank" title="Link do contrato " class="btn btn-default"><i class="fa fa-user"></i></a>';
                                $link_zap = isset($v['zapguru']['link_chat']) ? $v['zapguru']['link_chat'] : false;
                                // dump($link_zap);
                                if($link_zap){
                                    $telefone = '<a href="'.$link_zap.'" target="_blank" title="Acessar Whatsapp">'.$telefone.'</a>';
                                    $acao .= '<a href="'.$link_zap.'" target="_blank" title="Acessar Whatsapp" class="btn btn-success"><i class="fab fa-whatsapp"></i></a>';
                                }else{
                                    $telefone = $v['telefonezap'];
                                }
                            @endphp
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $v['aluno'] }}</td>
                                <td>{{ $v['nome_curso'] }}</td>
                                <td>{{ App\Qlib\Qlib::dataExibe($v['data_validade']) }}</td>
                                <td>{!! $telefone !!}</td>
                                <td class="">
                                    <div class="d-flex">
                                        {!! $acao !!}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
