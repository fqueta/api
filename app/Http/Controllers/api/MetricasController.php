<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;

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
            'resumo'=>[
                'propostas'=>0,
                'ganhos'=>0,
                'conversas_com_humanos'=>0,
            ],
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
                $ret['resumo']['propostas'] = $propostas;
            }
            if($ganhos){
                $ret['resumo']['ganhos'] = $ganhos;
            }

            // Contagem de conversas com humanos da tabela capta_lead
            $conversas_com_humanos = DB::table('capta_lead')
                ->whereBetween('atualizado', [$inicio, $fim])
                ->whereNotNull('interajai')
                ->where('interajai', '!=', '')
                ->where('excluido', '=', 'n')
                ->where('deletado', '=', 'n')
                ->count();

            if($conversas_com_humanos){
                $ret['resumo']['conversas_com_humanos'] = $conversas_com_humanos;
            }

            // Adiciona lista detalhada por data quando o período for diferente
            if($inicio !== $fim) {
                $ret['detalhado_por_data'] = $this->getDetalhamentoPorData($inicio, $fim);
            }
        }
        return $ret;
    }

    /**
     * Retorna detalhamento de propostas e ganhos por data no período
     *
     * @param string $inicio
     * @param string $fim
     * @return array
     */
    private function getDetalhamentoPorData($inicio, $fim)
    {
        $detalhamento = [];

        // Busca propostas agrupadas por data
        $propostas = Matricula::join('clientes','clientes.id','=','matriculas.id_cliente')
            ->selectRaw('DATE(matriculas.data) as data, COUNT(*) as total')
            ->whereBetween('matriculas.data',[$inicio,$fim])
            ->where('matriculas.excluido','=','n')
            ->where('matriculas.deletado','=','n')
            ->where('clientes.interajai','!=','')
            ->where('clientes.interajai','!=','null')
            ->where('clientes.interajai','!=','0')
            ->where('clientes.interajai','!=','false')
            ->whereNotNull('matriculas.data') // Adiciona verificação para data não nula
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        // Busca ganhos agrupados por data
        $ganhos = Matricula::join('clientes','clientes.id','=','matriculas.id_cliente')
            ->selectRaw('DATE(matriculas.data_situacao) as data, COUNT(*) as total')
            ->whereBetween('matriculas.data_situacao',[$inicio,$fim])
            ->where('matriculas.situacao','=','g')
            ->where('matriculas.excluido','=','n')
            ->where('matriculas.deletado','=','n')
            ->where('clientes.interajai','!=','')
            ->where('clientes.interajai','!=','null')
            ->where('clientes.interajai','!=','0')
            ->where('clientes.interajai','!=','false')
            ->whereNotNull('matriculas.data_situacao') // Adiciona verificação para data não nula
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        // Busca conversas com humanos agrupadas por data
        $conversas_com_humanos = DB::table('capta_lead')
            ->selectRaw('DATE(atualizado) as data, COUNT(*) as total')
            ->whereBetween('atualizado', [$inicio, $fim])
            ->whereNotNull('interajai')
            ->where('interajai', '!=', '')
            ->where('excluido', '=', 'n')
            ->where('deletado', '=', 'n')
            ->whereNotNull('atualizado') // Adiciona verificação para data não nula
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        // Cria array com todas as datas do período
        $dataInicio = new \DateTime($inicio);
        $dataFim = new \DateTime($fim);
        $periodo = new \DatePeriod(
            $dataInicio,
            new \DateInterval('P1D'),
            $dataFim->modify('+1 day')
        );

        // Inicializa array com zeros para todas as datas
        foreach ($periodo as $data) {
            $dataFormatada = $data->format('Y-m-d');
            $detalhamento[$dataFormatada] = [
                'data' => $dataFormatada,
                'propostas' => 0,
                'ganhos' => 0,
                'conversas_com_humanos' => 0
            ];
        }
        // Preenche propostas
        $arr_propostas = $propostas->toArray();
        foreach ($arr_propostas as $proposta) {
            // Verifica se a data é uma string válida antes de usar como chave
            if (!empty($proposta['total'])) {
                $detalhamento[$proposta['data']]['propostas'] = $proposta['total'];
            }
        }

        // Preenche ganhos
        $arr_ganhos = $ganhos->toArray();
        foreach ($arr_ganhos as $ganho) {
            // Verifica se a data é uma string válida antes de usar como chave
            if (!empty($ganho['total'])) {
                $detalhamento[$ganho['data']]['ganhos'] = $ganho['total'];
            }
        }

        // Preenche conversas com humanos
        $arr_conversas = $conversas_com_humanos->toArray();
        foreach ($arr_conversas as $conversa) {
            // Verifica se a data é uma string válida antes de usar como chave
            if (!empty($conversa->total)) {
                $detalhamento[$conversa->data]['conversas_com_humanos'] = $conversa->total;
            }
        }

        // Retorna apenas as datas que têm dados ou converte para array indexado
        return array_values($detalhamento);
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
