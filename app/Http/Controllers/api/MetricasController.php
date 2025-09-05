<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matricula;

class MetricasController extends Controller
{
    /**
     * Intregra a rota de metrica de propostas iniciada e concluídas
     * é necessario informa (o inicio ou fim juntos) ou ano,numero,tipo
     * @param Request $request
     * @param string $inicio
     * @param string $fim
     * @param int $ano
     * @param int $numero (se tipo for semana=1-52, se o tipo for mes=1-12)
     * @param string $tipo (semana,mes)
     * @return json
     *
     */

    public function total_metricas(Request $request)
    {
        try{
            $inicio = $request->input('inicio');
            $fim = $request->input('fim');
            $ano = $request->input('ano');
            $numero = $request->input('numero');
            $tipo = $request->input('tipo');
            if(!$inicio && !$fim && $ano && $numero && $tipo){
                $intervalo = $this->intervaloPeriodo($ano,$numero,$tipo,'Y-m-d');
                $inicio = $intervalo['inicio'];
                $fim = $intervalo['fim'];
            }
            $ret = [
                'propostas'=>0,
                'ganhos'=>0,
                'periodo_consulta'=>[
                    'data_inicio'=>$inicio,
                    'data_fim'=>$fim,
                ]
            ];
            if($inicio && $fim){
                $ret = $this->get_metricas($inicio,$fim);
            }
            return response()->json(['data'=>$ret],200);
        }catch(\Exception $e){
            return response()->json(['error'=>$e->getMessage()],500);
        }
    }
    /**
     * Metricas de propostas iniciada e concluídas
     *
     * @param string $inicio
     * @param string $fim
     * @return json
     */
    public function get_metricas($inicio,$fim)
    {
        $ret = [
            'propostas'=>0,
            'ganhos'=>0,
            'periodo_consulta'=>[
                'data_inicio'=>$inicio,
                'data_fim'=>$fim,
            ]
        ];
        if($inicio && $fim){
            $query = Matricula::join('clientes','clientes.id','=','matriculas.id_cliente');
            $propostas = $query
                ->whereBetween('matriculas.data',[$inicio,$fim])
                ->where('matriculas.excluido','=','n')
                ->where('matriculas.deletado','=','n')
                ->where('clientes.interajai','!=','')
                ->where('clientes.interajai','!=','null')
                ->where('clientes.interajai','!=','0')
                ->where('clientes.interajai','!=','false')
                ->count();
            $ganhos = $query
                ->whereBetween('matriculas.data_situacao',[$inicio,$fim])
                ->where('matriculas.situacao','=','g')
                ->where('matriculas.excluido','=','n')
                ->where('matriculas.deletado','=','n')
                ->where('clientes.interajai','!=','')
                ->where('clientes.interajai','!=','null')
                ->where('clientes.interajai','!=','0')
                ->where('clientes.interajai','!=','false')
                ->count();
            if($propostas){
                $ret['propostas'] = $propostas;
            }
            if($ganhos){
                $ret['ganhos'] = $ganhos;
            }
        }
        return $ret;
    }
    /**
     * Retorna o periodo de consulta quando informado o ano e a semana | mes
     *
     * @param int $ano
     * @param int $numero
     * @param string $tipo
     * @param string $formato
     * @return array
     * // Exemplos de uso:
        *   $intervaloSemana = (new MetricasController)->intervaloPeriodo(2025, 36, 'semana', 'd/m/Y');
        *   echo "Semana 36/2025: Início {$intervaloSemana['inicio']} - Fim {$intervaloSemana['fim']}\n";
        *   $intervaloMes = (new MetricasController)->intervaloPeriodo(2025, 9, 'mes', 'd/m/Y');
        *   echo "Setembro/2025: Início {$intervaloMes['inicio']} - Fim {$intervaloMes['fim']}\n";
     */
    public function intervaloPeriodo($ano, $numero, $tipo = 'semana', $formato = 'Y-m-d') {
        if ($tipo === 'semana') {
            // Segunda-feira da semana
            $dataInicio = new \DateTime();
            $dataInicio->setISODate($ano, $numero, 1);

            // Domingo da semana
            $dataFim = clone $dataInicio;
            $dataFim->modify('+6 days');
        } elseif ($tipo === 'mes') {
            // Primeiro dia do mês
            $dataInicio = new \DateTime("$ano-$numero-01");

            // Último dia do mês
            $dataFim = clone $dataInicio;
            $dataFim->modify('last day of this month');
        } else {
            throw new \Exception("Tipo inválido, use 'semana' ou 'mes'.");
        }

        return [
            'inicio' => $dataInicio->format($formato),
            'fim'    => $dataFim->format($formato)
        ];
    }


}
