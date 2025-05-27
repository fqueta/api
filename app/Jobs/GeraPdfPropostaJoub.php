<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\MatriculasController;
use Illuminate\Support\Facades\Log;
class GeraPdfPropostaJoub implements ShouldQueue
{
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * gerar um pdf estatico do orçamento...
     */
    protected $tm;
    public function __construct($tm)
    {
        $this->tm = $tm;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobLogger = Log::channel('jobs');
        $jobLogger->info('Iniciando o token matricula: '.$this->tm.'.');

        try {
            // Lógica do Job
            // $ret = (new MatriculasController)->grava_contrato_statico($this->tm);
            $ret = (new MatriculasController)->orcamento_pdf_estatico($this->tm);
            $jobLogger->info('GeraPdfPropostaJoub token matricula: '.$this->tm.' está processando...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no GeraPdfPropostaJoub: ' . $e->getMessage());
        }

    }
}
