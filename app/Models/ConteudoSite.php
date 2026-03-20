<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConteudoSite extends Model
{
    use HasFactory;

    protected $table = 'conteudo_site';

    protected $guarded = ['id'];
}
