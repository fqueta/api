<?php

namespace App\Http\Controllers;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;

class PdfSanappy extends Controller
{
    public function gerarPdf()
    {
        $dados = [
            'titulo' => 'Relatório de Teste',
            'conteudo' => 'Este é o conteúdo do relatório.',
        ];

        // Carregar a view e gerar o PDF
        $pdf = SnappyPdf::loadView('pdf.meu_pdf', $dados)
        ->setPaper('a4') // Define o tamanho do papel
        ->setOption('margin-top', 0)
        ->setOption('margin-bottom', 0)
        ->setOption('margin-left', 0)
        ->setOption('margin-right', 0)
        ->setOption('enable-local-file-access', true);
        // Retornar o PDF para download
        return $pdf->download('relatorio.pdf');
    }

    public function gerarPdfComImagemDeFundo()
    {
        $html = view('pdf.pdf_com_imagem')->render();
        // return $html;
        // Gerar o PDF
        $pdf = SnappyPdf::loadHTML($html)
            ->setPaper('a4') // Define o tamanho do papel
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('enable-local-file-access', true); // Necessário para imagens locais
        // $pdf->setOption('header-html', view('header')->render());
        return $pdf->download('pdf_com_imagem.pdf');
}

}
