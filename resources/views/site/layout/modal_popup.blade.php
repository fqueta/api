@php
    $popup = false;
    global $ganhador;
    $close_popup = session()->get('close_popup');
    // dd(session()->all());
    $seg1 = request()->segment(1);
    $seg2 = request()->segment(2);
    if($ganhador && isset($ganhador)){
        $popup = view('site.leiloes.list_leiloes_ganhos',['ganhos'=>$ganhador,'card_title'=>'Leilões ganhos aguardando pagamento']);
    }
@endphp
@if ($popup && $seg1!='payment' && $close_popup!='s')
    @include('site.partes_bs.modal',['config'=>[
        'tam'=>'modal-lg',
        'id'=>'modal-popup',
        'conteudo'=>$popup,
    ]])
    <button type="button" id="btn-modal" class="btn btn-primary d-none" data-bs-toggle="modal" data-bs-target="#modal-popup">
        open popup
      </button>
    <script>
        $(function(){
            let data = sessionStorage.getItem("close_popup");
            if(!data)
            document.getElementById("btn-modal").click();
            $('#modal-popup [data-bs-dismiss="modal"]').on('click', function(){
                close_popup();
            });
        });

    </script>
@endif
