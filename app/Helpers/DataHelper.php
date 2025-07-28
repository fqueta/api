<?php

namespace App\Helpers;

use DateTime;
use DateInterval;
use InvalidArgumentException;

class DataHelper
{
    /**
     * Calcula a data de vencimento a partir de uma data inicial e um prazo.
     *
     * @param string $dataInicial       Ex: '2025-07-23'
     * @param int|string $prazo         Ex: 10, -10, 'P15D', '-P1Y2M'
     * @param string|null $tipoPrazo    'dias', 'meses', 'anos' ou null se ISO
     * @param string $formatoRetorno    Ex: 'Y-m-d'
     * @return string
     */
    public static function calcularVencimento(string $dataInicial, int|string $prazo, ?string $tipoPrazo = 'dias', string $formatoRetorno = 'Y-m-d'): string
    {
        $data = new DateTime($dataInicial);

        // Se for string no formato ISO 8601 (ex: 'P15D', '-P1Y2M')
        if (is_string($prazo) && str_starts_with(ltrim($prazo, '-'), 'P')) {
            $isNegativo = str_starts_with($prazo, '-');
            $intervaloStr = ltrim($prazo, '-');

            try {
                $intervalo = new DateInterval($intervaloStr);

                if ($isNegativo) {
                    // Inverter sinais (todos os campos válidos)
                    $intervalo->invert = 1;
                }

                $data->add($intervalo);
            } catch (\Exception $e) {
                throw new InvalidArgumentException("Intervalo ISO inválido: {$prazo}");
            }
        }
        // Se for inteiro e tipo definido (dias, meses, anos)
        elseif (is_int($prazo) && $tipoPrazo !== null) {
            $mod = $prazo >= 0 ? "+{$prazo}" : "{$prazo}";

            switch (strtolower($tipoPrazo)) {
                case 'dias':
                    $data->modify("{$mod} days");
                    break;
                case 'meses':
                    $data->modify("{$mod} months");
                    break;
                case 'anos':
                    $data->modify("{$mod} years");
                    break;
                default:
                    throw new InvalidArgumentException("Tipo de prazo inválido: {$tipoPrazo}");
            }
        } else {
            throw new InvalidArgumentException("Parâmetros de prazo inválidos.");
        }

        return $data->format($formatoRetorno);
    }
        /**
     * Verifica se uma data está vencida (anterior a hoje).
     *
     * @param string $data             Data a ser verificada (ex: '2025-07-23')
     * @param bool $incluirHoje        Se true, considera hoje como vencido também
     * @return bool                    true se vencido, false se ainda está no prazo
     */
    public static function estaVencido(string $data, bool $incluirHoje = false): bool
    {
        $hoje = new DateTime();
        $dataVerificada = new DateTime($data);

        if ($incluirHoje) {
            return $dataVerificada <= $hoje;
        }

        return $dataVerificada < $hoje;
    }

}
