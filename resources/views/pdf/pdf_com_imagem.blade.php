@php
    $style_content = isset($style_content) ? $style_content : '';
    $altura_pagina = isset($altura_pagina) ? $altura_pagina : '1402px';
@endphp
<!DOCTYPE html>
<html lang="pt-br">
<meta charset="UTF-8">
<head>
    <style>
        /* Estilo geral para remover margens e usar o layout completo */
        @page {
            margin: 0cm;
        }

        body {
            margin: 0cm;
            padding: 0cm;
            font-family: Arial, sans-serif;
        }

        .pagina {
            position: relative;
            width: 100%;
            height: {{$altura_pagina}}; /* Altura total da página */
            page-break-after: always; /* Quebra de página após cada página */
        }
        /* Estilo para o conteúdo sobre a imagem */
        .conteudo {
            /* width: 100%; */
            align-items: center;
            /* justify-content: ; */
            color: #333333;
            {!!$style_content!!}
        }
    </style>
    @if(isset($paginas))
        @foreach ($paginas as $k=>$v)
            <style>
                .pagina-{{$k+1}} {
                    background-image: url('{{$v['bk_img']}}'); /* URL da imagem da página 3 */
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                }
                .pagina-{{$k+1}} .conteudo {
                    padding:{{@$v['padding']}};
                    /* margin:{{@$v['margin']}}; */
                }
            </style>
        @endforeach

    @endif
</head>
<body>
    @if(isset($paginas))
        @foreach ($paginas as $k=>$v)
        <div class="pagina pagina-{{$k+1}}">
            <div class="conteudo">
                {!!$v['title']!!}
                {!!$v['content']!!}
            </div>
        </div>
        @endforeach
    @endif
</body>
</html>
