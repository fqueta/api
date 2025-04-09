@include('qlib.partes_html',['config'=>[
    'parte'=>'modal',
    'id'=>'modal-geral',
    'conteudo'=>false,
    'botao'=>false,
    'botao_fechar'=>true,
    'tam'=>'modal-lg',
]])
@if (isset($config['media']))
    @include('admin.media.painel_select_media')
@endif
@include('qlib.modal_pesquisa')
<script src="{{url('/js/jquery-ui.min.js')}}"></script>
<script src="{{url('/js/jquery.maskMoney.min.js')}}"></script>
<script src="{{url('/js/jquery.inputmask.bundle.min.js')}}"></script>
<script src="{{url('/summernote/summernote.min.js')}}"></script>
<script src="{{url('/vendor/venobox/venobox.min.js')}}"></script>
<script src="{{url('/js/jquery.validate.min.js')}}"></script>
<script src="{{url('/js/jscolor/jscolor.js')}}"></script>
<script src=" {{url('/js/lib.js')}}?ver={{config('app.version')}}"></script>
<script>
    $(function(){
        $('.dataTable').DataTable({
                "paging":   false,
                stateSave: true,
                language: {
                    url: '/DataTables/datatable-pt-br.json'
                },
                order:[]
        });
        carregaMascaraMoeda(".moeda");
        $('[selector-event]').on('change',function(){
            initSelector($(this));
        });
        $('[mask-cpf]').inputmask('999.999.999-99');
        @if (App\Qlib\Qlib::is_frontend())
            $('.summernote').summernote({
                height: 250,
                placeholder: 'Digite o conteudo',
                toolbar:[
                    ['style', ['bold', 'italic']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });
            $('[data-dismiss="modal"]').on('click', function(){
                $('.modal').modal('hide');
            });
        @else
            $('[vinculo-event]').on('click',function(){
                var funCall = function(res){};
                initSelector($(this));
            });

            $('.select2').select2();
            $('[fachar-alerta-fatura="true"]').on('click',function(){
                fecharAlertaFatura('{{route('alerta.cobranca.fechar')}}');
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            lib_autocompleteGeral('.autocomplete');
            // lib_autocompleteGeral('.autocomplete-pesq',function(ui,el){
            //     console.log(ui);
            //     try {
            //         if(ui.id){
            //             let ur = '/beneficiarios/'+ui.id+'?redirect='+window.location.href;
            //             window.location = ur;
            //         }
            //     } catch (error) {
            //         console.log(error);
            //     }
            // });
            $('.summernote').summernote({
                height: 250,
                placeholder: 'Digite o conteudo',
                toolbar:[
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    // ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'video']],
                    // ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview', 'help']],
                    // ['view', ['fullscreen', 'codeview', 'help']],
                ],
                callbacks: {
                    onVideoInsert: function(url) {
                    // Após o vídeo ser inserido, encontrar o iframe e modificar suas dimensões
                        alert('insert video');
                        $('iframe').attr('width', '100%').attr('height', '360');
                    }
                }
            });
        @endif
        new VenoBox({
            selector: ".venobox",
            numeration: true,
            infinigall: true,
            share: false,
            spinner: 'rotating-plane'
        });
        $('[data-toggle="tooltip"]').tooltip({html:true});
        $('[data-toggle="popover"]').popover({html:true,container: 'body'});
        $('[data-widget="navbar-search"]').on('click', function(){
            $('.navbar-search-block').hide();
            $('#pesquisar').modal('show');
            // $('[type="button"][data-widget="navbar-search"]').click();
        });
        jscolor.presets.default = {
            value: '#88DD20',
            position: 'right',
            backgroundColor: '#333',
            palette: '#fff #000 #808080 #996e36 #f55525 #ffe438 #88dd20 #22e0cd #269aff #bb1cd4',
        };
    });
</script>
