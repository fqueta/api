<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>@yield('title', 'PDF')</title> --}}
    <title>{!! $titulo !!}</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            /* background-color: #f1f1f1; */
            background-color: #ffffff;
        }
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* background-color: #f1f1f1; */
            background-color: #ffffff;
            z-index: -1;
        }
        body{
            margin: 0;
            /* font-family: "Source Sans Pro",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529; */
            text-align: left;
            font-family: "Open Sans", Arial, Helvetica, Sans-Serif;
            font-size: 13px;
            line-height: 1.42857143;
            color: #333;
        }
        .conteudo{
            /* text-align: justify; */
            widows: 100%;
            /* margin: 0 25px ; */
            padding: 20px 30px ;
            /* position: relative;
            z-index: 1; */

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
    {{-- <div class="page-background"></div> --}}
    <div class="conteudo">
        {!! $conteudo !!}
         {{-- @yield('content') --}}
    </div>
</body>
</html>
