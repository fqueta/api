@include('qlib.partes_html',['config'=>[
    'parte'=>'modal',
    'id'=>'modal-geral',
    'conteudo'=>false,
    'botao'=>false,
    'botao_fechar'=>true,
    'tam'=>'modal-lg',
]])
<script src="{{url('/')}}/js/jquery.maskMoney.min.js"></script>
<script src="{{url('/')}}/js/jquery-ui.min.js"></script>
<script src="{{url('/')}}/js/jquery.inputmask.bundle.min.js"></script>
<script src="{{url('/')}}/summernote/summernote.min.js"></script>
<script src=" {{url('/')}}/js/lib.js"></script>
<script>
    $(function(){
        $('.dataTable').DataTable({
                "paging":   false,
                stateSave: true,
                language: {
                    url: '/DataTables/datatable-pt-br.json'
                }
        });
        carregaMascaraMoeda(".moeda");
        $('[selector-event]').on('change',function(){
            initSelector($(this));
        });
        $('[vinculo-event]').on('click',function(){
            var funCall = function(res){};
            initSelector($(this));
        });

        $('.select2').select2();
        var urlAuto = $('.autocomplete').attr('url');
        $( ".autocomplete" ).autocomplete({
            source: urlAuto,
            select: function (event, ui) {
                //var sec = $(this).attr('sec');
                lib_listarCadastro(ui.item,$(this));
            },
        });
        $('.summernote').summernote({
            height: 400,
            placeholder: 'Digite o conteudo',
            /*
            onImageUpload: function(files, editor, welEditable) {
                alert('enviar imagem')
                //sendFile(files[0],editor,welEditable);
            }*/
        });
    });
</script>
