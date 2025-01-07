<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesteController extends Controller
{
    public function index(){
        $ret['exec'] = false;
        $ret = (new SiteController())->short_code('fundo_proposta',['compl'=>'']);
        return $ret;
    }
}
