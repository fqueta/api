<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Qlib\Qlib;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PdfGenerateController extends Controller
{
    /**
     * Metodo para expor um oraçamento PDF
     */
    public function orcamento_pdf($token){
        $ret = $this->gera_orcamento($token,'pdf',[
            'verificar_assinatura'=>true,
        ]);
        return $ret;
    }
    public function gera_orcamento($token=false,$type='pdf',$config=[]){
        if($token){
            $orca = new MatriculasController;
            $d = $orca->dm($token);
            if($d){
                $t_pdf = isset($config['t_pdf']) ? $config['t_pdf'] : false;
                // $routeName = isset($config['routeName']) ? $config['routeName'] : request()->route()->getName();
                $f_exibe = isset($config['f_exibe']) ? $config['f_exibe'] : false;
                $verificar = isset($config['verificar_assinatura']) ? $config['verificar_assinatura'] : false;

                $t_pdf = $t_pdf ? $t_pdf : false;
                $f_exibe = $f_exibe ? $f_exibe : 'navegador';
                if($verificar){
                    //verifica se está assinado
                    $is_signed = (new MatriculasController)->verificaDataAssinatura(['campo_bus'=>'token','token'=>$token]);
                    if($is_signed){
                        //se está assinado redireciona para o link de página assinado
                        $link_assinado = Qlib::qoption('dominio').'/solicitar-orcamento/proposta/'.$token.'/a';
                        return redirect($link_assinado);
                    }
                }

                $nome = isset($d['Nome']) ? $d['Nome'] : '';
                $code = 'fundo_proposta';
                $id_curso = isset($d['id_curso']) ? $d['id_curso'] : '';
                $tipo_curso = isset($d['tipo_curso']) ? $d['tipo_curso'] : '';
                $fundo = (new SiteController())->short_code($code,['comple'=>" AND tipo_curso='$tipo_curso'"]);
                $paginas = [];
                $dias = isset($dias)?$dias: Qlib::qoption('validade_orcamento');
				if(!$dias){
					$dias = 7;
				}
                $dadosD = explode(' ',$d['atualizado']);
				$validade =  Qlib::CalcularVencimento(Qlib::dataExibe($dadosD[0]),$dias);
                $validade = Qlib::dataExibe($validade);
                $res_orc = (new MatriculasController)->gerar_orcamento($token,'s');
                $dadosCli = isset($res_orc['dadosCli']) ? $res_orc['dadosCli'] : '';
                $orcamento = isset($res_orc['table']) ? $res_orc['table'] : '';
                $parcelamento = isset($res_orc['parcelamento']) ? $res_orc['parcelamento'] : '';
                if($tipo_curso==4 && isset($res_orc['listMod']['html'])){
                    $orcamento .= $res_orc['listMod']['html'];
                }
                // dd($d);
                if($type=='pdf'){
                    if(is_array($fundo)){
                        //Montar as paginas do PDF
                        $info_proposta = (new SiteController)->short_code('info_proposta',false,@$_GET['edit']);
                        foreach ($fundo as $k => $v) {
                            $pagina = ($k+1);
                            $title = '';//'<h1>Title pagina '.$pagina.'.</h1>';
                            $content = '';//'<p>Conteudo da pagina '.$pagina.'.<p>';
                            $padding = '110px 30px 10px 30px';
                            if($k==0){
                                //paigina inicial
                                $padding = '805px 30px 10px 30px';
                                $content = $dadosCli;
                            }
                            if($k==1){
                                // $padding = '805px 30px 10px 30px';
                                $content = $orcamento;
                            }
                            if($k==2){
                                if($tipo_curso!=4){
                                    $title = '';
                                }
                                $title2 = '<h2>Parcelamento</h2>';
                                $padding = '120px 30px 10px 30px';
                                $content = '<span style="font-size:12px">'.$info_proposta.'</span>';
                                $content .= $title2;
                                $content .= $parcelamento;
                            }
                            $paginas[$k] = [
                                'bk_img'=>$v['url'],
                                'title'=>$title,
                                'content'=>$content,
                                'padding'=>$padding,
                                // 'margin'=>'0px',
                            ];
                        }
                    }
                }

                $filename = 'Orçamento ' . $nome;
                $arquivo = isset($res_orc['nome_arquivo']) ? $res_orc['nome_arquivo'] : $filename;
                //,'paginas'=>['bk_img'=>'','title'=>'','content']
                if($type == 'pdf'){
                    $ret = $this->gerarPdfComImagemDeFundo(['token'=>$token,'nome_arquivo' => $arquivo,'paginas'=>$paginas,'t_pdf'=>$t_pdf,'f_exibe'=>$f_exibe]);
                }else{
                    $ret['dadosCli'] = $dadosCli;
                }
                return $ret;
            }
        }
    }
    /**
     * Metodo para gerar um arquivo em PDF aparter eu um HTML
     * @param array $config
     * uso $ret = (new PdfGererateController)->gerarPdfComImagemDeFundo(['nome_arquivo' => 'Orçamento','paginas'=>['bk_img'=>'','title'=>'','content']]);
     */

     public function gerarPdfComImagemDeFundo($config=[])
    {
        $t_pdf = isset($config['t_pdf']) ? $config['t_pdf'] : 'true';
        $arquivo_tipo = isset($config['arquivo_tipo']) ? $config['arquivo_tipo'] : 'orcamentos';
        $token = isset($config['token']) ? $config['token'] : uniqid();
        $f_exibe = isset($config['f_exibe']) ? $config['f_exibe'] : 'navegador'; // navegador ou download

        $nome_arquivo = isset($config['nome_arquivo']) ? $config['nome_arquivo'] : 'Pdf_com_imagem';

        $paginas = isset($config['paginas']) ? $config['paginas'] : [
             [
                 'bk_img'=>'https://crm.aeroclubejf.com.br/enviaImg/uploads/ead/5e3d812dd5612/6542b60fd4295.png',
                 'title'=>'<h2>Página1</h2>',
                 'content'=>'<p>
                 Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas pretium tempus libero sit amet venenatis. Phasellus ultrices ut ipsum sed tincidunt. Phasellus auctor nibh ut sem tempus accumsan. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Integer velit tellus, placerat condimentum consequat et, sodales dictum purus. Aenean quis velit faucibus, molestie augue sit amet, suscipit arcu. Aenean rutrum ante in tortor iaculis dignissim. Donec in turpis et augue fringilla molestie. Nunc non dictum augue. Suspendisse potenti. Morbi aliquam dignissim erat, eget blandit mauris rutrum ac. Vivamus ut pulvinar diam.
                 </p>
                 <p>',
                 'padding'=>'100px 30px 10px 30px',
                 'margin'=>'0px',
             ],
             [
                 'bk_img'=>'https://crm.aeroclubejf.com.br/enviaImg/uploads/ead/668ef8112510b/66fadef37341b.png',
                 'title'=>'<h2>Página2</h2>',
                 'content'=>'<p>Conteúdo da página2</p>',
                 'margin'=>'0px',
             ],
             [
                 'bk_img'=>'https://crm.aeroclubejf.com.br/enviaImg/uploads/ead/668ef8112510b/66fadef448e00.png',
                 'title'=>'<h2>Página3</h2>',
                 'content'=>'<p>Conteúdo da página3</p>',
                 'padding'=>'20px 20px 10px 10px',
                 'margin'=>'0px',
             ],
         ];
         $dados = [
             'style_content'=>'padding:20px;text-align:justify;',
             'paginas' =>$paginas,
         ];
        $html = view('pdf.pdf_com_imagem',$dados)->render();
        if($t_pdf=='false'){
            dd($f_exibe,$t_pdf,$paginas,$config);
            return $html;
        }

         // Gerar o PDF
         $pdf = SnappyPdf::loadHTML($html)
             ->setPaper('a4') // Define o tamanho do papel
             ->setOption('margin-top', 0)
             ->setOption('margin-bottom', 0)
             ->setOption('margin-left', 0)
             ->setOption('margin-right', 0)
             ->setOption('enable-local-file-access', true);
            //  ->setOption('ignore-certificate-errors', true); // Necessário para imagens locais

         // $pdf->setOption('header-html', view('header')->render());



        if($f_exibe=='download'){
            //faz download
            return $pdf->download($nome_arquivo.'.pdf');
        }elseif($f_exibe=='server'){

            $fileName = $arquivo_tipo.'/'.$token.'/proposta.pdf';
            //grava statico no servidor
            $pdfbin = $pdf->output();
            Storage::put($fileName, $pdfbin);
            $id_matricula = Qlib::get_matricula_id_by_token($token);
            if (Storage::exists($fileName) && $id_matricula) {
                $url = Storage::url($fileName);$short_code = 'proposta';
                $short_code .= '_pdf';
                $ret['salvo'] = Qlib::update_matriculameta($id_matricula,$short_code,$url);
                $ret['id_matricula'] = $id_matricula;
                $ret['short_code'] = $short_code;
                $ret['url'] = $url;
                if($ret['salvo']){
                    $ret['exec'] = true;
                }
                return $ret;
            }else{
                // Retornar uma mensagem ou caminho do arquivo salvo
                return response()->json(['exec'=>true,'message' => 'PDF salvo com sucesso!', 'path' => $fileName]);
            }
        }else{
            //grava statico no navegador
            return $pdf->inline($nome_arquivo.'.pdf');
        }
        // return $pdf->output('pdf_com_imagem_n.pdf');
    }
    public function convert_html($config=[]){
        $f_exibe = isset($config['f_exibe']) ? $config['f_exibe'] : 'pdf';
        $html = isset($config['html']) ? $config['html'] : '';
        $nome_aquivo_savo = isset($config['nome_aquivo_savo']) ? $config['nome_aquivo_savo'] : '';
        $titulo = isset($config['titulo']) ? $config['titulo'] : '';
        $token = isset($config['token']) ? $config['token'] : '';
        $pasta = isset($config['pasta']) ? $config['pasta'] : '';
        $id_matricula = isset($config['id_matricula']) ? $config['id_matricula'] : null;
        $short_code = isset($config['short_code']) ? $config['short_code'] : false;
        // $nome_aquivo_savo='arquivo',$titulo='Arquivo'
        // dd($config);
        $ret['exec'] = '';
        $html = view('pdf.template_default', ['titulo'=>$titulo,'conteudo'=>trim($html)])->render();
        $headerHtml = View::make('pdf.header')->render();
        $footerHtml = View::make('pdf.footer')->render();
        if(isset($_GET['tes'])){
            return $headerHtml.$html.$footerHtml;
        }
        $pdf = SnappyPdf::loadHTML($html)
                ->setPaper('a4')
                ->setOption('header-html', $headerHtml)
                ->setOption('margin-top', 25)
                ->setOption('margin-bottom', 13)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0)
                ->setOption('disable-smart-shrinking', true)
                ->setOption('footer-spacing', '0')
                ->setOption('print-media-type', true)
                ->setOption('background', true)
                ->setOption('replace', [
                    '{PAGE_NUM}' => '{PAGE_NUM}',
                    '{PAGE_COUNT}' => '{PAGE_COUNT}'
                ])
                ->setOption('footer-html', $footerHtml);
        if($f_exibe=='pdf'){
            return $pdf->stream($nome_aquivo_savo.'.pdf');
        }elseif($f_exibe=='server' && $token){
            try {
                $fileName = $pasta.'/'.$token.'/'.Qlib::createSlug($nome_aquivo_savo).'.pdf';
                //grava statico no servidor
                $pdfbin = $pdf->output();
                $ret['ger_arquivo'] = Storage::put($fileName, $pdfbin);
                // dd(Storage::exists($fileName),$short_code,$id_matricula);
                if (Storage::exists($fileName) && $short_code && $id_matricula) {
                    $url = Storage::url($fileName);
                    $ret['salvo'] = Qlib::update_matriculameta($id_matricula,$short_code.'_pdf',$url);
                    $ret['url'] = $url;
                    if($ret['salvo']){
                        $ret['exec'] = true;
                    }
                }else{

                }
            } catch (\Throwable $th) {
                $ret['error'] = $th->getMessage();
            }
        }
        if(!$token){
            $ret['mens'] = 'Token de contrato inválido';
        }
        return $ret;
    }
}
