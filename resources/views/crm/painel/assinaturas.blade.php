<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
@php
// dd($arr_processo);
$assinantes = isset($arr_processo['signers']) ? $arr_processo['signers'] : false;
@endphp
<style>
    .h1-sig{
        font-size: 16px;
        font-weight: bold;
    }
</style>
@if (is_array($assinantes))
    <div class="card card-secondary card-outline">
        <div class="card-header">
            {{__('Gerenciamento de assinaturas')}}
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($assinantes as $k=>$v )
                {{-- {{dd($v)}} --}}
                    @php
                        $status_sign = $v['status'];
                        $bdg = 'badge-danger';
                        if($status_sign=='signed'){
                            $bdg = 'badge-success';
                            $status_sign = 'Assinado';
                        }else{
                            $status_sign = 'Aguardando Assinatura';
                        }
                    @endphp
                    <div class="card w-100">
                        <div class="card-body">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>Nome: </b> {{@$v['name']}}
                                    </div>
                                    <div class="col-md-6">
                                        <b>Visualizado: </b> {{@$v['times_viewed']}}
                                    </div>
                                    <div class="col-12 mb-2">
                                        <b>Status: </b> <span class="badge {{$bdg}}">{{$status_sign}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">{{__('Link de assinatura:')}}</span>
                                      </div>
                                    <input type="text" class="form-control" disabled value="{{$v['sign_url']}}" aria-label="Text input with dropdown button">
                                    <div class="input-group-append">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Ação</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="copyTextToClipboard('{{$v['sign_url']}}')">Copiar</a>
                                        <a class="dropdown-item" target="_blank" href="{{$v['sign_url']}}">Acessar</a>
                                        {{-- <a class="dropdown-item" href="#">Something else here</a>
                                        <div role="separator" class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a> --}}
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- <div class="card-footer text-muted">
            Footer
        </div> --}}
    </div>
@endif
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
