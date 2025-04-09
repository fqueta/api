@if ($config['parte']=='painel')
        <link rel="stylesheet" href="{{url('/')}}/css/dropzone.min.css" type="text/css" />
        <script src="{{url('/')}}/js/dropzone.min.js"></script>
        <!-- Button trigger modal -->
        <div class="row">
            <div class="col-md-12 mb-2">
                <span id="lista-files">
                    {{App\Qlib\Qlib::gerUploadWp([
                        'parte'=>'lista',
                        'token_produto'=>$config['token_produto'],
                        'tipo'=>'list',
                        'listFiles'=>@$config['listFiles'],
                        'routa'=>@$config['routa'],
                        'arquivos'=>@$config['arquivos'],
                        ])}}

                </span>
            </div>
            <div class="col-md-12">
                @can('create',$config['routa'])
                    @if (isset($config['arquivos']) && $config['arquivos'])
                        @php
                            if(isset($config['listFiles'][0]['guid'])){
                                $display = 'none';
                            }else{
                                $display = 'block';
                            }
                        @endphp
                        <button id="enviar-arquivo" style="display: {{$display}}" title="{{__('Enviar arquivos do computador')}}" type="button" class="btn btn-primary" data-toggle="modal" data-target="#modelId"> <i class="fas fa-upload"></i>
                            {{ __('Enviar arquivos') }}
                        </button>
                    @endif
                @endcan
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Uploads de arquivos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <nav class="nav nav-tabs nav-stacked">
                                <a class="nav-link active" href="#">Enviar do computador</a>
                                <a class="nav-link" href="#">Imagens no Servidor</a>
                            </nav>
                            <form id="file-upload" action="{{route('api-wp.store')}}" method="post" class="dropzone" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="token_produto" value="{{$config['token_produto']}}" />
                                <input type="hidden" name="pasta" value="{{$config['pasta']}}" />
                                <input type="hidden" name="arquivos" value="{{$config['arquivos']}}" />
                                <input type="hidden" name="typeN" value="{{@$config['typeN']}}" />
                                <input type="hidden" name="post_id" value="{{@$config['id']}}" />
                                <input type="hidden" name="wp_ep" value="midia" />
                                <div class="fallback">
                                    <input name="file" type="file" multiple />
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"  onclick="visualizaArquivos('{{$config['id']}}','{{route('uploads.index')}}','i_wp')" data-dismiss="modal">{{__('Fechar')}}
                        </button>
                        <!--<button type="button" class="btn btn-primary">{{__('Visualizar')}}</button>-->
                    </div>
                </div>
            </div>
        </div>
        <!--
            <script>
                $('#exampleModal').on('show.bs.modal', event => {
                    var button = $(event.relatedTarget);
                    var modal = $(this);
                    // Use above variables to manipulate the DOM

                });
            </script>
        -->
@endif

@if ($config['parte']=='lista' && isset($config['listFiles']) && is_array($config['listFiles']))
    <div class="list-group">
        @foreach ($config['listFiles'] as $k=>$vl)
        <div class="list-group-item d-flex align-items-center px-0" id="item-{{$vl['ID']}}">
            @if (isset($vl['post_mime_type']) && $vl['post_mime_type']=='image/jpeg')
                <div class="col-md-12">
                    <a href="{{$vl['guid']}}" class="venobox">
                        <img src="{{$vl['guid']}}" alt="{{$vl['post_title']}}" style="width: 100%">
                    </a>
                </div>
                @can('delete',$config['routa'])
                <span style="position: absolute;top:2px;right:2px">
                    <button type="button" onclick="excluirArquivo('{{$vl['ID']}}','{{route('uploads.destroy',['id'=>$vl['ID']])}}')" class="btn btn-default" title="Excluir"><i class="fas fa-trash "></i></button type="button">
                </span>
                @endcan
            @else
                <a href="{{$vl['guid']}}" target="_blank" rel="noopener noreferrer">
                    <span class="pull-left"><i class="fas fa-file-{{@$vl['tipo_icon']}} fa-2x"></i></span> {{$vl['post_title']}}
                </a>
                @can('delete',$config['routa'])
                  <button type="button" onclick="excluirArquivo('{{$vl['ID']}}','{{route('uploads.destroy',['id'=>$vl['ID']])}}')" class="btn btn-default" title="Excluir"><i class="fas fa-trash "></i></button type="button">
                @endcan
            @endif
        </div>
        @endforeach
    </div>
@endif

