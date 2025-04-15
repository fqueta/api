<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documento Timbrado</title>
    <style>
        @page {
            size: A4;
            margin: 100px 50px; /* top/bottom: 100px; left/right: 50px */
        }


        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }

        body {
            background-image: url('https://crm.aeroclubejf.com.br/enviaImg/uploads/ead/67f8242bbaf7a/67f824afe2a82.png');
            background-size: cover;
            /* background-repeat: no-repeat; */
            background-position: top left;
            font-family: sans-serif;
            font-size: 12pt;
        }

        .conteudo {
            position: relative;
            padding: 100px 80px 100px 80px; /* ajuste para evitar sobreposição ao cabeçalho/rodapé do timbrado */
        }

        h1 {
            margin-bottom: 20px;
        }

        p {
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="conteudo">
        {!! $conteudo !!}
    </div>
</body>
</html>
