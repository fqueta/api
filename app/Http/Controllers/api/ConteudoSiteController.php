<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ConteudoSite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConteudoSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ConteudoSite::query();

        // Filtro por id_curso if present
        if ($request->has('id_curso')) {
            $query->where('id_curso', $request->get('id_curso'));
        }

        // Filtro por short_code if present
        if ($request->has('short_code')) {
            $query->where('short_code', $request->get('short_code'));
        }

        // Filtro por tipo_conteudo if present
        if ($request->has('tipo_conteudo')) {
            $query->where('tipo_conteudo', $request->get('tipo_conteudo'));
        }

        // Filtro padrão para registros ativos (seguindo padrão do SiteController)
        if (!$request->has('all')) {
            $query->where('ativo', 's');
        }

        $limit = $request->get('limit', 25);
        
        if ($limit === 'all') {
            $data = $query->get();
        } else {
            $data = $query->paginate($limit);
        }

        return response()->json([
            'exec' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $conteudo = ConteudoSite::find($id);

        if (!$conteudo) {
            return response()->json([
                'exec' => false,
                'status' => 404,
                'message' => 'Conteúdo não encontrado',
            ], 404);
        }

        return response()->json([
            'exec' => true,
            'status' => 200,
            'data' => $conteudo,
        ]);
    }
}
