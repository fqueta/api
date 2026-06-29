<?php

namespace App\Http\Controllers\api;

use App\Exports\TurmasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TurmasController extends Controller
{
    /**
     * Exportar turmas com filtros
     * @param string $request->formato 'xlsx' (padrao) ou 'json'
     */
    public function exportar(Request $request)
    {
        $filters = [];
        if ($request->has('id_curso')) {
            $filters['id_curso'] = $request->get('id_curso');
        }
        if ($request->has('id')) {
            $filters['id'] = $request->get('id');
        }
        if ($request->has('status')) {
            $filters['status'] = $request->get('status');
        }
        if ($request->has('ativo')) {
            $filters['ativo'] = $request->get('ativo');
        }

        $formato = $request->get('formato', 'xlsx');

        if ($formato === 'json') {
            $export = new TurmasExport($filters);
            $data = $export->collection();
            $ret = [
                'exec' => true,
                'status' => 200,
                'total' => $data->count(),
                'data' => $data,
            ];
            return response()->json($ret);
        }

        $export = new TurmasExport($filters);
        $nomeArquivo = 'turmas_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download($export, $nomeArquivo);
    }
}
