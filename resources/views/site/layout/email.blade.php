<div class="container pt-3">
    <div class="mx-auto col-md-5">
        <div class="card mt-5 mb-5">
            <div class="card-header">
                {{__('Complete seu cadastro')}}
            </div>
            <div class="card-body">
                {{-- {{ __('adminlte::adminlte.verify_check_your_email') }}
                {{ __('adminlte::adminlte.verify_if_not_recieved') }}, --}}
                @php
                    $send = session('message-very');
                @endphp
                @if ($send=='enviado')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                      {{-- <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button> --}}
                      <strong>Sucesso:</strong>
                      <span>link de verificação foi enviado, por favor verifique a caixa de entrada do seu e-mail</span>
                      <span>Caso não encontre verifique na caixa de span ou na lixeira.</span>

                    </div>

                    <script>
                      $(".alert").alert();
                    </script>
                @else
                <p>{{__('Antes de continuar, por favor verifique seu email com o link de confirmação. Clique no botão abaixo, que um link de confirmação de conta será enviado para o seu e-mail')}}</p>
                <div class="text-center">
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <input type="hidden" name="send-email" value="s" />
                        <button type="submit" class="btn btn-primary">
                            {{-- {{ __('adminlte::adminlte.verify_request_another') }} --}}
                            {{(__('Solicitar um link'))}}
                        </button>.
                    </form>
                </div>
                @endif

            </div>
            <div class="card-footer text-muted">
                <a href="{{route('index')}}" class="btn btn-outline-primary"> <i class="fas fa-chevron-left"></i> Voltar</a>
            </div>
        </div>
    </div>

</div>
{{-- @extends('adminlte::auth.auth-page', ['auth_type' => 'login']) --}}

{{-- @section('auth_header', __('adminlte::adminlte.verify_message'))

@section('auth_body')

    @if(session('resent'))
        <div class="alert alert-success" role="alert">
            {{ __('adminlte::adminlte.verify_email_sent') }}
        </div>
    @endif

    {{ __('adminlte::adminlte.verify_check_your_email') }}
    {{ __('adminlte::adminlte.verify_if_not_recieved') }},

    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">
            {{ __('adminlte::adminlte.verify_request_another') }}
        </button>.
    </form>

@stop --}}
