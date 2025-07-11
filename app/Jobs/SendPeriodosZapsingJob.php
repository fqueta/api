<?php

namespace App\Jobs;

use App\Http\Controllers\MatriculasController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPeriodosZapsingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $tm;  //Token matricula
    protected $tp;  //Token periodos
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
        $jobLogger->info('Iniciando o token matricula PNL: '.$this->tm.', e periodo '.$this->tp.'.');


        try {
            // Lógica do Job
            $ret = (new MatriculasController)->send_to_zapSing($this->tm,false,$this->tp);
            $jobLogger->info('SendPeriodosZapsingJob token matricula: '.$this->tm.', e periodo '.$this->tp.' está processando...',$ret);
        } catch (\Exception $e) {
            $jobLogger->error('Erro no SendPeriodosZapsingJob: ' . $e->getMessage());
        }
    }
}
