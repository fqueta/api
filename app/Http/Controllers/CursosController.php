<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Qlib\Qlib;
use Illuminate\Http\Request;

class CursosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $d = Curso::where('excluido','=','n')->where('deletado','=','n')->OrderBy('id','ASC')->get();
        $ret['exec'] = false;
        $ret['dados'] = [];
        if($d->count() > 0){
            $ret['exec'] = true;
            $dados = [];
            foreach ($d as $k => $v) {
                $dados[$k] = $v->getOriginal();
            }
            $ret['dados'] = $d;
        }
        return $ret;
    }
    /**
	 * Metodo para verificar se o curso é um recheck
	 */
	public function is_recheck($id_curso){
		$ret = false;
        $tab10 = 'cursos';
		if($id_curso){
			$conf = Qlib::buscaValorDb0($tab10,'id',$id_curso,'config');
			if($conf){
				$arr_con = Qlib::lib_json_array($conf);
				if(isset($arr_con['adc']['recheck']) && $arr_con['adc']['recheck'] == 's'){
					$ret = true;
				}
			}
		}
		return $ret;
	}
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
