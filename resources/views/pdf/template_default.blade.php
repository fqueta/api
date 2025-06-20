<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{!! $titulo !!}</title>
    <style>
        body{
            margin: 0;
            /* font-family: "Source Sans Pro",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529; */
            text-align: left;
            background-color: #f1f1f1;

            font-family: "Open Sans", Arial, Helvetica, Sans-Serif;
            font-size: 13px;
            line-height: 1.42857143;
            color: #333;
        }
        .conteudo{
            text-align: justify;
            widows: 100%;
            /* margin: 0 25px ; */
            padding: 10px 30px ;

        }
        h2 {
            letter-spacing: -1px;
            font-size: 22px;
            margin: 0px 0;
            margin-top: 0px;
            line-height: normal;
        }
        h3 {
            display: block;
            font-size: 19px;
            font-weight: 400;
            margin: 0px 0;
            line-height: normal;
        }
        h5 {
            font-size: 17px;
            font-weight: 300;
            margin: 10px 0;
            line-height: normal;
        }
        h6{
            font-size: 15px;
            margin: 10px 0;
            font-weight: 700;
            line-height: normal;
        }
    </style>
</head>
<body>
    {{-- <h1>{{ $titulo }}</h1> --}}
    <div class="conteudo">
        {!! $conteudo !!}
    </div>
</body>
</html>
