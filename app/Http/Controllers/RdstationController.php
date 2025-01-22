<?php

namespace App\Http\Controllers;

use App\Qlib\Qlib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RdstationController extends Controller
{
    public $url_padrao;
    public $token_api;
    public $version;
    public function __construct(){
        $this->version = 'v1';
        $this->url_padrao = 'https://crm.rdstation.com/api/'.$this->version;
        $this->token_api = Qlib::qoption('token_usuario_rd');
    }
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
    public function webhook(){
        $ret['exec'] = false;
		@header("Content-Type: application/json");
		$json = file_get_contents('php://input');
        $d = [];
        if($json){
            $d = Qlib::lib_json_array($json);
            $ret = Qlib::saveEditJson($d,'webwook_rd.json');
        }
        Log::info('Webhook recebido:', $d);
        // $ret['exec'] = false;
        return $ret;
    }
}
