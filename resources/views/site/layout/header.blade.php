@php
    global $post,$menus,$notification,$user;
    $n=isset($user['name']) ? $user['name'] : false;
    $nome = false;
    if($n){
        $n = explode(' ',$n);
        if(isset($n[0])){
            $nome = $n[0];
        }
    }
@endphp
<!-- ======= Header ======= -->
<style>
    .dropdown-menu-end1{
        left: auto !important;
        right: 0px;
    }
    .cx-notification{
        padding-left: 10px;
        padding-right: 10px;
    }
    .w-10{
        width: 10%;
    }
</style>
<div class="logo d-none d-print-block">
  {{-- <img src="{{url('/vendor/adminlte/dist/img/logo-h.png')}}" alt="" class="img-fluid"> --}}
  <img src="{{url('/vendor/site/logo/logo.png')}}" alt="" class="img-fluid">
</div>
<div class="container d-flex justify-content-between align-items-center d-print-none">

      <div class="logo">
        {{-- <a href="{{url('/'.App\Qlib\Qlib::get_slug_post_by_id(37))}}"><img src="{{url('/vendor/adminlte/dist/img/logo-h.png')}}" alt="" class="img-fluid"></a> --}}
        <a href="https://aeroclubejf.com.br"><img src="{{url('/vendor/site/logo/logo.png')}}" alt="" class="img-fluid"></a>
      </div>

      <nav id="navbar" class="navbar navbar-expand-lg" data-bs-theme="dark">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            {{-- @if(is_array($menus))
                @foreach ($menus as $km=>$vm )
                    @php
                        $sl = @$post->post_name;
                        $active = false;
                        $comple_url = '/';
                        if($sl==$vm['url']){
                            $active = 'active';
                        }
                        if($vm['description']=='Home' || $vm['description']=='Contato'){
                            $comple_url = '';
                        }
                    @endphp
                    <li class="nav-item ms-3"><a class="{{$active}}" href="{{$comple_url.$vm['url']}}">{{$vm['description']}}</a></li>
                @endforeach
            @endif --}}
            @can('is_logado')

                <li class="nav-item me-3 me-lg-0">
                    <div class="dropdown">
                        <a class="me-3 dropdown-toggle hidden-arrow" href="#" id="notification"
                        role="button" data-mdb-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell text-white"></i>
                            @if(isset($notification['total']) && $notification['total']>0)
                                <span class="badge rounded-pill badge-notification bg-danger" id="total-notifications">
                                    {{$notification['total']}}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end1" style="width: 450px" aria-labelledby="notification">
                            <li class="w-100 cx-notification">
                                @include('site.layout.top_notification')
                            </li>
                            {{-- <li>
                                <a class="dropdown-item" href="#">Another news</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </li> --}}
                        </ul>
                    </div>
                </li>

                {{-- @if (Gate::allows('is_admin2') || Gate::allows('is_customer_logado'))
                    <li class="dropdown dropdown-menu-right"><a href="#" style="padding-left: 0px"><span><i class="fas fa-user-circle fa-2x"></i> {{@$nome}}</span> <i class="bi bi-chevron-down"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end1">
                        @can('is_admin2')
                            <li><a href="/admin">Painel Admin</a></li>
                        @endcan
                        <li class="dropdown"><a href="#"> <span>Gerenciar leilões</span> <i class="bi bi-chevron-right"></i> </a>
                            <ul>
                            <li><a href="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(2)}}">{{__('Cadastrar leilão')}}</a></li>
                            <li><a href="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(12)}}">{{__('Meus leilões')}}</a></li>
                            <li><a href="{{url('/')}}/{{App\Qlib\Qlib::get_slug_post_by_id(3)}}">{{__('Meus lances')}}</a></li>
                            </ul>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="{{url('/meu-cadastro')}}">Meu Cadastro</a></li>
                        <li class="user-footer">
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{__('Sair')}}
                            </a>
                            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                        </ul>
                    </li>
                @else
                    <li>

                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{__('Usuario bloquedo clique para sair')}}
                        </a>
                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                @endif --}}
                {{-- <li><a href="/cart"><i class="fas fa-cart-arrow-down"></i></a></li> --}}

            @else
                {{-- <li class="nav-item dropdown ms-3">
                    <a class="nav-link dropdown-toggle show" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="true">
                        Minha conta
                    </a>
                    <ul class="dropdown-menu border-0 show" data-bs-popper="static">
                        <li>
                            <a class="dropdown-item" href="{{route('login')}}">
                                <small><i class="fa fa-user"></i> Login</small>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{url('/user/create')}}">
                                <small><i class="fa fa-address-card"></i> Cadastro</small>
                            </a>
                        </li>

                    </ul>
                </li> --}}
                {{-- <li><a class="btn btn-default btn-flat float-right" href="{{route('login')}}"><i class="fas fa-user"></i>&nbsp;Login</a></li>
                <li><a class="btn btn-default btn-flat float-right" href="{{url('/user/create')}}"><i class="fas fa-user"></i>&nbsp;Cadastrar</a></li> --}}
            @endcan
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav>
      <!-- .navbar -->

    </div>
