<?php

namespace App\Http\Controllers;

use App\Models\ddi;
use Illuminate\Http\Request;

class ddiController extends Controller
{
    public function index(Request $request){
        return ddi::all();
    }
}
