<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MatriculasController;
use Illuminate\Http\Request;

class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $d = request()->all();
        $token = $id;
        $exibe_parcelamento = isset($d['ep']) ? $d['ep'] : null;
        $ret = (new MatriculasController)->gerar_orcamento($token, $exibe_parcelamento);

        return response()->json($ret);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function assinar_proposta(string $token){
        $d = request()->all();
        if(!isset($d['token_matricula'])){
            $d['token_matricula'] = $token;
        }
        $ret = (new MatriculasController)->assinar_proposta($d);
        return $ret;
    }

    public function add_update($config=[]){
        //indentificar o curso
        //selecionar a primeira turma disponivel
        //
    }
}
