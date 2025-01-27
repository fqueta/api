<?php

namespace App\Http\Controllers;

use App\Qlib\Qlib;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $tab;
    public function __construct()
    {
        $this->tab = 'clientes';
    }
    /**
     * adicion ou atualiza um cliente
     * @param array $dados array com os campos e valores que serão gravados no bando dedados
     * @param string $where com as string SQL de condição para atualização do registro no banco de dados
     */
    public function add_update($dados=[],$where=''){
        $tab = $this->tab;
        //tornar unico pelo email por padrão
        $where = $where ? $where : false;
        if(empty($where) && isset($dados['Email']) && !empty($dados['Email'])){
            $where = "WHERE Email='".$dados['Email']."'";
        }
        $ret = Qlib::update_tab($tab,$dados,$where);
        return $ret;
    }
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
        $ret['exec'] = false;

        return $ret;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
