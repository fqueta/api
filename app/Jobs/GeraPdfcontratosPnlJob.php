<?php

namespace App\Jobs;

use App\Http\Controllers\MatriculasController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GeraPdfcontratosPnlJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $tm; //token matricula
    protected $tp; //token do periodo
    public function __construct($tm,$tp)
    {
        $this->tm = $tm;
        $this->tp = $tp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobLogger = Log::channel('jobs');
        $jobLogger->info('Iniciando o token matricula PNL: '.$this->tm.'.');
        try {
            $ret = (new MatriculasController)->grava_contrato_statico_periodo($this->tm,$this->tp);
            $jobLogger->info('contrato_periodos_estatica token matricula: '.$this->tm.' e tokem proposta ' .$this->tp. '. Está processando...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no GeraPdfcontratosPnlJob: ' . $e->getMessage());
        }
    }
}
