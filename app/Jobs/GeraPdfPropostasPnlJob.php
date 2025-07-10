<?php

namespace App\Jobs;

use App\Http\Controllers\api\OrcamentoController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GeraPdfPropostasPnlJob implements ShouldQueue
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
            $ret = (new OrcamentoController)->proposta_periodos_estatica($this->tm,$this->tp);
            $jobLogger->info('proposta_periodos_estatica token matricula: '.$this->tm.' e tokem proposta ' .$this->tp. '. Está processando...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no GeraPdfPropostasPnlJob: ' . $e->getMessage());
        }
    }
}
